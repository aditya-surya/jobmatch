-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 11:37 AM
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
(11, 'Healthcare & Medical'),
(12, 'Legal & Paralegal'),
(13, 'Logistics & Supply Chain'),
(14, 'Hospitality & Tourism'),
(15, 'Construction & Real Estate'),
(16, 'Retail & E-commerce'),
(17, 'Manufacturing'),
(18, 'Research & Development'),
(19, 'Media & Communication'),
(20, 'Agriculture & Farming');

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
(62, 'postnatal care', 11, 1.4),
(63, 'legal', 12, 1.3),
(64, 'lawyer', 12, 1.4),
(65, 'attorney', 12, 1.4),
(66, 'paralegal', 12, 1.2),
(67, 'litigation', 12, 1.3),
(68, 'contract', 12, 1.2),
(69, 'law firm', 12, 1.3),
(70, 'legal counsel', 12, 1.4),
(71, 'logistics', 13, 1.3),
(72, 'supply chain', 13, 1.4),
(73, 'warehouse', 13, 1.2),
(74, 'inventory', 13, 1.2),
(75, 'procurement', 13, 1.3),
(76, 'shipping', 13, 1.2),
(77, 'distribution', 13, 1.3),
(78, 'hotel', 14, 1.3),
(79, 'hospitality', 14, 1.4),
(80, 'tourism', 14, 1.3),
(81, 'restaurant', 14, 1.2),
(82, 'chef', 14, 1.3),
(83, 'housekeeping', 14, 1.2),
(84, 'front office', 14, 1.2),
(85, 'construction', 15, 1.4),
(86, 'architect', 15, 1.3),
(87, 'civil engineer', 15, 1.4),
(88, 'property', 15, 1.3),
(89, 'real estate', 15, 1.3),
(90, 'building', 15, 1.2),
(91, 'project manager', 15, 1.3),
(92, 'retail', 16, 1.3),
(93, 'e-commerce', 16, 1.4),
(94, 'merchandising', 16, 1.3),
(95, 'store manager', 16, 1.3),
(96, 'sales associate', 16, 1.2),
(97, 'inventory management', 16, 1.2),
(98, 'manufacturing', 17, 1.4),
(99, 'production', 17, 1.3),
(100, 'quality control', 17, 1.3),
(101, 'assembly', 17, 1.2),
(102, 'operator', 17, 1.2),
(103, 'fabrication', 17, 1.3),
(104, 'research', 18, 1.4),
(105, 'development', 18, 1.3),
(106, 'laboratory', 18, 1.3),
(107, 'scientist', 18, 1.4),
(108, 'innovation', 18, 1.3),
(109, 'experimental', 18, 1.2),
(110, 'media', 19, 1.3),
(111, 'journalism', 19, 1.4),
(112, 'broadcasting', 19, 1.3),
(113, 'public relations', 19, 1.3),
(114, 'content creation', 19, 1.2),
(115, 'social media', 19, 1.3),
(116, 'agriculture', 20, 1.4),
(117, 'farming', 20, 1.3),
(118, 'plantation', 20, 1.3),
(119, 'harvesting', 20, 1.2),
(120, 'cultivation', 20, 1.3),
(121, 'livestock', 20, 1.3),
(122, 'pengembang', 1, 1.3),
(123, 'pemrograman', 1, 1.3),
(124, 'perangkat lunak', 1, 1.3),
(125, 'basis data', 1, 1.2),
(126, 'pengembangan web', 1, 1.3),
(127, 'aplikasi', 1, 1.2),
(128, 'sistem informasi', 1, 1.3),
(129, 'jaringan', 1, 1.2),
(130, 'keamanan', 1, 1.3),
(131, 'analisis', 1, 1.2),
(132, 'pemasaran', 2, 1.3),
(133, 'digital marketing', 2, 1.3),
(134, 'media sosial', 2, 1.2),
(135, 'promosi', 2, 1.2),
(136, 'periklanan', 2, 1.3),
(137, 'strategi pemasaran', 2, 1.3),
(138, 'riset pasar', 2, 1.2),
(139, 'desain', 3, 1.3),
(140, 'desain grafis', 3, 1.3),
(141, 'ilustrasi', 3, 1.2),
(142, 'kreatif', 3, 1.2),
(143, 'desainer', 3, 1.3),
(144, 'layout', 3, 1.2),
(145, 'multimedia', 3, 1.2),
(146, 'penjualan', 4, 1.3),
(147, 'pemasaran', 4, 1.3),
(148, 'target', 4, 1.2),
(149, 'negosiasi', 4, 1.3),
(150, 'pelanggan', 4, 1.2),
(151, 'account manager', 4, 1.3),
(152, 'layanan pelanggan', 5, 1.3),
(153, 'kepuasan pelanggan', 5, 1.3),
(154, 'pelayanan', 5, 1.2),
(155, 'komunikasi', 5, 1.2),
(156, 'pengaduan', 5, 1.2),
(157, 'dukungan pelanggan', 5, 1.3),
(158, 'keuangan', 6, 1.3),
(159, 'akuntansi', 6, 1.3),
(160, 'pembukuan', 6, 1.2),
(161, 'pajak', 6, 1.3),
(162, 'audit', 6, 1.3),
(163, 'anggaran', 6, 1.2),
(164, 'laporan keuangan', 6, 1.3),
(165, 'sumber daya manusia', 7, 1.3),
(166, 'rekrutmen', 7, 1.3),
(167, 'kepegawaian', 7, 1.2),
(168, 'pelatihan', 7, 1.2),
(169, 'pengembangan sdm', 7, 1.3),
(170, 'administrasi hr', 7, 1.2),
(171, 'teknik', 8, 1.3),
(172, 'mesin', 8, 1.3),
(173, 'listrik', 8, 1.2),
(174, 'industri', 8, 1.2),
(175, 'otomasi', 8, 1.3),
(176, 'maintenance', 8, 1.2),
(177, 'pengawasan', 8, 1.2),
(178, 'administrasi', 9, 1.3),
(179, 'sekretaris', 9, 1.3),
(180, 'manajemen kantor', 9, 1.2),
(181, 'arsip', 9, 1.2),
(182, 'dokumentasi', 9, 1.2),
(183, 'koordinasi', 9, 1.2),
(184, 'pendidikan', 10, 1.3),
(185, 'pengajar', 10, 1.3),
(186, 'pelatih', 10, 1.2),
(187, 'kurikulum', 10, 1.2),
(188, 'pembelajaran', 10, 1.3),
(189, 'pengembangan', 10, 1.2),
(190, 'kesehatan', 11, 1.3),
(191, 'medis', 11, 1.3),
(192, 'perawatan', 11, 1.2),
(193, 'dokter', 11, 1.4),
(194, 'perawat', 11, 1.3),
(195, 'farmasi', 11, 1.2),
(196, 'laboratorium', 11, 1.2),
(197, 'hukum', 12, 1.3),
(198, 'pengacara', 12, 1.4),
(199, 'konsultan hukum', 12, 1.3),
(200, 'litigasi', 12, 1.3),
(201, 'perizinan', 12, 1.2),
(202, 'kontrak', 12, 1.2),
(203, 'kepatuhan', 12, 1.2),
(204, 'logistik', 13, 1.3),
(205, 'rantai pasok', 13, 1.3),
(206, 'pergudangan', 13, 1.2),
(207, 'pengiriman', 13, 1.2),
(208, 'distribusi', 13, 1.3),
(209, 'inventori', 13, 1.2),
(210, 'pengadaan', 13, 1.2),
(211, 'perhotelan', 14, 1.3),
(212, 'pariwisata', 14, 1.3),
(213, 'restoran', 14, 1.2),
(214, 'katering', 14, 1.2),
(215, 'reservasi', 14, 1.2),
(216, 'pelayanan tamu', 14, 1.3),
(217, 'konstruksi', 15, 1.3),
(218, 'bangunan', 15, 1.3),
(219, 'proyek', 15, 1.2),
(220, 'properti', 15, 1.3),
(221, 'arsitektur', 15, 1.3),
(222, 'sipil', 15, 1.2),
(223, 'pengawasan proyek', 15, 1.2),
(224, 'ritel', 16, 1.3),
(225, 'toko online', 16, 1.3),
(226, 'penjualan online', 16, 1.2),
(227, 'manajemen toko', 16, 1.2),
(228, 'merchandising', 16, 1.2),
(229, 'operasional toko', 16, 1.2),
(230, 'manufaktur', 17, 1.3),
(231, 'produksi', 17, 1.3),
(232, 'pabrik', 17, 1.2),
(233, 'quality control', 17, 1.3),
(234, 'pengendalian mutu', 17, 1.2),
(235, 'operasi', 17, 1.2),
(236, 'penelitian', 18, 1.3),
(237, 'pengembangan', 18, 1.3),
(238, 'riset', 18, 1.3),
(239, 'inovasi', 18, 1.2),
(240, 'laboratorium', 18, 1.2),
(241, 'eksperimen', 18, 1.2),
(242, 'media', 19, 1.3),
(243, 'komunikasi', 19, 1.3),
(244, 'jurnalistik', 19, 1.2),
(245, 'penyiaran', 19, 1.2),
(246, 'konten', 19, 1.2),
(247, 'hubungan masyarakat', 19, 1.3),
(248, 'pertanian', 20, 1.3),
(249, 'perkebunan', 20, 1.3),
(250, 'peternakan', 20, 1.2),
(251, 'budidaya', 20, 1.2),
(252, 'agribisnis', 20, 1.3),
(253, 'pengolahan hasil', 20, 1.2);

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
(1, 'Senior PHP Developer', 'TechNova Solutions', 'Kami mencari Senior PHP Developer untuk bergabung dengan tim pengembangan kami. Anda akan bertanggung jawab untuk mengembangkan dan memelihara aplikasi web berbasis PHP, bekerja sama dengan tim untuk menentukan kebutuhan aplikasi, dan memastikan kualitas kode yang tinggi.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- Minimal 3 tahun pengalaman dengan PHP\n- Pengalaman dengan Laravel atau CodeIgniter\n- Pengetahuan tentang MySQL atau PostgreSQL\n- Kemampuan HTML, CSS, dan JavaScript yang baik\n- Pengalaman dengan version control (Git)\n- Kemampuan komunikasi yang baik', 'Jakarta', 1, 'LinkedIn', '2025-04-10', '2025-04-17 12:50:10'),
(2, 'Web Developer', 'Digital Kreasi Indonesia', 'Digital Kreasi Indonesia sedang mencari Web Developer yang berpengalaman untuk bergabung dengan tim kami. Anda akan bekerja pada berbagai proyek web untuk klien dari berbagai industri.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- Menguasai HTML, CSS, JavaScript\n- Menguasai PHP dan MySQL\n- Pengalaman dengan WordPress\n- Memahami responsive design\n- Familiar dengan Git\n- Mampu bekerja dalam tim maupun individu', 'Bandung', 1, 'Jobstreet', '2025-04-12', '2025-04-17 12:50:10'),
(3, 'Front-end Developer', 'Startup Media', 'Startup Media mencari Front-end Developer untuk membantu mengembangkan antarmuka pengguna yang interaktif dan responsif untuk platform media kami.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- Pengalaman dengan HTML, CSS, dan JavaScript\n- Menguasai ReactJS atau VueJS\n- Pengalaman dengan CSS preprocessors (SASS/LESS)\n- Memahami prinsip UI/UX design\n- Kemampuan untuk menulis kode yang bersih dan terorganisir\n- Portfolio proyek front-end sebelumnya', 'Jakarta', 1, 'LinkedIn', '2025-04-11', '2025-04-17 12:50:10'),
(4, 'Full Stack Developer', 'InnoTech Solutions', 'InnoTech Solutions mencari Full Stack Developer untuk membangun aplikasi web modern. Anda akan terlibat dalam seluruh proses pengembangan, dari konsep hingga implementasi.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- Pengalaman dengan JavaScript (Node.js) di backend\n- Menguasai React atau Angular untuk frontend\n- Pengalaman dengan database SQL dan NoSQL\n- Familiar dengan REST API dan GraphQL\n- Pengalaman dengan CI/CD dan deployment\n- Minimal 2 tahun pengalaman dalam pengembangan web', 'Remote', 1, 'Jobstreet', '2025-04-09', '2025-04-17 12:50:10'),
(5, 'Database Administrator', 'Data Insight Corp', 'Data Insight Corp mencari Database Administrator untuk mengelola dan mengoptimalkan database perusahaan. Anda akan bertanggung jawab untuk maintenance, backup, dan keamanan data.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- Minimal 3 tahun pengalaman sebagai DBA\n- Penguasaan MySQL dan PostgreSQL\n- Pengalaman dengan optimasi performa database\n- Kemampuan menulis dan mengoptimasi query SQL\n- Pengalaman dalam backup dan recovery\n- Pemahaman tentang keamanan database', 'Surabaya', 1, 'LinkedIn', '2025-04-08', '2025-04-17 12:50:10'),
(6, 'UI/UX Designer', 'Creative Design Studio', 'Creative Design Studio mencari UI/UX Designer berbakat untuk membuat antarmuka pengguna yang menarik dan fungsional untuk aplikasi web dan mobile.\n\nKata kunci: desain, kreatif, multimedia', '- Keahlian dalam Adobe XD, Figma, atau Sketch\n- Portfolio yang menunjukkan proyek UI/UX sebelumnya\n- Pemahaman tentang prinsip desain dan pengalaman pengguna\n- Kemampuan untuk membuat wireframes dan prototypes\n- Kemampuan komunikasi yang baik untuk berkolaborasi dengan developer\n- Minimal 2 tahun pengalaman di bidang UI/UX', 'Jakarta', 3, 'Jobstreet', '2025-04-13', '2025-04-17 12:50:10'),
(7, 'Marketing Specialist', 'Brand Success Agency', 'Brand Success Agency mencari Marketing Specialist untuk membantu klien kami dalam merancang dan mengimplementasikan strategi pemasaran digital.\n\nKata kunci: pemasaran, promosi, strategi pemasaran', '- Pengalaman dalam digital marketing\n- Pemahaman tentang SEO, SEM, dan social media marketing\n- Kemampuan analitis untuk mengukur dan melaporkan performa kampanye\n- Kreativitas dalam membuat konten pemasaran\n- Kemampuan komunikasi dan presentasi yang baik\n- Minimal S1 Marketing atau bidang terkait', 'Yogyakarta', 2, 'LinkedIn', '2025-04-11', '2025-04-17 12:50:10'),
(8, 'Content Writer', 'Digital Content Solutions', 'Digital Content Solutions mencari Content Writer untuk membuat berbagai jenis konten untuk platform digital klien kami, termasuk blog, artikel, dan konten media sosial.\n\nKata kunci: pemasaran, promosi, strategi pemasaran', '- Kemampuan menulis yang sangat baik dalam Bahasa Indonesia dan Inggris\n- Pengalaman dalam content writing untuk platform digital\n- Pemahaman tentang SEO content writing\n- Kemampuan untuk menulis dalam berbagai gaya sesuai kebutuhan klien\n- Kreativitas dan kemampuan research yang baik\n- Minimal S1 Komunikasi, Sastra, atau bidang terkait', 'Remote', 2, 'Jobstreet', '2025-04-12', '2025-04-17 12:50:10'),
(9, 'Financial Analyst', 'Prosperity Finance Group', 'Prosperity Finance Group mencari Financial Analyst untuk menganalisis data keuangan dan membantu dalam perencanaan keuangan perusahaan.\n\nKata kunci: keuangan, akuntansi, pajak, audit', '- Minimal S1 Akuntansi, Keuangan, atau bidang terkait\n- Pemahaman tentang laporan keuangan dan analisis\n- Kemampuan menggunakan Excel dan software keuangan\n- Kemampuan analitis dan pemecahan masalah yang baik\n- Minimal 2 tahun pengalaman di bidang keuangan\n- Sertifikasi CFA/CPA menjadi nilai tambah', 'Jakarta', 6, 'LinkedIn', '2025-04-10', '2025-04-17 12:50:10'),
(10, 'HR Recruitment Specialist', 'Talent Source Indonesia', 'Talent Source Indonesia mencari HR Recruitment Specialist untuk membantu proses rekrutmen dan seleksi kandidat untuk berbagai posisi.\n\nKata kunci: sumber daya manusia, rekrutmen, pelatihan', '- Minimal S1 Psikologi, Manajemen SDM, atau bidang terkait\n- Pengalaman dalam rekrutmen dan seleksi kandidat\n- Pemahaman tentang teknik interviewing dan assessment\n- Kemampuan komunikasi dan interpersonal yang baik\n- Pengetahuan tentang hukum ketenagakerjaan di Indonesia\n- Minimal 2 tahun pengalaman di bidang HR', 'Bandung', 7, 'Jobstreet', '2025-04-09', '2025-04-17 12:50:10'),
(11, 'Midwife', 'RS Sehat Sentosa', 'RS Sehat Sentosa mencari Midwife yang berpengalaman untuk memberikan perawatan prenatal, persalinan, dan postnatal kepada pasien. Kandidat harus memiliki sertifikasi bidan dan pengalaman minimal 3 tahun.\n\nKata kunci: kesehatan, medis, perawatan, dokter', '- Sertifikasi bidan yang valid\r\n- Pengalaman minimal 3 tahun sebagai midwife\r\n- Pengetahuan tentang perawatan prenatal dan postnatal\r\n- Kemampuan komunikasi yang baik dengan pasien\r\n- Mampu bekerja dalam tim medis', 'Jakarta', 11, 'Jobstreet', '2025-06-23', '2025-06-23 08:06:01'),
(12, 'Nurse', 'Klinik Medika', 'Klinik Medika membutuhkan Nurse yang berdedikasi untuk memberikan perawatan medis dan dukungan kepada pasien. Kandidat harus memiliki lisensi keperawatan dan pengalaman di bidang kesehatan.\n\nKata kunci: kesehatan, medis, perawatan, dokter', '- Lisensi keperawatan yang valid\r\n- Pengalaman minimal 2 tahun di bidang keperawatan\r\n- Kemampuan melakukan tindakan medis dasar\r\n- Komunikatif dan empati terhadap pasien\r\n- Mampu bekerja shift', 'Bandung', 11, 'LinkedIn', '2025-06-23', '2025-06-23 08:06:01'),
(13, 'Legal Counsel', 'PT Hukum Sejahtera', 'Mencari Legal Counsel untuk menangani urusan hukum perusahaan dan memberikan konsultasi legal.\n\nKata kunci: hukum, pengacara, konsultan hukum, litigasi', '- Sarjana Hukum\n- Pengalaman minimal 3 tahun di bidang corporate legal\n- Memahami hukum bisnis dan ketenagakerjaan\n- Kemampuan analisis yang baik', 'Jakarta', 12, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(14, 'Paralegal Staff', 'LBH Nusantara', 'Membantu pengacara dalam penelitian hukum dan persiapan dokumen legal.\n\nKata kunci: hukum, pengacara, konsultan hukum, litigasi', '- D3/S1 Hukum\n- Pengalaman 1 tahun sebagai paralegal\n- Kemampuan riset yang baik\n- Teliti dan terorganisir', 'Surabaya', 12, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(15, 'Supply Chain Manager', 'PT Logistik Nusantara', 'Bertanggung jawab atas keseluruhan operasi rantai pasok dan logistik perusahaan.\n\nKata kunci: logistik, rantai pasok, pergudangan, distribusi', '- Minimal S1 di bidang Supply Chain Management atau setara\n- Pengalaman 5 tahun di bidang logistik\n- Kemampuan analisis dan problem solving yang baik\n- Mampu memimpin tim', 'Surabaya', 13, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(16, 'Warehouse Supervisor', 'PT Global Logistics', 'Mengawasi operasional gudang dan manajemen inventory.\n\nKata kunci: logistik, rantai pasok, pergudangan, distribusi', '- Minimal D3/S1 jurusan relevan\n- Pengalaman 3 tahun di pergudangan\n- Mampu mengoperasikan sistem WMS\n- Jiwa kepemimpinan', 'Bekasi', 13, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(17, 'Hotel General Manager', 'Grand Hotel Indonesia', 'Memimpin operasional hotel dan memastikan kepuasan pelanggan.\n\nKata kunci: perhotelan, pariwisata, restoran, pelayanan tamu', '- Minimal S1 Perhotelan atau setara\n- Pengalaman 7 tahun di industri perhotelan\n- Kemampuan leadership yang kuat\n- Fasih berbahasa Inggris', 'Bali', 14, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(18, 'Restaurant Manager', 'Fine Dining Group', 'Mengelola operasional restoran dan tim.\n\nKata kunci: perhotelan, pariwisata, restoran, pelayanan tamu', '- Minimal D3/S1 Perhotelan/Kuliner\n- Pengalaman 3 tahun di industri F&B\n- Kemampuan manajemen yang baik\n- Service oriented', 'Jakarta', 14, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(19, 'Project Manager Konstruksi', 'PT Pembangunan Jaya', 'Memimpin dan mengawasi proyek konstruksi dari awal hingga selesai.\n\nKata kunci: konstruksi, bangunan, proyek, properti', '- S1 Teknik Sipil\n- Pengalaman 5 tahun dalam project management konstruksi\n- Sertifikasi PMP lebih disukai\n- Kemampuan kepemimpinan yang baik', 'Jakarta', 15, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(20, 'Site Engineer', 'PT Konstruksi Maju', 'Mengawasi dan mengelola proyek konstruksi di lapangan.\n\nKata kunci: konstruksi, bangunan, proyek, properti', '- S1 Teknik Sipil\n- Pengalaman 3 tahun di proyek konstruksi\n- Mampu membaca gambar teknik\n- Kemampuan koordinasi yang baik', 'Bandung', 15, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(21, 'E-commerce Manager', 'PT Digital Retail Indonesia', 'Mengelola dan mengembangkan platform e-commerce perusahaan.\n\nKata kunci: ritel, toko online, penjualan online, merchandising', '- S1 di bidang relevan\n- Pengalaman 3 tahun di e-commerce\n- Pemahaman yang baik tentang digital marketing\n- Analytical thinking', 'Jakarta', 16, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(22, 'Retail Store Manager', 'Fashion Retail Group', 'Mengelola operasional toko dan tim sales.\n\nKata kunci: ritel, toko online, penjualan online, merchandising', '- Minimal D3/S1\n- Pengalaman 3 tahun di retail fashion\n- Target oriented\n- Kemampuan leadership', 'Surabaya', 16, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(23, 'Production Supervisor', 'PT Manufaktur Presisi', 'Mengawasi proses produksi dan memastikan kualitas produk.\n\nKata kunci: manufaktur, produksi, pabrik, quality control', '- S1 Teknik Industri\n- Pengalaman 3 tahun di manufaktur\n- Kemampuan leadership\n- Paham ISO 9001', 'Bekasi', 17, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(24, 'Quality Control Manager', 'PT Industri Jaya', 'Memastikan kualitas produk sesuai standar.\n\nKata kunci: manufaktur, produksi, pabrik, quality control', '- S1 Teknik Industri/Kimia\n- Pengalaman 5 tahun di QC\n- Familiar dengan standar ISO\n- Teliti dan analitis', 'Tangerang', 17, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(25, 'Research Scientist', 'PT Inovasi Indonesia', 'Melakukan penelitian dan pengembangan produk baru.\n\nKata kunci: penelitian, pengembangan, riset, inovasi', '- S2/S3 di bidang terkait\n- Pengalaman penelitian minimal 3 tahun\n- Publikasi ilmiah menjadi nilai plus\n- Kemampuan analitis yang kuat', 'Bandung', 18, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(26, 'R&D Engineer', 'Tech Innovation Lab', 'Mengembangkan dan menguji produk teknologi baru.\n\nKata kunci: penelitian, pengembangan, riset, inovasi', '- S1/S2 Teknik\n- Pengalaman 2 tahun di R&D\n- Kreatif dan inovatif\n- Kemampuan problem solving', 'Jakarta', 18, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(27, 'Content Manager', 'PT Media Digital', 'Mengelola strategi konten dan tim kreatif.\n\nKata kunci: media, komunikasi, jurnalistik, konten', '- S1 Komunikasi atau bidang terkait\n- Pengalaman 3 tahun di content management\n- Portfolio yang kuat\n- Kreativitas tinggi', 'Jakarta', 19, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(28, 'Public Relations Manager', 'Corporate Communications Group', 'Mengelola komunikasi eksternal dan internal perusahaan.\n\nKata kunci: media, komunikasi, jurnalistik, konten', '- S1 Public Relations/Komunikasi\n- Pengalaman 5 tahun di PR\n- Network yang luas\n- Kemampuan komunikasi yang excellent', 'Jakarta', 19, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(29, 'Farm Manager', 'PT Agro Nusantara', 'Mengelola operasional pertanian dan pengembangan hasil panen.\n\nKata kunci: pertanian, perkebunan, budidaya, agribisnis', '- S1 Pertanian atau bidang terkait\n- Pengalaman 5 tahun di pertanian\n- Pemahaman tentang teknologi pertanian modern\n- Kemampuan manajemen yang baik', 'Yogyakarta', 20, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(30, 'Agricultural Engineer', 'Modern Farming Group', 'Mengembangkan dan menerapkan teknologi pertanian modern.\n\nKata kunci: pertanian, perkebunan, budidaya, agribisnis', '- S1 Teknik Pertanian\n- Pengalaman 3 tahun di agricultural engineering\n- Familiar dengan smart farming\n- Inovatif dan adaptif', 'Malang', 20, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(31, 'Senior Backend Developer', 'Tech Solutions ID', 'Mengembangkan dan memelihara sistem backend untuk aplikasi enterprise.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- S1 Informatika/Teknik Komputer\n- Minimal 5 tahun pengalaman\n- Ahli dalam Node.js dan Python\n- Familiar dengan microservices', 'Jakarta', 1, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(32, 'DevOps Engineer', 'Cloud Tech Indonesia', 'Mengelola infrastruktur cloud dan pipeline CI/CD.\n\nKata kunci: pengembang, pemrograman, perangkat lunak, aplikasi', '- S1 bidang IT\n- Pengalaman 3 tahun sebagai DevOps\n- Ahli AWS/GCP\n- Familiar dengan Docker dan Kubernetes', 'Bandung', 1, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(33, 'Digital Marketing Manager', 'Brand Solutions', 'Memimpin strategi pemasaran digital dan tim marketing.\n\nKata kunci: pemasaran, promosi, strategi pemasaran', '- S1 Marketing/Komunikasi\n- Pengalaman 5 tahun di digital marketing\n- Ahli Google Analytics dan SEO\n- Data-driven decision maker', 'Jakarta', 2, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(34, 'Brand Manager', 'Consumer Goods Indonesia', 'Mengelola dan mengembangkan brand perusahaan.\n\nKata kunci: pemasaran, promosi, strategi pemasaran', '- S1 Marketing/Business\n- Pengalaman 4 tahun di brand management\n- Portfolio yang kuat\n- Strategic thinking', 'Surabaya', 2, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(35, 'Senior UI/UX Designer', 'Digital Creative Studio', 'Merancang user interface dan experience untuk produk digital.\n\nKata kunci: desain, kreatif, multimedia', '- S1 Desain atau setara\n- Pengalaman 5 tahun di UI/UX\n- Ahli Figma dan Adobe Suite\n- Portfolio yang mengesankan', 'Jakarta', 3, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(36, 'Graphic Designer', 'Creative Agency ID', 'Membuat desain visual untuk berbagai kebutuhan marketing.\n\nKata kunci: desain, kreatif, multimedia', '- S1 Desain Komunikasi Visual\n- Pengalaman 3 tahun\n- Ahli Adobe Creative Suite\n- Eye for detail', 'Bandung', 3, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(37, 'Business Development Manager', 'PT Solusi Bisnis', 'Mengembangkan bisnis dan menjalin kerjasama strategis.\n\nKata kunci: penjualan, pemasaran, target, negosiasi', '- S1 Business/Marketing\n- Pengalaman 5 tahun di business development\n- Network yang luas\n- Negotiation skills', 'Jakarta', 4, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(38, 'Sales Manager', 'Industrial Solutions', 'Memimpin tim sales dan mencapai target penjualan.\n\nKata kunci: penjualan, pemasaran, target, negosiasi', '- S1 dari jurusan apapun\n- Pengalaman 4 tahun di sales\n- Track record yang baik\n- Leadership skills', 'Surabaya', 4, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(39, 'Customer Experience Manager', 'E-commerce Platform', 'Memimpin tim customer service dan meningkatkan kepuasan pelanggan.\n\nKata kunci: layanan pelanggan, komunikasi, pelayanan', '- S1 dari jurusan apapun\n- Pengalaman 4 tahun di customer service\n- Problem solving skills\n- Excellent communication', 'Jakarta', 5, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(40, 'Technical Support Specialist', 'Software Solutions ID', 'Memberikan dukungan teknis kepada pelanggan.\n\nKata kunci: layanan pelanggan, komunikasi, pelayanan', '- S1 IT atau setara\n- Pengalaman 2 tahun di technical support\n- Kemampuan troubleshooting\n- Patient dan komunikatif', 'Bandung', 5, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(41, 'Finance Manager', 'PT Finansial Sukses', 'Mengelola keuangan dan pelaporan finansial perusahaan.\n\nKata kunci: keuangan, akuntansi, pajak, audit', '- S1 Akuntansi/Keuangan\n- Pengalaman 5 tahun di finance\n- Certified Public Accountant\n- Analytical thinking', 'Jakarta', 6, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(42, 'Tax Specialist', 'Konsultan Pajak Indonesia', 'Menangani perpajakan perusahaan dan konsultasi pajak.\n\nKata kunci: keuangan, akuntansi, pajak, audit', '- S1 Akuntansi/Perpajakan\n- Pengalaman 3 tahun di perpajakan\n- Bersertifikat Konsultan Pajak\n- Up to date dengan regulasi', 'Surabaya', 6, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(43, 'HR Manager', 'PT SDM Unggul', 'Mengelola seluruh aspek sumber daya manusia perusahaan.\n\nKata kunci: sumber daya manusia, rekrutmen, pelatihan', '- S1 Psikologi/HRD\n- Pengalaman 5 tahun di HR\n- Kemampuan leadership\n- People management skills', 'Jakarta', 7, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(44, 'Recruitment Specialist', 'Talent Search Indonesia', 'Menangani proses rekrutmen dan seleksi kandidat.\n\nKata kunci: sumber daya manusia, rekrutmen, pelatihan', '- S1 Psikologi/HRD\n- Pengalaman 3 tahun di recruitment\n- Networking skills\n- Assessment skills', 'Bandung', 7, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(45, 'Mechanical Engineer', 'PT Engineering Solutions', 'Merancang dan mengembangkan sistem mekanik.\n\nKata kunci: teknik, mesin, listrik, industri', '- S1 Teknik Mesin\n- Pengalaman 4 tahun\n- Ahli CAD/CAM\n- Problem solving skills', 'Surabaya', 8, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(46, 'Electrical Engineer', 'Power Solutions ID', 'Menangani sistem kelistrikan dan maintenance.\n\nKata kunci: teknik, mesin, listrik, industri', '- S1 Teknik Elektro\n- Pengalaman 3 tahun\n- Paham standar kelistrikan\n- Safety oriented', 'Jakarta', 8, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(47, 'Office Manager', 'Corporate Services ID', 'Mengelola operasional kantor dan administrasi.\n\nKata kunci: administrasi, sekretaris, manajemen kantor', '- S1 dari jurusan apapun\n- Pengalaman 4 tahun di office management\n- Organizational skills\n- Multitasking ability', 'Jakarta', 9, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(48, 'Executive Assistant', 'Multinational Corp', 'Mendukung eksekutif dalam tugas administratif.\n\nKata kunci: administrasi, sekretaris, manajemen kantor', '- S1 dari jurusan apapun\n- Pengalaman 3 tahun sebagai EA\n- Excellent English\n- Time management skills', 'Surabaya', 9, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(49, 'Training Manager', 'Learning Development Center', 'Mengembangkan dan mengelola program pelatihan.\n\nKata kunci: pendidikan, pengajar, pelatih, pembelajaran', '- S1 Pendidikan/Psikologi\n- Pengalaman 5 tahun di training\n- Instructional design skills\n- Public speaking', 'Jakarta', 10, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(50, 'Corporate Trainer', 'Professional Training Institute', 'Memberikan pelatihan soft skill dan technical skill.\n\nKata kunci: pendidikan, pengajar, pelatih, pembelajaran', '- S1 dari jurusan relevan\n- Pengalaman 3 tahun sebagai trainer\n- Sertifikasi trainer\n- Komunikasi yang baik', 'Bandung', 10, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(51, 'Medical Director', 'RS Internasional', 'Memimpin dan mengawasi pelayanan medis rumah sakit.\n\nKata kunci: kesehatan, medis, perawatan, dokter', '- S2 Kedokteran\n- Pengalaman 10 tahun\n- Lisensi dokter aktif\n- Leadership skills', 'Jakarta', 11, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(52, 'Head Nurse', 'Klinik Spesialis', 'Memimpin tim perawat dan mengawasi perawatan pasien.\n\nKata kunci: kesehatan, medis, perawatan, dokter', '- S1 Keperawatan\n- Pengalaman 5 tahun\n- Sertifikasi keperawatan\n- Manajemen tim', 'Surabaya', 11, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(53, 'Corporate Lawyer', 'Firma Hukum Global', 'Menangani urusan hukum korporasi dan kontrak.\n\nKata kunci: hukum, pengacara, konsultan hukum, litigasi', '- S1 Hukum\n- Pengalaman 5 tahun\n- Lisensi advokat\n- Bahasa Inggris fasih', 'Jakarta', 12, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(54, 'Legal Manager', 'Multinational Company', 'Mengelola aspek legal perusahaan multinasional.\n\nKata kunci: hukum, pengacara, konsultan hukum, litigasi', '- S1 Hukum\n- Pengalaman 7 tahun\n- Pemahaman hukum internasional\n- Leadership skills', 'Surabaya', 12, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(55, 'Logistics Coordinator', 'Global Shipping Corp', 'Mengkoordinasikan pengiriman dan distribusi barang.\n\nKata kunci: logistik, rantai pasok, pergudangan, distribusi', '- S1 Manajemen/Logistik\n- Pengalaman 3 tahun\n- Familiar dengan software logistik\n- Koordinasi yang baik', 'Jakarta', 13, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(56, 'Supply Chain Analyst', 'Manufacturing Industry', 'Menganalisis dan optimasi rantai pasok.\n\nKata kunci: logistik, rantai pasok, pergudangan, distribusi', '- S1 Teknik Industri/Supply Chain\n- Pengalaman 2 tahun\n- Analytical skills\n- MS Excel advanced', 'Bandung', 13, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(57, 'Food & Beverage Manager', 'Luxury Hotel Chain', 'Mengelola operasional F&B di hotel berbintang.\n\nKata kunci: perhotelan, pariwisata, restoran, pelayanan tamu', '- S1 Perhotelan/Kuliner\n- Pengalaman 5 tahun di F&B\n- Servsafe certified\n- Customer service oriented', 'Bali', 14, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(58, 'Front Office Manager', 'Resort & Spa', 'Memimpin tim front office dan guest service.\n\nKata kunci: perhotelan, pariwisata, restoran, pelayanan tamu', '- S1 Perhotelan\n- Pengalaman 4 tahun\n- Bahasa Inggris fasih\n- Leadership skills', 'Lombok', 14, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(59, 'Quantity Surveyor', 'Construction Company', 'Menghitung dan mengestimasi biaya proyek konstruksi.\n\nKata kunci: konstruksi, bangunan, proyek, properti', '- S1 Teknik Sipil\n- Pengalaman 3 tahun\n- Familiar dengan software QS\n- Teliti dan akurat', 'Jakarta', 15, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(60, 'Property Manager', 'Real Estate Developer', 'Mengelola properti komersial dan residensial.\n\nKata kunci: konstruksi, bangunan, proyek, properti', '- S1 Properti/Manajemen\n- Pengalaman 4 tahun\n- Building management skills\n- Customer oriented', 'Surabaya', 15, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(61, 'Category Manager', 'Online Marketplace', 'Mengelola kategori produk dan strategi penjualan.\n\nKata kunci: ritel, toko online, penjualan online, merchandising', '- S1 Business/Marketing\n- Pengalaman 4 tahun\n- Category management skills\n- Analytical thinking', 'Jakarta', 16, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(62, 'Digital Marketing Specialist', 'Fashion E-commerce', 'Mengelola kampanye marketing digital.\n\nKata kunci: ritel, toko online, penjualan online, merchandising', '- S1 Marketing/Komunikasi\n- Pengalaman 2 tahun\n- Digital marketing skills\n- Creative thinking', 'Bandung', 16, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(63, 'Plant Manager', 'Manufacturing Plant', 'Memimpin operasional pabrik secara keseluruhan.\n\nKata kunci: manufaktur, produksi, pabrik, quality control', '- S1 Teknik Industri\n- Pengalaman 7 tahun\n- Production management\n- Leadership skills', 'Karawang', 17, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(64, 'Process Engineer', 'Chemical Industry', 'Mengoptimalkan proses produksi dan efisiensi.\n\nKata kunci: manufaktur, produksi, pabrik, quality control', '- S1 Teknik Kimia\n- Pengalaman 3 tahun\n- Process improvement\n- Analytical skills', 'Cikarang', 17, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(65, 'Senior Researcher', 'Research Institute', 'Memimpin proyek penelitian dan pengembangan.\n\nKata kunci: penelitian, pengembangan, riset, inovasi', '- S3 di bidang terkait\n- Pengalaman 5 tahun\n- Research methodology\n- Publication record', 'Bandung', 18, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(66, 'Product Development Manager', 'Consumer Goods', 'Mengelola pengembangan produk baru.\n\nKata kunci: penelitian, pengembangan, riset, inovasi', '- S1 Teknik/Sciences\n- Pengalaman 5 tahun\n- Product development\n- Project management', 'Jakarta', 18, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(67, 'News Editor', 'Media Group', 'Mengelola konten berita dan tim editorial.\n\nKata kunci: media, komunikasi, jurnalistik, konten', '- S1 Jurnalistik/Komunikasi\n- Pengalaman 5 tahun\n- Editorial skills\n- News judgment', 'Jakarta', 19, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(68, 'Social Media Manager', 'Digital Agency', 'Mengelola strategi dan konten media sosial.\n\nKata kunci: media, komunikasi, jurnalistik, konten', '- S1 Komunikasi/Marketing\n- Pengalaman 3 tahun\n- Social media marketing\n- Content creation', 'Surabaya', 19, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36'),
(69, 'Plantation Manager', 'Agribusiness Corp', 'Mengelola perkebunan dan produksi hasil pertanian.\n\nKata kunci: pertanian, perkebunan, budidaya, agribisnis', '- S1 Pertanian/Agribisnis\n- Pengalaman 5 tahun\n- Plantation management\n- Leadership skills', 'Medan', 20, 'LinkedIn', '2025-06-24', '2025-06-24 08:48:36'),
(70, 'Agriculture Consultant', 'Farming Solutions', 'Memberikan konsultasi untuk pengembangan pertanian.\n\nKata kunci: pertanian, perkebunan, budidaya, agribisnis', '- S1 Pertanian\n- Pengalaman 4 tahun\n- Agricultural knowledge\n- Communication skills', 'Palembang', 20, 'JobStreet', '2025-06-24', '2025-06-24 08:48:36');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `keywords`
--
ALTER TABLE `keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

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
