# JobMatch - Sistem Pendukung Keputusan Pencarian Lowongan Kerja

## Deskripsi
JobMatch adalah sistem pendukung keputusan (SPK) yang menggunakan algoritma Naive Bayes untuk mencocokkan CV (Curriculum Vitae) dengan lowongan kerja yang tersedia. Sistem ini dirancang untuk membantu pencari kerja menemukan lowongan yang paling sesuai dengan keahlian dan pengalaman mereka.

## Fitur Utama
- Upload dan parsing CV dalam format PDF, DOC, DOCX, dan TXT
- Implementasi algoritma Naive Bayes untuk klasifikasi dokumen
- Pencocokan CV dengan lowongan berdasarkan keyword dan kategori
- Deteksi bahasa otomatis (Indonesia/Inggris)
- Interface web yang user-friendly
- Sistem scoring yang akurat untuk ranking hasil

## Struktur Database
### Tabel Kategori
- 50 kategori pekerjaan (IT & Software, Healthcare & Medical, dll)

### Tabel Keywords
- 147+ keyword dengan bobot yang berbeda
- Kategori-specific keywords
- Support bahasa Indonesia dan Inggris

### Tabel Lowongan
- Informasi lengkap lowongan kerja
- Deskripsi dan persyaratan
- Kategori dan lokasi

## Algoritma Naive Bayes
### Formula yang Digunakan:
```
P(C|x) = P(C) * ∏ P(xi|C)
```
Dimana:
- P(C|x) = Posterior probability (probabilitas kategori diberikan dokumen)
- P(C) = Prior probability (probabilitas kategori)
- P(xi|C) = Likelihood (probabilitas kata xi muncul dalam kategori C)

### Implementasi:
1. **Preprocessing**: Text cleaning, stopword removal, tokenization
2. **Feature Extraction**: Keyword extraction dengan bobot
3. **Probability Calculation**: Log probability untuk stabilitas numerik
4. **Smoothing**: Laplace smoothing untuk menghindari zero probability
5. **Normalization**: Scaling dan normalisasi skor akhir

## Cara Penggunaan
1. **Upload CV**: Pilih file CV dalam format yang didukung
2. **Processing**: Sistem akan menganalisis CV menggunakan Naive Bayes
3. **Results**: Lihat lowongan yang cocok dengan skor kecocokan
4. **Details**: Klik lowongan untuk melihat detail lengkap

## Teknologi yang Digunakan
- **Backend**: PHP 8.0+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Algorithm**: Naive Bayes Classifier
- **PDF Parsing**: pdftotext, PdfParser library

## File Struktur
```
jobmatch_project/
├── classes/
│   ├── CVParser.php          # CV parsing dan extraction
│   └── NaiveBayes.php        # Implementasi algoritma Naive Bayes
├── config/
│   └── database.php          # Konfigurasi database
├── css/                      # Stylesheet
├── data/
│   ├── jobmatch.db           # Database SQLite (alternatif)
│   ├── stopwords_en.txt      # Stopwords bahasa Inggris
│   └── stopwords_id.txt      # Stopwords bahasa Indonesia
├── db/
│   └── jobmatch_db.sql       # Database schema dan data
├── uploads/                  # Folder upload CV
├── index.html               # Halaman utama
├── upload.php               # Halaman upload CV
├── process_cv.php           # Proses CV dengan Naive Bayes
├── results.php              # Halaman hasil pencarian
└── job_detail.php           # Detail lowongan
```

## Troubleshooting

### Masalah Upload CV
1. Pastikan file tidak lebih dari 5MB
2. Gunakan format PDF, DOC, DOCX, atau TXT
3. Periksa permission folder uploads/

### Masalah Parsing PDF
1. Install pdftotext: `sudo apt-get install poppler-utils`
2. Atau install PdfParser: `composer require smalot/pdfparser`

### Masalah Database
1. Import file `db/jobmatch_db.sql`
2. Periksa konfigurasi di `config/database.php`
3. Pastikan MySQL service berjalan

# PERBAIKAN SISTEM SPK JOBMATCH

## Masalah yang Ditemukan
Sistem SPK dengan metode Naive Bayes untuk pencarian lowongan kerja berdasarkan CV mengalami masalah berikut:

1. **Hasil tidak sesuai**: CV yang diupload selalu menghasilkan lowongan yang tidak sesuai.
2. **Ketergantungan pada Template**: CVParser menggunakan template berdasarkan nama file, sehingga CV yang tidak memiliki template spesifik akan menggunakan template default.

## Solusi yang Diterapkan

### 1. Perbaikan CVParser.php
**Masalah**: CVParser bergantung pada template dan selalu menggunakan template default untuk CV yang tidak bisa diekstrak.
**Solusi**:
- Menambahkan pengecekan `isSimulatedContent()` untuk mendeteksi template yang disimulasikan
- Mengubah logika ekstraksi untuk benar-benar mengekstrak teks dari file CV
- Hanya menggunakan template minimal sebagai fallback terakhir jika semua metode ekstraksi gagal
- Template minimal tidak mengandung bias ke profesi tertentu

### 2. Perbaikan Algoritma Naive Bayes
**Masalah**: Algoritma tidak bisa membedakan antara konten CV asli dan template.
**Solusi**:
- Menambahkan pengecekan `isTemplateContent()` untuk mendeteksi CV yang berisi template
- Menggunakan fallback method untuk CV template dengan analisis keyword sederhana
- Memperbaiki scoring system dengan threshold yang lebih ketat
- Menambahkan bonus/penalty berdasarkan kategori yang sama/berbeda