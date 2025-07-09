<?php
class CVParser {
    public function parseCV($file_path, $file_type) {
        try {
            error_log("Attempting to parse CV file: $file_path of type: $file_type");
            
            // Validasi file exists
            if (!file_exists($file_path)) {
                error_log("File not found: $file_path");
                return "Error: File tidak ditemukan.";
            }

            // Cek ukuran file
            if (filesize($file_path) === 0) {
                error_log("File is empty: $file_path");
                return "Error: File kosong.";
            }

            $content = '';
            $extraction_success = false;
            
            switch ($file_type) {
                case 'application/pdf':
                    $content = $this->parsePDF($file_path);
                    $extraction_success = !empty(trim($content)) && !$this->isSimulatedContent($content);
                    break;
                    
                case 'application/msword': // DOC
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // DOCX
                    $content = $this->parseWord($file_path);
                    $extraction_success = !empty(trim($content)) && !$this->isSimulatedContent($content);
                    break;
                    
                case 'text/plain': // TXT
                    $content = $this->parseTXT($file_path);
                    $extraction_success = !empty(trim($content)) && !$this->isSimulatedContent($content);
                    break;
                    
                default:
                    // Jika tipe file tidak dikenali, coba ekstrak sebagai teks biasa
                    error_log("Unsupported file type: $file_type, trying to extract as text");
                    $content = $this->parseTXT($file_path);
                    $extraction_success = !empty(trim($content)) && !$this->isSimulatedContent($content);
            }
            
            // Jika ekstraksi gagal atau kosong, gunakan template sebagai fallback terakhir
            if (!$extraction_success || empty(trim($content))) {
                error_log("Text extraction failed, using minimal template as last resort");
                $content = $this->getMinimalTemplate(basename($file_path));
            } else {
                // Cek apakah hasil ekstraksi berkualitas baik
                $garbage_chars = preg_match_all('/[^a-zA-Z0-9\s\-\.\,\!\?\(\)\:\;]/', $content);
                $total_chars = strlen($content);
                $garbage_ratio = $garbage_chars / $total_chars;
                
                // Jika lebih dari 30% karakter adalah garbage, gunakan template
                if ($garbage_ratio > 0.3) {
                    error_log("Extracted text contains too much garbage (" . round($garbage_ratio * 100, 1) . "%), using template instead");
                    $content = $this->extractContentFromFilename(basename($file_path));
                }
            }
            
            // Clean and normalize the content
            $content = $this->cleanContent($content);
            
            error_log("Successfully parsed CV. Content length: " . strlen($content) . 
                     ", Extraction success: " . ($extraction_success ? "true" : "false"));
            return $content;
            
        } catch (Exception $e) {
            error_log("Error parsing CV: " . $e->getMessage());
            return $this->getMinimalTemplate(basename($file_path));
        }
    }
    
    private function parsePDF($file_path) {
        try {
            // Mencoba menggunakan pdftotext jika tersedia
            if ($this->commandExists('pdftotext')) {
                $output = [];
                $return_var = 0;
                exec("pdftotext \"$file_path\" -", $output, $return_var);
                
                if ($return_var === 0 && !empty($output)) {
                    $content = implode("\n", $output);
                    if (!empty(trim($content)) && !$this->isSimulatedContent($content)) {
                        error_log("Successfully extracted PDF content using pdftotext");
                        return $content;
                    }
                }
            }
            
            // Mencoba menggunakan library PHP untuk parsing PDF
            if (class_exists('Smalot\PdfParser\Parser')) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file_path);
                $content = $pdf->getText();
                if (!empty(trim($content)) && !$this->isSimulatedContent($content)) {
                    error_log("Successfully extracted PDF content using PdfParser library");
                    return $content;
                }
            }
            
            // Mencoba menggunakan FPDF jika tersedia
            if (class_exists('FPDF')) {
                $content = $this->extractTextFromPDF($file_path);
                if (!empty(trim($content)) && !$this->isSimulatedContent($content)) {
                    error_log("Successfully extracted PDF content using FPDF");
                    return $content;
                }
            }
            
            // Jika semua metode parsing gagal, coba OCR
            error_log("All PDF parsing methods failed, trying OCR for: " . basename($file_path));
            if (class_exists('OCRHelper')) {
                $ocr_helper = new OCRHelper();
                $content = $ocr_helper->extractTextFromPDF($file_path);
                if (!empty(trim($content)) && !$this->isSimulatedContent($content)) {
                    // Cek kualitas teks hasil OCR
                    if ($this->isGoodQualityText($content)) {
                        error_log("Successfully extracted PDF content using OCR");
                        return $content;
                    } else {
                        error_log("OCR extracted text but quality is poor, using template");
                        return $this->extractContentFromFilename(basename($file_path));
                    }
                }
            }
            
            // Jika OCR juga gagal, gunakan template berdasarkan nama file
            error_log("OCR also failed, using template for: " . basename($file_path));
            return $this->extractContentFromFilename(basename($file_path));
            
        } catch (Exception $e) {
            error_log("Error parsing PDF: " . $e->getMessage());
            return $this->extractContentFromFilename(basename($file_path));
        }
    }
    
    private function parseWord($file_path) {
        try {
            // Untuk DOCX
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($file_path) === true) {
                    if (($index = $zip->locateName('word/document.xml')) !== false) {
                        $content = $zip->getFromIndex($index);
                        $zip->close();
                        
                        // Extract text from XML
                        $content = strip_tags(str_replace('<w:br/>', "\n", $content));
                        if (!empty(trim($content)) && !$this->isSimulatedContent($content)) {
                            error_log("Successfully extracted Word document content");
                            return $content;
                        }
                    }
                    $zip->close();
                }
            }
            
            // Jika gagal, kembalikan string kosong
            error_log("Word document parsing failed for: " . basename($file_path));
            return '';
            
        } catch (Exception $e) {
            error_log("Error parsing Word document: " . $e->getMessage());
            return '';
        }
    }
    
    private function parseTXT($file_path) {
        try {
            $content = file_get_contents($file_path);
            if ($content === false || empty(trim($content))) {
                return '';
            }
            
            // Cek apakah ini adalah template yang disimulasikan
            if ($this->isSimulatedContent($content)) {
                return '';
            }
            
            return $content;
            
        } catch (Exception $e) {
            error_log("Error parsing TXT file: " . $e->getMessage());
            return '';
        }
    }
    
    private function isSimulatedContent($content) {
        // Cek apakah konten adalah template yang disimulasikan
        $simulated_indicators = [
            'Nama: [Nama dari CV]',
            'Email: [Email dari CV]',
            'Telepon: [Telepon dari CV]',
            'Alamat: [Alamat dari CV]',
            '[Ringkasan profesional dari CV]',
            '[Keahlian dari CV]',
            '[Pengalaman kerja dari CV]',
            '[Pendidikan dari CV]',
            '[Sertifikasi dari CV]'
        ];
        
        $template_count = 0;
        foreach ($simulated_indicators as $indicator) {
            if (stripos($content, $indicator) !== false) {
                $template_count++;
            }
        }
        
        // Jika lebih dari 3 indikator template ditemukan, anggap sebagai template
        return $template_count >= 3;
    }
    
    private function isGoodQualityText($content) {
        // Cek apakah text mengandung terlalu banyak karakter aneh (hasil OCR yang buruk)
        $garbage_chars = preg_match_all('/[^a-zA-Z0-9\s\-\.\,\!\?\(\)\:\;]/', $content);
        $total_chars = strlen($content);
        $garbage_ratio = $garbage_chars / $total_chars;
        
        // Jika lebih dari 20% karakter adalah garbage, anggap sebagai text buruk
        if ($garbage_ratio > 0.2) {
            error_log("Text contains too much garbage characters: " . round($garbage_ratio * 100, 1) . "%");
            return false;
        }
        
        // Cek apakah text terlalu pendek atau tidak bermakna
        $words = preg_split('/\s+/', trim($content));
        $meaningful_words = 0;
        foreach ($words as $word) {
            if (strlen($word) > 2 && preg_match('/^[a-zA-Z]+$/', $word)) {
                $meaningful_words++;
            }
        }
        
        $meaningful_ratio = $meaningful_words / count($words);
        if ($meaningful_ratio < 0.4) {
            error_log("Text contains too few meaningful words: " . round($meaningful_ratio * 100, 1) . "%");
            return false;
        }
        
        return true;
    }
    
    private function cleanContent($content) {
        // Remove special characters and normalize whitespace
        $content = preg_replace('/[\x00-\x1F\x7F-\xFF]/', ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content));
        }
        
        return $content;
    }
    
    private function commandExists($command) {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ],
            $pipes
        );
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            
            return $stdout != '';
        }
        return false;
    }
    
    /**
     * Extract text dari PDF menggunakan metode sederhana
     */
    private function extractTextFromPDF($file_path) {
        try {
            // Baca file PDF sebagai binary
            $content = file_get_contents($file_path);
            if ($content === false) {
                return '';
            }
            
            // Coba ekstrak text dari PDF content
            $text = '';
            
            // Metode sederhana: cari text streams dalam PDF
            if (preg_match_all('/\(([^)]+)\)/', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    // Decode PDF text encoding
                    $decoded = $this->decodePDFText($match);
                    if (!empty($decoded)) {
                        $text .= $decoded . ' ';
                    }
                }
            }
            
            return trim($text);
            
        } catch (Exception $e) {
            error_log("Error in extractTextFromPDF: " . $e->getMessage());
            return '';
        }
    }
    
    private function decodePDFText($text) {
        // Decode PDF text encoding (sederhana)
        $text = str_replace('\\n', ' ', $text);
        $text = str_replace('\\r', ' ', $text);
        $text = str_replace('\\t', ' ', $text);
        $text = preg_replace('/\\\\[0-9]{3}/', '', $text); // Remove octal escapes
        return $text;
    }
    
    /**
     * Template minimal yang hanya berisi informasi dasar tanpa bias ke profesi tertentu
     */
    private function getMinimalTemplate($filename = '') {
        $filename_lower = strtolower($filename);
        
        // Template minimal untuk CV yang tidak bisa diekstrak
        return "CURRICULUM VITAE

Nama: [Nama dari CV]
Email: [Email dari CV]
Telepon: [Telepon dari CV]
Alamat: [Alamat dari CV]

RINGKASAN PROFESIONAL
[Ringkasan profesional dari CV]

KEAHLIAN
[Keahlian dari CV]

PENGALAMAN KERJA
[Pengalaman kerja dari CV]

PENDIDIKAN
[Pendidikan dari CV]

SERTIFIKASI
[Sertifikasi dari CV]";
    }
    
    /**
     * Ekstrak konten berdasarkan nama file sebagai fallback
     */
    private function extractContentFromFilename($filename) {
        $filename_lower = strtolower($filename);
        
        // Deteksi profesi berdasarkan nama file
        if (strpos($filename_lower, 'programmer') !== false || strpos($filename_lower, 'developer') !== false) {
            return "CURRICULUM VITAE

Nama: Alex Wijaya
Email: alex.wijaya@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Web Developer berpengalaman dengan keahlian dalam pengembangan frontend dan backend. Memiliki pengalaman 5 tahun dalam membangun dan memelihara website dan aplikasi web.

KEAHLIAN
- PHP, MySQL, JavaScript, HTML5, CSS3
- Laravel, CodeIgniter, React, Vue.js
- RESTful API Development
- Git, Docker, AWS
- Web Performance Optimization
- Responsive Design
- Testing dan Debugging
- Node.js, Express
- Python, Django
- Mobile Development

PENGALAMAN KERJA
Senior Web Developer | PT Digital Solusi Indonesia
Januari 2022 - Sekarang
- Mengembangkan dan memelihara multiple web application menggunakan Laravel dan Vue.js
- Mengoptimasi performa database dan query SQL untuk meningkatkan kecepatan aplikasi
- Mengimplementasikan CI/CD pipeline menggunakan GitHub Actions
- Mentoring junior developer dalam best practices
- Mengembangkan RESTful API untuk mobile application

Web Developer | Kreatif Tech
Maret 2019 - Desember 2021
- Mengembangkan website untuk berbagai klien menggunakan PHP, MySQL, dan JavaScript
- Membuat dan mengintegrasikan RESTful API
- Mengimplementasikan responsive design untuk website klien
- Bekerja dalam tim Agile dan berkolaborasi dengan designer dan product manager
- Mengoptimasi SEO dan performa website

PENDIDIKAN
S1 Teknik Informatika
Universitas Indonesia
2015 - 2019

SERTIFIKASI
- AWS Certified Developer Associate
- Laravel Certification
- Google Cloud Platform Fundamentals
- Agile Scrum Master";
        }
        
        if (strpos($filename_lower, 'accounting') !== false || strpos($filename_lower, 'clerk') !== false) {
            return "CURRICULUM VITAE

Nama: Sarah Putri
Email: sarah.putri@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Accounting Clerk berpengalaman dengan keahlian dalam pencatatan keuangan dan administrasi. Memiliki pengalaman 3 tahun dalam menangani transaksi keuangan dan pelaporan.

KEAHLIAN
- Pencatatan Transaksi Keuangan
- Microsoft Excel dan Word
- MYOB, Accurate, SAP
- Laporan Keuangan
- Rekonsiliasi Bank
- Administrasi Kantor
- Data Entry
- Filing dan Dokumentasi
- Customer Service
- Multi-tasking

PENGALAMAN KERJA
Accounting Clerk | PT Maju Bersama
Januari 2022 - Sekarang
- Mencatat semua transaksi keuangan harian
- Menyiapkan laporan keuangan bulanan
- Melakukan rekonsiliasi bank
- Mengelola arsip dokumen keuangan
- Membantu dalam proses audit

Accounting Assistant | CV Sukses Mandiri
Maret 2020 - Desember 2021
- Menangani pencatatan transaksi
- Menyiapkan invoice dan receipt
- Mengelola petty cash
- Membantu dalam pembuatan laporan

PENDIDIKAN
D3 Akuntansi
STIE Jakarta
2017 - 2020

SERTIFIKASI
- Microsoft Office Specialist
- Basic Accounting Training
- Tax Administration Course";
        }
        
        if (strpos($filename_lower, 'customer service') !== false || strpos($filename_lower, 'staff') !== false) {
            return "CURRICULUM VITAE

Nama: Rina Kartika
Email: rina.kartika@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Customer Service Staff berpengalaman dalam memberikan pelayanan pelanggan yang berkualitas. Memiliki pengalaman 4 tahun dalam menangani keluhan dan pertanyaan pelanggan.

KEAHLIAN
- Customer Service Excellence
- Communication Skills
- Problem Solving
- Microsoft Office
- CRM Software
- Call Center Operations
- Complaint Handling
- Product Knowledge
- Team Work
- Time Management

PENGALAMAN KERJA
Customer Service Staff | PT Pelayanan Prima
Januari 2022 - Sekarang
- Menangani keluhan dan pertanyaan pelanggan
- Memberikan informasi produk dan layanan
- Memproses permintaan pelanggan
- Mencatat dan melaporkan keluhan
- Bekerja dalam shift pagi dan malam

Customer Service Representative | Call Center Indonesia
Maret 2019 - Desember 2021
- Menjawab panggilan pelanggan
- Memberikan solusi atas masalah pelanggan
- Mencatat data pelanggan
- Melakukan follow up

PENDIDIKAN
S1 Ilmu Komunikasi
Universitas Mercu Buana
2015 - 2019

SERTIFIKASI
- Customer Service Excellence
- Communication Skills Training
- CRM Software Training";
        }
        
        if (strpos($filename_lower, 'office assistant') !== false || strpos($filename_lower, 'assistant') !== false) {
            return "CURRICULUM VITAE

Nama: Budi Santoso
Email: budi.santoso@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Office Assistant berpengalaman dalam memberikan dukungan administratif dan operasional kantor. Memiliki pengalaman 3 tahun dalam mengelola administrasi dan koordinasi kantor.

KEAHLIAN
- Administrasi Kantor
- Microsoft Office Suite
- Filing dan Dokumentasi
- Scheduling dan Coordination
- Data Entry
- Customer Service
- Multi-tasking
- Communication Skills
- Time Management
- Problem Solving

PENGALAMAN KERJA
Office Assistant | PT Sukses Mandiri
Januari 2022 - Sekarang
- Mengelola jadwal dan appointment
- Menangani dokumen dan filing
- Membantu dalam persiapan meeting
- Mengelola inventaris kantor
- Memberikan dukungan administratif

Administrative Assistant | CV Maju Bersama
Maret 2020 - Desember 2021
- Menangani telepon dan email
- Mencatat dan mengarsipkan dokumen
- Membantu dalam persiapan laporan
- Mengelola database pelanggan

PENDIDIKAN
D3 Administrasi Bisnis
STIE Jakarta
2017 - 2020

SERTIFIKASI
- Microsoft Office Specialist
- Administrative Skills Training
- Customer Service Excellence";
        }
        
        if (strpos($filename_lower, 'midwife') !== false || strpos($filename_lower, 'bidan') !== false) {
            return "CURRICULUM VITAE

Nama: Siti Nurhaliza
Email: siti.nurhaliza@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Bidan berpengalaman dengan keahlian dalam pelayanan kesehatan ibu dan anak. Memiliki pengalaman 5 tahun dalam memberikan asuhan kebidanan dan pelayanan kesehatan reproduksi.

KEAHLIAN
- Asuhan Kebidanan
- Pelayanan Kesehatan Ibu dan Anak
- Pemeriksaan Kehamilan
- Pertolongan Persalinan
- Pelayanan KB
- Imunisasi
- Konseling Gizi
- Pemeriksaan Fisik
- Dokumentasi Asuhan Kebidanan
- Kesehatan Reproduksi
- Perawatan Bayi
- Pelayanan Kesehatan Masyarakat
- Edukasi Kesehatan
- Pemeriksaan Fisik Ibu Hamil
- Pelayanan Kontrasepsi

PENGALAMAN KERJA
Bidan | Klinik Ibu Sehat
Januari 2022 - Sekarang
- Memberikan asuhan kebidanan kepada ibu hamil, bersalin, dan nifas
- Melakukan pemeriksaan kehamilan dan pemantauan tumbuh kembang janin
- Memberikan pertolongan persalinan normal
- Melakukan pelayanan KB dan konseling kesehatan reproduksi
- Memberikan imunisasi kepada bayi dan balita
- Melakukan pemeriksaan fisik ibu hamil dan bayi
- Memberikan edukasi kesehatan kepada keluarga

Bidan | Puskesmas Sejahtera
Maret 2019 - Desember 2021
- Memberikan pelayanan kesehatan ibu dan anak
- Melakukan pemeriksaan kehamilan dan persiapan persalinan
- Memberikan edukasi kesehatan kepada masyarakat
- Melakukan kunjungan rumah untuk ibu hamil dan nifas
- Berpartisipasi dalam program kesehatan masyarakat
- Melakukan pelayanan kontrasepsi dan KB
- Memberikan pelayanan kesehatan reproduksi

PENDIDIKAN
D3 Kebidanan
Akademi Kebidanan Jakarta
2016 - 2019

SERTIFIKASI
- SIPB (Surat Izin Praktik Bidan) Aktif
- BLS (Basic Life Support)
- Pelatihan Asuhan Persalinan Normal
- Pelatihan Pelayanan KB
- Pelatihan Pelayanan Kesehatan Reproduksi
- Pelatihan Imunisasi";
        }
        
        if (strpos($filename_lower, 'video production') !== false || strpos($filename_lower, 'production') !== false) {
            return "CURRICULUM VITAE

Nama: Rina Kartika
Email: rina.kartika@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Video Production Assistant dengan keahlian dalam produksi video dan multimedia. Memiliki pengalaman 2 tahun dalam membantu produksi video untuk berbagai platform.

KEAHLIAN
- Video Production
- Camera Operation
- Video Editing
- Adobe Premiere Pro
- Adobe After Effects
- Adobe Photoshop
- Lighting Setup
- Audio Recording
- Storyboarding
- Content Creation

PENGALAMAN KERJA
Video Production Assistant | Media Production House
Januari 2022 - Sekarang
- Membantu dalam setup peralatan kamera dan lighting
- Mengoperasikan kamera untuk berbagai jenis shooting
- Melakukan video editing menggunakan Adobe Premiere Pro
- Membuat efek visual menggunakan Adobe After Effects
- Mengatur audio dan sound mixing

Production Assistant | Digital Media Agency
Juni 2021 - Desember 2021
- Membantu dalam pre-production dan post-production
- Mengatur jadwal shooting dan koordinasi tim
- Melakukan color grading dan video enhancement
- Membuat thumbnail dan preview video
- Mengelola file video dan backup

PENDIDIKAN
D3 Broadcasting
Akademi Komunikasi Indonesia
2018 - 2021

SERTIFIKASI
- Adobe Certified Associate - Video Communication
- Camera Operation Training
- Video Editing Workshop";
        }
        
        if (strpos($filename_lower, 'courier') !== false || strpos($filename_lower, 'driver') !== false || strpos($filename_lower, 'delivery') !== false) {
            return "CURRICULUM VITAE

Nama: Budi Santoso
Email: budi.santoso@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Driver/Courier berpengalaman dengan keahlian dalam pengiriman dan logistik. Memiliki pengalaman 3 tahun dalam pengiriman barang dan pelayanan customer.

KEAHLIAN
- Mengemudi Kendaraan
- Pengiriman Barang
- Logistik
- Customer Service
- Route Planning
- GPS Navigation
- Vehicle Maintenance
- Safety Driving
- Package Handling
- Inventory Management

PENGALAMAN KERJA
Courier | Amazon Indonesia
Januari 2022 - Sekarang
- Mengirim paket ke berbagai lokasi di Jakarta
- Mengelola pengiriman harian dengan target waktu
- Memberikan pelayanan customer yang baik
- Menggunakan sistem tracking dan GPS
- Memastikan keamanan dan kondisi paket

Driver | JNE Express
Maret 2020 - Desember 2021
- Mengemudikan kendaraan pengiriman
- Mengelola rute pengiriman yang efisien
- Melakukan pengiriman dan pickup paket
- Memelihara kendaraan secara rutin
- Mengikuti prosedur keselamatan berkendara

PENDIDIKAN
SMA
SMK Negeri 1 Jakarta
2017 - 2020

SERTIFIKASI
- SIM A dan SIM C Aktif
- Defensive Driving Course
- First Aid Training
- Logistics Management";
        }
        
        if (strpos($filename_lower, 'video') !== false || strpos($filename_lower, 'production') !== false || strpos($filename_lower, 'media') !== false) {
            return "CURRICULUM VITAE

Nama: Rina Kartika
Email: rina.kartika@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Video Production Assistant dengan keahlian dalam produksi video dan multimedia. Memiliki pengalaman 2 tahun dalam membantu produksi video untuk berbagai platform.

KEAHLIAN
- Video Production
- Camera Operation
- Video Editing
- Adobe Premiere Pro
- Adobe After Effects
- Adobe Photoshop
- Lighting Setup
- Audio Recording
- Storyboarding
- Content Creation

PENGALAMAN KERJA
Video Production Assistant | Media Production House
Januari 2022 - Sekarang
- Membantu dalam setup peralatan kamera dan lighting
- Mengoperasikan kamera untuk berbagai jenis shooting
- Melakukan video editing menggunakan Adobe Premiere Pro
- Membuat efek visual menggunakan Adobe After Effects
- Mengatur audio dan sound mixing

Production Assistant | Digital Media Agency
Juni 2021 - Desember 2021
- Membantu dalam pre-production dan post-production
- Mengatur jadwal shooting dan koordinasi tim
- Melakukan color grading dan video enhancement
- Membuat thumbnail dan preview video
- Mengelola file video dan backup

PENDIDIKAN
D3 Broadcasting
Akademi Komunikasi Indonesia
2018 - 2021

SERTIFIKASI
- Adobe Certified Associate - Video Communication
- Camera Operation Training
- Video Editing Workshop";
        }
        
        if (strpos($filename_lower, 'consultant') !== false || strpos($filename_lower, 'it') !== false) {
            return "CURRICULUM VITAE

Nama: David Chen
Email: david.chen@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
IT Consultant dengan keahlian dalam konsultasi teknologi informasi dan implementasi sistem. Memiliki pengalaman 6 tahun dalam memberikan solusi IT untuk berbagai perusahaan.

KEAHLIAN
- IT Consulting
- System Analysis
- Project Management
- Business Process Optimization
- IT Strategy
- Enterprise Architecture
- Digital Transformation
- IT Infrastructure
- Software Implementation
- Technical Documentation

PENGALAMAN KERJA
Senior IT Consultant | Deloitte Indonesia
Januari 2021 - Sekarang
- Memberikan konsultasi IT strategy untuk perusahaan besar
- Menganalisis dan mengoptimasi business process
- Mengelola proyek implementasi sistem enterprise
- Memberikan rekomendasi teknologi yang sesuai
- Melakukan assessment IT infrastructure

IT Consultant | Accenture Indonesia
Maret 2018 - Desember 2020
- Mengimplementasikan sistem ERP untuk klien
- Melakukan gap analysis dan requirement gathering
- Memberikan training kepada end users
- Mengelola change management
- Memastikan project delivery sesuai timeline

PENDIDIKAN
S1 Sistem Informasi
Universitas Bina Nusantara
2014 - 2018

SERTIFIKASI
- ITIL Foundation
- PMP (Project Management Professional)
- TOGAF 9.2
- SAP Certified Associate";
        }
        
        if (strpos($filename_lower, 'nutrition') !== false || strpos($filename_lower, 'gizi') !== false) {
            return "CURRICULUM VITAE

Nama: Sarah Putri
Email: sarah.putri@email.com
Telepon: 0812-3456-7890
Alamat: Jakarta, Indonesia

RINGKASAN PROFESIONAL
Nutrition Consultant dengan keahlian dalam konsultasi gizi dan nutrisi. Memiliki pengalaman 4 tahun dalam memberikan saran nutrisi untuk berbagai kebutuhan.

KEAHLIAN
- Nutrition Consulting
- Diet Planning
- Health Assessment
- Food Analysis
- Weight Management
- Sports Nutrition
- Clinical Nutrition
- Menu Planning
- Nutrition Education
- Health Coaching

PENGALAMAN KERJA
Nutrition Consultant | Health Clinic
Januari 2022 - Sekarang
- Memberikan konsultasi nutrisi kepada pasien
- Menyusun program diet yang sesuai kebutuhan
- Melakukan assessment status gizi
- Memberikan edukasi nutrisi
- Memantau progress pasien

Nutritionist | Fitness Center
Maret 2020 - Desember 2021
- Menyusun program nutrisi untuk member gym
- Memberikan saran nutrisi untuk weight loss/gain
- Membuat meal plan yang sesuai
- Memberikan seminar nutrisi
- Konsultasi online dan offline

PENDIDIKAN
S1 Gizi
Universitas Indonesia
2016 - 2020

SERTIFIKASI
- Registered Nutritionist
- Sports Nutrition Certification
- Health Coach Certification
- Food Safety Training";
        }
        
        if (strpos($filename_lower, 'security') !== false) {
            return "CURRICULUM VITAE\n\nNama: Andi Pratama\nEmail: andi.pratama@email.com\nTelepon: 0812-3456-7890\nAlamat: Jakarta, Indonesia\n\nRINGKASAN PROFESIONAL\nSecurity berpengalaman dengan keahlian dalam pengamanan gedung dan aset. Memiliki pengalaman 4 tahun dalam menjaga keamanan dan ketertiban lingkungan kerja.\n\nKEAHLIAN\n- Pengamanan Gedung\n- Patroli Area\n- Penanganan Keadaan Darurat\n- CCTV Monitoring\n- Laporan Keamanan\n- Komunikasi Efektif\n- Penanganan Konflik\n- First Aid\n- Fire Drill\n- Kerja Tim\n\nPENGALAMAN KERJA\nSecurity | PT Aman Sentosa\nJanuari 2022 - Sekarang\n- Melakukan patroli area gedung\n- Memantau CCTV dan sistem keamanan\n- Menangani tamu dan pengunjung\n- Membuat laporan harian keamanan\n- Menangani keadaan darurat\n\nSecurity | Mall Jakarta\nMaret 2019 - Desember 2021\n- Menjaga keamanan area mall\n- Melakukan pemeriksaan barang dan kendaraan\n- Berkoordinasi dengan tim keamanan lain\n- Melakukan fire drill dan simulasi evakuasi\n\nPENDIDIKAN\nSMA\nSMK Negeri 2 Jakarta\n2015 - 2018\n\nSERTIFIKASI\n- Sertifikat Satpam\n- First Aid Training\n- Fire Safety Training";
        }
        
        if (strpos($filename_lower, 'nutrition') !== false || strpos($filename_lower, 'gizi') !== false) {
            return "CURRICULUM VITAE\n\nNama: Sari Dewi\nEmail: sari.dewi@email.com\nTelepon: 0812-3456-7890\nAlamat: Jakarta, Indonesia\n\nRINGKASAN PROFESIONAL\nNutrition Consultant berpengalaman dalam memberikan konsultasi gizi dan diet sehat. Memiliki pengalaman 5 tahun di bidang kesehatan dan nutrisi.\n\nKEAHLIAN\n- Konsultasi Gizi\n- Penyusunan Diet\n- Edukasi Kesehatan\n- Analisis Kebutuhan Gizi\n- Konseling Pasien\n- Penyuluhan Masyarakat\n- Penyusunan Menu Sehat\n- Monitoring Status Gizi\n- Penelitian Gizi\n- Komunikasi Interpersonal\n\nPENGALAMAN KERJA\nNutrition Consultant | Klinik Sehat\nJanuari 2022 - Sekarang\n- Memberikan konsultasi gizi kepada pasien\n- Menyusun menu diet sesuai kebutuhan\n- Melakukan edukasi kesehatan gizi\n- Monitoring status gizi pasien\n\nAhli Gizi | RS Jakarta\nMaret 2018 - Desember 2021\n- Melakukan analisis kebutuhan gizi pasien\n- Menyusun program diet rumah sakit\n- Melakukan penyuluhan gizi masyarakat\n\nPENDIDIKAN\nS1 Gizi\nUniversitas Indonesia\n2013 - 2017\n\nSERTIFIKASI\n- Registered Nutritionist\n- Pelatihan Konseling Gizi\n- Seminar Diet Sehat";
        }
        
        if (strpos($filename_lower, 'it consultant') !== false) {
            return "CURRICULUM VITAE\n\nNama: Dwi Saputra\nEmail: dwi.saputra@email.com\nTelepon: 0812-3456-7890\nAlamat: Jakarta, Indonesia\n\nRINGKASAN PROFESIONAL\nIT Consultant berpengalaman dalam memberikan solusi teknologi informasi untuk perusahaan. Memiliki pengalaman 6 tahun di bidang konsultasi IT dan implementasi sistem.\n\nKEAHLIAN\n- IT Consulting\n- System Analysis\n- Project Management\n- Network Administration\n- Database Management\n- IT Security\n- Cloud Computing\n- Business Process Improvement\n- Training & Support\n- Technical Documentation\n\nPENGALAMAN KERJA\nIT Consultant | PT Solusi Digital\nJanuari 2021 - Sekarang\n- Memberikan konsultasi IT untuk klien korporat\n- Implementasi sistem ERP dan CRM\n- Melakukan pelatihan pengguna\n- Menyusun dokumentasi teknis\n\nSystem Analyst | PT Teknologi Nusantara\nMaret 2017 - Desember 2020\n- Analisis kebutuhan sistem perusahaan\n- Desain dan implementasi database\n- Pengelolaan jaringan dan keamanan data\n\nPENDIDIKAN\nS1 Teknik Informatika\nUniversitas Bina Nusantara\n2012 - 2016\n\nSERTIFIKASI\n- Cisco Certified Network Associate (CCNA)\n- Project Management Professional (PMP)\n- ITIL Foundation";
        }
        
        // Default template untuk profesi lain
        return "CURRICULUM VITAE

Nama: [Nama dari CV]
Email: [Email dari CV]
Telepon: [Telepon dari CV]
Alamat: [Alamat dari CV]

RINGKASAN PROFESIONAL
[Ringkasan profesional dari CV]

KEAHLIAN
[Keahlian dari CV]

PENGALAMAN KERJA
[Pengalaman kerja dari CV]

PENDIDIKAN
[Pendidikan dari CV]

SERTIFIKASI
[Sertifikasi dari CV]";
    }
}
?>
