<?php
class CVParser {
    /**
     * Parse CV berdasarkan tipe file
     */
    public function parseCV($file_path, $file_type) {
        // SOLUSI TEMPORARY: Untuk demo skripsi, kita akan menggunakan simulasi parsing
        // Dalam implementasi nyata, Anda perlu menggunakan library yang tepat
        
        // Pemeriksaan apakah file ada dan bisa dibaca
        if (!file_exists($file_path) || !is_readable($file_path)) {
            error_log("File tidak ditemukan atau tidak bisa dibaca: " . $file_path);
            return "Error: File tidak ditemukan atau tidak bisa dibaca";
        }
        
        // Cek ukuran file
        if (filesize($file_path) === 0) {
            error_log("File kosong: " . $file_path);
            return "Error: File kosong";
        }
        
        // Logging file yang diupload
        error_log("Memproses file: " . $file_path . " (Tipe: " . $file_type . ")");
        
        // Demo: Ekstrak teks berdasarkan tipe file
        switch ($file_type) {
            case 'application/pdf':
                return $this->simulateParsePDF($file_path);
            
            case 'application/msword': // DOC
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // DOCX
                return $this->simulateParseWord($file_path);
            
            default:
                // Jika tipe file tidak dikenali, gunakan simulasi default
                return $this->getSimulatedCVContent();
        }
    }
    
    /**
     * Simulasi parsing PDF
     * Dalam implementasi nyata, gunakan library seperti Smalot\PdfParser
     */
    private function simulateParsePDF($file_path) {
        // Dapatkan nama file untuk identifikasi log
        $filename = basename($file_path);
        error_log("Simulasi parsing PDF untuk file: " . $filename);
        
        // Dapatkan teks simulasi
        return $this->getSimulatedCVContent();
    }
    
    /**
     * Simulasi parsing DOC/DOCX
     * Dalam implementasi nyata, gunakan library seperti PhpOffice\PhpWord
     */
    private function simulateParseWord($file_path) {
        // Dapatkan nama file untuk identifikasi log
        $filename = basename($file_path);
        error_log("Simulasi parsing Word untuk file: " . $filename);
        
        // Dapatkan teks simulasi
        return $this->getSimulatedCVContent();
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