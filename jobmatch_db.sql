-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 09:16 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jobmatch_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'IT & Software'),
(2, 'Marketing'),
(3, 'Design'),
(4, 'Sales'),
(5, 'Customer Service'),
(6, 'Finance & Accounting'),
(7, 'Human Resources'),
(8, 'Engineering'),
(9, 'Administration'),
(10, 'Education & Training'),
(11, 'Healthcare & Medical');

-- --------------------------------------------------------

--
-- Table structure for table `keywords`
--

CREATE TABLE `keywords` (
  `id` int(11) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `bobot` float DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `keywords`
--

INSERT INTO `keywords` (`id`, `keyword`, `kategori_id`, `bobot`) VALUES
(1, 'php', 1, 1.5),
(2, 'javascript', 1, 1.5),
(3, 'java', 1, 1.5),
(4, 'python', 1, 1.5),
(5, 'html', 1, 1),
(6, 'css', 1, 1),
(7, 'sql', 1, 1.2),
(8, 'mysql', 1, 1.2),
(9, 'postgresql', 1, 1.2),
(10, 'mongodb', 1, 1.2),
(11, 'react', 1, 1.3),
(12, 'angular', 1, 1.3),
(13, 'vue', 1, 1.3),
(14, 'node.js', 1, 1.3),
(15, 'express', 1, 1.2),
(16, 'laravel', 1, 1.2),
(17, 'codeigniter', 1, 1.2),
(18, 'wordpress', 1, 1.1),
(19, 'git', 1, 1.1),
(20, 'docker', 1, 1.3),
(21, 'kubernetes', 1, 1.3),
(22, 'aws', 1, 1.4),
(23, 'azure', 1, 1.4),
(24, 'google cloud', 1, 1.4),
(25, 'devops', 1, 1.4),
(26, 'agile', 1, 1.1),
(27, 'scrum', 1, 1.1),
(28, 'jira', 1, 1),
(29, 'machine learning', 1, 1.5),
(30, 'data science', 1, 1.5),
(31, 'ai', 1, 1.5),
(32, 'artificial intelligence', 1, 1.5),
(33, 'mobile development', 1, 1.3),
(34, 'android', 1, 1.2),
(35, 'ios', 1, 1.2),
(36, 'swift', 1, 1.2),
(37, 'kotlin', 1, 1.2),
(38, 'flutter', 1, 1.2),
(39, 'react native', 1, 1.2),
(40, 'web development', 1, 1.3),
(41, 'frontend', 1, 1.2),
(42, 'backend', 1, 1.2),
(43, 'fullstack', 1, 1.3),
(44, 'ui/ux', 1, 1.1),
(45, 'database administrator', 1, 1.3),
(46, 'network administrator', 1, 1.3),
(47, 'system administrator', 1, 1.3),
(48, 'cybersecurity', 1, 1.4),
(49, 'security', 1, 1.3),
(50, 'penetration testing', 1, 1.3),
(51, 'data analyst', 1, 1.3),
(52, 'business intelligence', 1, 1.3),
(53, 'midwife', 11, 1.5),
(54, 'bidan', 11, 1.5),
(55, 'nurse', 11, 1.5),
(56, 'healthcare', 11, 1.3),
(57, 'patient care', 11, 1.4),
(58, 'medical', 11, 1.3),
(59, 'obstetrics', 11, 1.4),
(60, 'maternal health', 11, 1.4),
(61, 'prenatal care', 11, 1.4),
(62, 'postnatal care', 11, 1.4);

-- --------------------------------------------------------

--
-- Table structure for table `lowongan`
--

CREATE TABLE `lowongan` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `perusahaan` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `persyaratan` text NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `sumber` varchar(50) NOT NULL,
  `tanggal_posting` date NOT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lowongan`
--

INSERT INTO `lowongan` (`id`, `judul`, `perusahaan`, `deskripsi`, `persyaratan`, `lokasi`, `kategori_id`, `sumber`, `tanggal_posting`, `tanggal_dibuat`) VALUES
(1, 'Senior PHP Developer', 'TechNova Solutions', 'Kami mencari Senior PHP Developer untuk bergabung dengan tim pengembangan kami. Anda akan bertanggung jawab untuk mengembangkan dan memelihara aplikasi web berbasis PHP, bekerja sama dengan tim untuk menentukan kebutuhan aplikasi, dan memastikan kualitas kode yang tinggi.', '- Minimal 3 tahun pengalaman dengan PHP\n- Pengalaman dengan Laravel atau CodeIgniter\n- Pengetahuan tentang MySQL atau PostgreSQL\n- Kemampuan HTML, CSS, dan JavaScript yang baik\n- Pengalaman dengan version control (Git)\n- Kemampuan komunikasi yang baik', 'Jakarta', 1, 'LinkedIn', '2025-04-10', '2025-04-17 12:50:10'),
(2, 'Web Developer', 'Digital Kreasi Indonesia', 'Digital Kreasi Indonesia sedang mencari Web Developer yang berpengalaman untuk bergabung dengan tim kami. Anda akan bekerja pada berbagai proyek web untuk klien dari berbagai industri.', '- Menguasai HTML, CSS, JavaScript\n- Menguasai PHP dan MySQL\n- Pengalaman dengan WordPress\n- Memahami responsive design\n- Familiar dengan Git\n- Mampu bekerja dalam tim maupun individu', 'Bandung', 1, 'Jobstreet', '2025-04-12', '2025-04-17 12:50:10'),
(3, 'Front-end Developer', 'Startup Media', 'Startup Media mencari Front-end Developer untuk membantu mengembangkan antarmuka pengguna yang interaktif dan responsif untuk platform media kami.', '- Pengalaman dengan HTML, CSS, dan JavaScript\n- Menguasai ReactJS atau VueJS\n- Pengalaman dengan CSS preprocessors (SASS/LESS)\n- Memahami prinsip UI/UX design\n- Kemampuan untuk menulis kode yang bersih dan terorganisir\n- Portfolio proyek front-end sebelumnya', 'Jakarta', 1, 'LinkedIn', '2025-04-11', '2025-04-17 12:50:10'),
(4, 'Full Stack Developer', 'InnoTech Solutions', 'InnoTech Solutions mencari Full Stack Developer untuk membangun aplikasi web modern. Anda akan terlibat dalam seluruh proses pengembangan, dari konsep hingga implementasi.', '- Pengalaman dengan JavaScript (Node.js) di backend\n- Menguasai React atau Angular untuk frontend\n- Pengalaman dengan database SQL dan NoSQL\n- Familiar dengan REST API dan GraphQL\n- Pengalaman dengan CI/CD dan deployment\n- Minimal 2 tahun pengalaman dalam pengembangan web', 'Remote', 1, 'Jobstreet', '2025-04-09', '2025-04-17 12:50:10'),
(5, 'Database Administrator', 'Data Insight Corp', 'Data Insight Corp mencari Database Administrator untuk mengelola dan mengoptimalkan database perusahaan. Anda akan bertanggung jawab untuk maintenance, backup, dan keamanan data.', '- Minimal 3 tahun pengalaman sebagai DBA\n- Penguasaan MySQL dan PostgreSQL\n- Pengalaman dengan optimasi performa database\n- Kemampuan menulis dan mengoptimasi query SQL\n- Pengalaman dalam backup dan recovery\n- Pemahaman tentang keamanan database', 'Surabaya', 1, 'LinkedIn', '2025-04-08', '2025-04-17 12:50:10'),
(6, 'UI/UX Designer', 'Creative Design Studio', 'Creative Design Studio mencari UI/UX Designer berbakat untuk membuat antarmuka pengguna yang menarik dan fungsional untuk aplikasi web dan mobile.', '- Keahlian dalam Adobe XD, Figma, atau Sketch\n- Portfolio yang menunjukkan proyek UI/UX sebelumnya\n- Pemahaman tentang prinsip desain dan pengalaman pengguna\n- Kemampuan untuk membuat wireframes dan prototypes\n- Kemampuan komunikasi yang baik untuk berkolaborasi dengan developer\n- Minimal 2 tahun pengalaman di bidang UI/UX', 'Jakarta', 3, 'Jobstreet', '2025-04-13', '2025-04-17 12:50:10'),
(7, 'Marketing Specialist', 'Brand Success Agency', 'Brand Success Agency mencari Marketing Specialist untuk membantu klien kami dalam merancang dan mengimplementasikan strategi pemasaran digital.', '- Pengalaman dalam digital marketing\n- Pemahaman tentang SEO, SEM, dan social media marketing\n- Kemampuan analitis untuk mengukur dan melaporkan performa kampanye\n- Kreativitas dalam membuat konten pemasaran\n- Kemampuan komunikasi dan presentasi yang baik\n- Minimal S1 Marketing atau bidang terkait', 'Yogyakarta', 2, 'LinkedIn', '2025-04-11', '2025-04-17 12:50:10'),
(8, 'Content Writer', 'Digital Content Solutions', 'Digital Content Solutions mencari Content Writer untuk membuat berbagai jenis konten untuk platform digital klien kami, termasuk blog, artikel, dan konten media sosial.', '- Kemampuan menulis yang sangat baik dalam Bahasa Indonesia dan Inggris\n- Pengalaman dalam content writing untuk platform digital\n- Pemahaman tentang SEO content writing\n- Kemampuan untuk menulis dalam berbagai gaya sesuai kebutuhan klien\n- Kreativitas dan kemampuan research yang baik\n- Minimal S1 Komunikasi, Sastra, atau bidang terkait', 'Remote', 2, 'Jobstreet', '2025-04-12', '2025-04-17 12:50:10'),
(9, 'Financial Analyst', 'Prosperity Finance Group', 'Prosperity Finance Group mencari Financial Analyst untuk menganalisis data keuangan dan membantu dalam perencanaan keuangan perusahaan.', '- Minimal S1 Akuntansi, Keuangan, atau bidang terkait\n- Pemahaman tentang laporan keuangan dan analisis\n- Kemampuan menggunakan Excel dan software keuangan\n- Kemampuan analitis dan pemecahan masalah yang baik\n- Minimal 2 tahun pengalaman di bidang keuangan\n- Sertifikasi CFA/CPA menjadi nilai tambah', 'Jakarta', 6, 'LinkedIn', '2025-04-10', '2025-04-17 12:50:10'),
(10, 'HR Recruitment Specialist', 'Talent Source Indonesia', 'Talent Source Indonesia mencari HR Recruitment Specialist untuk membantu proses rekrutmen dan seleksi kandidat untuk berbagai posisi.', '- Minimal S1 Psikologi, Manajemen SDM, atau bidang terkait\n- Pengalaman dalam rekrutmen dan seleksi kandidat\n- Pemahaman tentang teknik interviewing dan assessment\n- Kemampuan komunikasi dan interpersonal yang baik\n- Pengetahuan tentang hukum ketenagakerjaan di Indonesia\n- Minimal 2 tahun pengalaman di bidang HR', 'Bandung', 7, 'Jobstreet', '2025-04-09', '2025-04-17 12:50:10'),
(11, 'Midwife', 'RS Sehat Sentosa', 'RS Sehat Sentosa mencari Midwife yang berpengalaman untuk memberikan perawatan prenatal, persalinan, dan postnatal kepada pasien. Kandidat harus memiliki sertifikasi bidan dan pengalaman minimal 3 tahun.', '- Sertifikasi bidan yang valid\r\n- Pengalaman minimal 3 tahun sebagai midwife\r\n- Pengetahuan tentang perawatan prenatal dan postnatal\r\n- Kemampuan komunikasi yang baik dengan pasien\r\n- Mampu bekerja dalam tim medis', 'Jakarta', 11, 'Jobstreet', '2025-06-23', '2025-06-23 08:06:01'),
(12, 'Nurse', 'Klinik Medika', 'Klinik Medika membutuhkan Nurse yang berdedikasi untuk memberikan perawatan medis dan dukungan kepada pasien. Kandidat harus memiliki lisensi keperawatan dan pengalaman di bidang kesehatan.', '- Lisensi keperawatan yang valid\r\n- Pengalaman minimal 2 tahun di bidang keperawatan\r\n- Kemampuan melakukan tindakan medis dasar\r\n- Komunikatif dan empati terhadap pasien\r\n- Mampu bekerja shift', 'Bandung', 11, 'LinkedIn', '2025-06-23', '2025-06-23 08:06:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `keywords`
--
ALTER TABLE `keywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `lowongan`
--
ALTER TABLE `lowongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `keywords`
--
ALTER TABLE `keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `keywords`
--
ALTER TABLE `keywords`
  ADD CONSTRAINT `keywords_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);

--
-- Constraints for table `lowongan`
--
ALTER TABLE `lowongan`
  ADD CONSTRAINT `lowongan_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
