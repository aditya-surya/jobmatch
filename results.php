<?php
session_start();

// Check if we have matching jobs in session
if (!isset($_SESSION['matching_jobs']) || empty($_SESSION['matching_jobs'])) {
    header('Location: upload.php');
    exit();
}

$matching_jobs = $_SESSION['matching_jobs'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Analisis CV - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/upload.css">
    <style>
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                            url('https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500');
            background-size: cover;
            background-position: center;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }
        .job-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .match-score {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
        }
        .category-badge {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .job-link {
            text-decoration: none;
            color: inherit;
        }
        .job-link:hover {
            color: inherit;
        }
        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .btn-custom {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 2rem;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
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
