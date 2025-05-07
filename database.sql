-- Membuat database
CREATE DATABASE IF NOT EXISTS jobmatch_db;
USE jobmatch_db;

-- Membuat tabel kategori
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL
);

-- Membuat tabel lowongan
CREATE TABLE IF NOT EXISTS lowongan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    perusahaan VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    persyaratan TEXT NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    kategori_id INT NOT NULL,
    sumber VARCHAR(50) NOT NULL,
    tanggal_posting DATE NOT NULL,
    tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

-- Membuat tabel keywords
CREATE TABLE IF NOT EXISTS keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL,
    kategori_id INT,
    bobot FLOAT DEFAULT 1.0,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

-- Mengisi data kategori
INSERT INTO kategori (nama_kategori) VALUES
('IT & Software'),
('Marketing'),
('Design'),
('Sales'),
('Customer Service'),
('Finance & Accounting'),
('Human Resources'),
('Engineering'),
('Administration'),
('Education & Training');

-- Mengisi data keywords (ini contoh untuk kategori IT)
INSERT INTO keywords (keyword, kategori_id, bobot) VALUES
('php', 1, 1.5),
('javascript', 1, 1.5),
('java', 1, 1.5),
('python', 1, 1.5),
('html', 1, 1.0),
('css', 1, 1.0),
('sql', 1, 1.2),
('mysql', 1, 1.2),
('postgresql', 1, 1.2),
('mongodb', 1, 1.2),
('react', 1, 1.3),
('angular', 1, 1.3),
('vue', 1, 1.3),
('node.js', 1, 1.3),
('express', 1, 1.2),
('laravel', 1, 1.2),
('codeigniter', 1, 1.2),
('wordpress', 1, 1.1),
('git', 1, 1.1),
('docker', 1, 1.3),
('kubernetes', 1, 1.3),
('aws', 1, 1.4),
('azure', 1, 1.4),
('google cloud', 1, 1.4),
('devops', 1, 1.4),
('agile', 1, 1.1),
('scrum', 1, 1.1),
('jira', 1, 1.0),
('machine learning', 1, 1.5),
('data science', 1, 1.5),
('ai', 1, 1.5),
('artificial intelligence', 1, 1.5),
('mobile development', 1, 1.3),
('android', 1, 1.2),
('ios', 1, 1.2),
('swift', 1, 1.2),
('kotlin', 1, 1.2),
('flutter', 1, 1.2),
('react native', 1, 1.2),
('web development', 1, 1.3),
('frontend', 1, 1.2),
('backend', 1, 1.2),
('fullstack', 1, 1.3),
('ui/ux', 1, 1.1),
('database administrator', 1, 1.3),
('network administrator', 1, 1.3),
('system administrator', 1, 1.3),
('cybersecurity', 1, 1.4),
('security', 1, 1.3),
('penetration testing', 1, 1.3),
('data analyst', 1, 1.3),
('business intelligence', 1, 1.3);

-- Mengisi contoh data lowongan
INSERT INTO lowongan (judul, perusahaan, deskripsi, persyaratan, lokasi, kategori_id, sumber, tanggal_posting) VALUES
('Senior PHP Developer', 'TechNova Solutions', 
'Kami mencari Senior PHP Developer untuk bergabung dengan tim pengembangan kami. Anda akan bertanggung jawab untuk mengembangkan dan memelihara aplikasi web berbasis PHP, bekerja sama dengan tim untuk menentukan kebutuhan aplikasi, dan memastikan kualitas kode yang tinggi.',
'- Minimal 3 tahun pengalaman dengan PHP
- Pengalaman dengan Laravel atau CodeIgniter
- Pengetahuan tentang MySQL atau PostgreSQL
- Kemampuan HTML, CSS, dan JavaScript yang baik
- Pengalaman dengan version control (Git)
- Kemampuan komunikasi yang baik',
'Jakarta', 1, 'LinkedIn', '2025-04-10'),

('Web Developer', 'Digital Kreasi Indonesia',
'Digital Kreasi Indonesia sedang mencari Web Developer yang berpengalaman untuk bergabung dengan tim kami. Anda akan bekerja pada berbagai proyek web untuk klien dari berbagai industri.',
'- Menguasai HTML, CSS, JavaScript
- Menguasai PHP dan MySQL
- Pengalaman dengan WordPress
- Memahami responsive design
- Familiar dengan Git
- Mampu bekerja dalam tim maupun individu',
'Bandung', 1, 'Jobstreet', '2025-04-12'),

('Front-end Developer', 'Startup Media',
'Startup Media mencari Front-end Developer untuk membantu mengembangkan antarmuka pengguna yang interaktif dan responsif untuk platform media kami.',
'- Pengalaman dengan HTML, CSS, dan JavaScript
- Menguasai ReactJS atau VueJS
- Pengalaman dengan CSS preprocessors (SASS/LESS)
- Memahami prinsip UI/UX design
- Kemampuan untuk menulis kode yang bersih dan terorganisir
- Portfolio proyek front-end sebelumnya',
'Jakarta', 1, 'LinkedIn', '2025-04-11'),

('Full Stack Developer', 'InnoTech Solutions',
'InnoTech Solutions mencari Full Stack Developer untuk membangun aplikasi web modern. Anda akan terlibat dalam seluruh proses pengembangan, dari konsep hingga implementasi.',
'- Pengalaman dengan JavaScript (Node.js) di backend
- Menguasai React atau Angular untuk frontend
- Pengalaman dengan database SQL dan NoSQL
- Familiar dengan REST API dan GraphQL
- Pengalaman dengan CI/CD dan deployment
- Minimal 2 tahun pengalaman dalam pengembangan web',
'Remote', 1, 'Jobstreet', '2025-04-09'),

('Database Administrator', 'Data Insight Corp',
'Data Insight Corp mencari Database Administrator untuk mengelola dan mengoptimalkan database perusahaan. Anda akan bertanggung jawab untuk maintenance, backup, dan keamanan data.',
'- Minimal 3 tahun pengalaman sebagai DBA
- Penguasaan MySQL dan PostgreSQL
- Pengalaman dengan optimasi performa database
- Kemampuan menulis dan mengoptimasi query SQL
- Pengalaman dalam backup dan recovery
- Pemahaman tentang keamanan database',
'Surabaya', 1, 'LinkedIn', '2025-04-08'),

('UI/UX Designer', 'Creative Design Studio',
'Creative Design Studio mencari UI/UX Designer berbakat untuk membuat antarmuka pengguna yang menarik dan fungsional untuk aplikasi web dan mobile.',
'- Keahlian dalam Adobe XD, Figma, atau Sketch
- Portfolio yang menunjukkan proyek UI/UX sebelumnya
- Pemahaman tentang prinsip desain dan pengalaman pengguna
- Kemampuan untuk membuat wireframes dan prototypes
- Kemampuan komunikasi yang baik untuk berkolaborasi dengan developer
- Minimal 2 tahun pengalaman di bidang UI/UX',
'Jakarta', 3, 'Jobstreet', '2025-04-13'),

('Marketing Specialist', 'Brand Success Agency',
'Brand Success Agency mencari Marketing Specialist untuk membantu klien kami dalam merancang dan mengimplementasikan strategi pemasaran digital.',
'- Pengalaman dalam digital marketing
- Pemahaman tentang SEO, SEM, dan social media marketing
- Kemampuan analitis untuk mengukur dan melaporkan performa kampanye
- Kreativitas dalam membuat konten pemasaran
- Kemampuan komunikasi dan presentasi yang baik
- Minimal S1 Marketing atau bidang terkait',
'Yogyakarta', 2, 'LinkedIn', '2025-04-11'),

('Content Writer', 'Digital Content Solutions',
'Digital Content Solutions mencari Content Writer untuk membuat berbagai jenis konten untuk platform digital klien kami, termasuk blog, artikel, dan konten media sosial.',
'- Kemampuan menulis yang sangat baik dalam Bahasa Indonesia dan Inggris
- Pengalaman dalam content writing untuk platform digital
- Pemahaman tentang SEO content writing
- Kemampuan untuk menulis dalam berbagai gaya sesuai kebutuhan klien
- Kreativitas dan kemampuan research yang baik
- Minimal S1 Komunikasi, Sastra, atau bidang terkait',
'Remote', 2, 'Jobstreet', '2025-04-12'),

('Financial Analyst', 'Prosperity Finance Group',
'Prosperity Finance Group mencari Financial Analyst untuk menganalisis data keuangan dan membantu dalam perencanaan keuangan perusahaan.',
'- Minimal S1 Akuntansi, Keuangan, atau bidang terkait
- Pemahaman tentang laporan keuangan dan analisis
- Kemampuan menggunakan Excel dan software keuangan
- Kemampuan analitis dan pemecahan masalah yang baik
- Minimal 2 tahun pengalaman di bidang keuangan
- Sertifikasi CFA/CPA menjadi nilai tambah',
'Jakarta', 6, 'LinkedIn', '2025-04-10'),

('HR Recruitment Specialist', 'Talent Source Indonesia',
'Talent Source Indonesia mencari HR Recruitment Specialist untuk membantu proses rekrutmen dan seleksi kandidat untuk berbagai posisi.',
'- Minimal S1 Psikologi, Manajemen SDM, atau bidang terkait
- Pengalaman dalam rekrutmen dan seleksi kandidat
- Pemahaman tentang teknik interviewing dan assessment
- Kemampuan komunikasi dan interpersonal yang baik
- Pengetahuan tentang hukum ketenagakerjaan di Indonesia
- Minimal 2 tahun pengalaman di bidang HR',
'Bandung', 7, 'Jobstreet', '2025-04-09');
