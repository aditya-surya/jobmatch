<?php
session_start();

$matching_jobs = isset($_SESSION['matching_jobs']) ? $_SESSION['matching_jobs'] : [];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Analisis CV - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/results.css">
</head>
<body class="bg-light">
    <!-- Hero Section -->
    <header class="hero-section text-white text-center">
        <div class="container">
            <h1 class="display-4 mb-3">Hasil Analisis CV</h1>
            <p class="lead mb-0">Kami telah menemukan lowongan pekerjaan yang paling sesuai dengan keahlian Anda</p>
        </div>
    </header>

    <div class="container">
        <!-- Results Count -->
        <?php if (empty($matching_jobs)): ?>
            <div class="no-matches-container text-center my-5 p-4">
                <div class="alert alert-info mb-4" role="alert">
                    <h4 class="alert-heading mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak Ada Lowongan yang Sesuai
                    </h4>
                    <p class="mb-2">
                        Maaf saat ini lowongan kerja yang sesuai dengan CV anda tidak tersedia, 
                        atau database kami masih belum memuat pekerjaan yang anda miliki.
                    </p>
                    <p class="mb-0">
                        Kami mohon maaf atas ketidaknyamanannya.
                    </p>
                </div>
                
                <button type="button" class="btn btn-outline-danger" onclick="showReportNotification()">
                    <i class="fas fa-flag me-2"></i>
                    Laporkan Masalah
                </button>
            </div>

            <script>
            function showReportNotification() {
                alert('Maaf, fitur ini belum tersedia saat ini.');
            }
            </script>

            <style>
            .no-matches-container {
                max-width: 600px;
                margin: 0 auto;
            }
            .no-matches-container .alert {
                background-color: rgba(255, 255, 255, 0.9);
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .no-matches-container .btn {
                padding: 10px 20px;
                border-radius: 8px;
                transition: all 0.3s ease;
            }
            .no-matches-container .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            </style>
        <?php else: ?>
            <div class="text-center mb-4">
                <h5 class="text-muted">
                    Ditemukan <?php echo count($matching_jobs); ?> lowongan yang sesuai dengan profil Anda
                </h5>
            </div>

            <!-- Job Cards -->
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                <?php foreach ($matching_jobs as $job): ?>
                    <div class="col">
                        <a href="job_detail.php?id=<?php echo $job['id']; ?>" class="job-link">
                            <div class="card h-100 job-card">
                                <div class="card-body position-relative">
                                    <span class="match-score">
                                        <i class="fas fa-chart-line me-2"></i>
                                        <?php echo round($job['match_score'] * 100); ?>% Match
                                    </span>
                                    
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($job['judul']); ?></h5>
                                    
                                    <p class="card-text mb-2">
                                        <i class="fas fa-building me-2"></i>
                                        <?php echo htmlspecialchars($job['perusahaan']); ?>
                                    </p>
                                    
                                    <p class="card-text mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($job['lokasi']); ?>
                                    </p>
                                    
                                    <div class="mb-3">
                                        <span class="category-badge">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($job['nama_kategori']); ?>
                                        </span>
                                        <span class="category-badge">
                                            <i class="fas fa-globe me-1"></i>
                                            <?php echo htmlspecialchars($job['sumber']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="card-text mb-3">
                                        <h6 class="mb-2">Deskripsi:</h6>
                                        <p class="text-muted">
                                            <?php echo nl2br(htmlspecialchars(substr($job['deskripsi'], 0, 150) . '...')); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-2"></i>
                                            Posted: <?php echo date('d M Y', strtotime($job['tanggal_posting'])); ?>
                                        </small>
                                        <span class="text-primary">
                                            Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="text-center mb-5">
            <a href="upload.php" class="btn btn-primary btn-custom me-2">
                <i class="fas fa-upload me-2"></i>
                Upload CV Lain
            </a>
            <a href="index.html" class="btn btn-outline-secondary btn-custom">
                <i class="fas fa-home me-2"></i>
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
