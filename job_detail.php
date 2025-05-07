<?php
session_start();
require_once 'config/database.php';

// Cek apakah id diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: results.php");
    exit();
}

$job_id = intval($_GET['id']);

// Ambil data lowongan dari database
$query = "SELECT lowongan.*, kategori.nama_kategori as kategori 
          FROM lowongan 
          JOIN kategori ON lowongan.kategori_id = kategori.id 
          WHERE lowongan.id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Lowongan tidak ditemukan
    header("Location: results.php");
    exit();
}

$job = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['judul']); ?> - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="job-detail-container my-5">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="results.php">Hasil Pencarian</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Lowongan</li>
                </ol>
            </nav>
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($job['judul']); ?></h1>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h2 class="h4 company-name"><?php echo htmlspecialchars($job['perusahaan']); ?></h2>
                            <p class="mb-2"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($job['lokasi']); ?></p>
                            <p class="mb-0">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($job['kategori']); ?></span>
                                <small class="ms-2 text-muted">Sumber: <?php echo htmlspecialchars($job['sumber']); ?></small>
                                <small class="ms-2 text-muted">Diposting: <?php echo date('d F Y', strtotime($job['tanggal_posting'])); ?></small>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php if (isset($_SESSION["matching_jobs"])): ?>
                                <?php 
                                // Cari skor kecocokan dari session jika ada
                                $match_score = 0;
                                foreach ($_SESSION["matching_jobs"] as $match_job) {
                                    if ($match_job['id'] == $job_id) {
                                        $match_score = $match_job['match_score'];
                                        break;
                                    }
                                }
                                $match_percent = round($match_score * 100);
                                ?>
                                <div class="match-score-big">
                                    <p class="mb-1">Tingkat Kecocokan</p>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $match_percent; ?>%;" aria-valuenow="<?php echo $match_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <strong><?php echo $match_percent; ?>%</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="job-description mb-4">
                        <h3 class="h5 mb-3">Deskripsi Pekerjaan</h3>
                        <div class="job-description-text">
                            <?php echo nl2br(htmlspecialchars($job['deskripsi'])); ?>
                        </div>
                    </div>
                    
                    <div class="job-requirements mb-4">
                        <h3 class="h5 mb-3">Persyaratan</h3>
                        <div class="job-requirements-text">
                            <?php echo nl2br(htmlspecialchars($job['persyaratan'])); ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="results.php" class="btn btn-outline-primary">Kembali ke Hasil Pencarian</a>
                        <button type="button" class="btn btn-success" onclick="alert('Fitur aplikasi belum tersedia dalam demo ini.')">Lamar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
