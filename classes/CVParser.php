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
            
            switch ($file_type) {
                case 'application/pdf':
                    $content = $this->parsePDF($file_path);
                    break;
                    
                case 'application/msword': // DOC
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // DOCX
                    $content = $this->parseWord($file_path);
                    break;
                    
                case 'text/plain': // TXT
                    $content = $this->parseTXT($file_path);
                    break;
                    
                default:
                    // Jika tipe file tidak dikenali, gunakan simulasi
                    error_log("Using simulated content for unsupported file type: $file_type");
                    return $this->getSimulatedCVContent();
            }
            
            if (empty($content)) {
                error_log("No content extracted from file");
                return $this->getSimulatedCVContent(); // Fallback to simulation if extraction fails
            }
            
            // Clean and normalize the content
            $content = $this->cleanContent($content);
            
            error_log("Successfully parsed CV. Content length: " . strlen($content));
            return $content;
            
        } catch (Exception $e) {
            error_log("Error parsing CV: " . $e->getMessage());
            return $this->getSimulatedCVContent(); // Fallback to simulation on error
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
                    return implode("\n", $output);
                }
            }
            
            // Jika pdftotext gagal atau tidak tersedia, gunakan simulasi
            error_log("Using simulated content for PDF: " . basename($file_path));
            return $this->getSimulatedCVContent();
            
        } catch (Exception $e) {
            error_log("Error parsing PDF: " . $e->getMessage());
            return $this->getSimulatedCVContent();
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
                        if (!empty($content)) {
                            return $content;
                        }
                    }
                    $zip->close();
                }
            }
            
            // Jika gagal atau untuk DOC, gunakan simulasi
            error_log("Using simulated content for Word document: " . basename($file_path));
            return $this->getSimulatedCVContent();
            
        } catch (Exception $e) {
            error_log("Error parsing Word document: " . $e->getMessage());
            return $this->getSimulatedCVContent();
        }
    }
    
    private function parseTXT($file_path) {
        try {
            $content = file_get_contents($file_path);
            if ($content === false || empty($content)) {
                throw new Exception("Tidak dapat membaca file teks.");
            }
            return $content;
            
        } catch (Exception $e) {
            error_log("Error parsing TXT file: " . $e->getMessage());
            return $this->getSimulatedCVContent();
        }
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
     * Menghasilkan teks CV simulasi untuk tujuan demo
     */
    private function getSimulatedCVContent() {
        // Kita buat beberapa template CV simulasi untuk demo
        $templates = [
            // Template 1: Web Developer
            "CURRICULUM VITAE

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

PENGALAMAN KERJA
Senior Web Developer | PT Digital Solusi Indonesia
Januari 2022 - Sekarang
- Mengembangkan dan memelihara multiple web application menggunakan Laravel dan Vue.js
- Mengoptimasi performa database dan query SQL untuk meningkatkan kecepatan aplikasi
- Mengimplementasikan CI/CD pipeline menggunakan GitHub Actions
- Mentoring junior developer dalam best practices

Web Developer | Kreatif Tech
Maret 2019 - Desember 2021
- Mengembangkan website untuk berbagai klien menggunakan PHP, MySQL, dan JavaScript
- Membuat dan mengintegrasikan RESTful API
- Mengimplementasikan responsive design untuk website klien
- Bekerja dalam tim Agile dan berkolaborasi dengan designer dan product manager

PENDIDIKAN
S1 Teknik Informatika
Universitas Indonesia
2015 - 2019",

            // Template 2: Marketing Specialist
            "CURRICULUM VITAE

Nama: Dina Sari
Email: dina.sari@email.com
Telepon: 0857-1234-5678
Alamat: Bandung, Indonesia

RINGKASAN PROFESIONAL
Marketing Specialist dengan pengalaman lebih dari 4 tahun dalam digital marketing. Memiliki keahlian dalam SEO, content marketing, dan social media management.

KEAHLIAN
- Digital Marketing Strategy
- SEO & SEM
- Content Marketing
- Social Media Management
- Email Marketing
- Google Analytics
- Market Research
- Content Creation
- Campaign Management

PENGALAMAN KERJA
Marketing Specialist | Brand Solutions Agency
Februari 2021 - Sekarang
- Mengelola kampanye digital marketing untuk berbagai klien
- Mengembangkan dan mengimplementasikan strategi SEO dan content marketing
- Mengelola social media accounts dan menganalisis performa konten
- Menyusun laporan performa marketing bulanan untuk klien

Digital Marketing Associate | MarketPro Indonesia
Juni 2019 - Januari 2021
- Membuat dan mengelola konten untuk social media dan website
- Melakukan riset keyword dan optimasi konten untuk SEO
- Membantu dalam perencanaan dan eksekusi email marketing campaigns
- Menganalisis data engagement dan conversion menggunakan Google Analytics

PENDIDIKAN
S1 Manajemen Bisnis
Universitas Padjajaran
2015 - 2019",

            // Template 3: Data Analyst
            "CURRICULUM VITAE

Nama: Rudi Hartono
Email: rudi.hartono@email.com
Telepon: 0878-9012-3456
Alamat: Surabaya, Indonesia

RINGKASAN PROFESIONAL
Data Analyst dengan pengalaman 3 tahun dalam menganalisis data dan membuat visualisasi untuk mendukung keputusan bisnis. Familiar dengan berbagai tools data analytics dan programming languages.

KEAHLIAN
- Data Analysis & Visualization
- SQL, MySQL, PostgreSQL
- Python (Pandas, NumPy, Matplotlib)
- R Programming
- Machine Learning Basics
- Statistical Analysis
- Dashboard Development (Tableau, Power BI)
- Excel Advanced (VBA, Pivot Tables)
- Business Intelligence

PENGALAMAN KERJA
Data Analyst | Insight Analytics
Agustus 2022 - Sekarang
- Melakukan analisis data untuk mengidentifikasi tren dan pola
- Membuat dashboard interaktif menggunakan Tableau untuk visualisasi data
- Mengembangkan model prediktif sederhana menggunakan Python
- Berkolaborasi dengan tim bisnis untuk menghasilkan insights yang actionable

Junior Data Analyst | Tech Solutions Indonesia
Mei 2020 - Juli 2022
- Mengumpulkan, membersihkan, dan menganalisis dataset besar
- Mengelola database dan mengoptimasi query SQL
- Membuat laporan analisis reguler untuk stakeholders
- Membantu dalam implementasi sistem business intelligence

PENDIDIKAN
S1 Statistika
Institut Teknologi Sepuluh November
2016 - 2020"
        ];
        
        // Pilih template secara acak untuk simulasi
        return $templates[array_rand($templates)];
    }
}
?>
