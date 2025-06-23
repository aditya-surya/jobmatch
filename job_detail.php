<?php
require_once 'config/database.php';

// Get job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get job details
    $stmt = $conn->prepare("
        SELECT l.*, k.nama_kategori 
        FROM lowongan l 
        JOIN kategori k ON l.kategori_id = k.id 
        WHERE l.id = ?
    ");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        header('Location: results.php');
        exit();
    }

} catch (Exception $e) {
    error_log("Error in job_detail: " . $e->getMessage());
    header('Location: results.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['judul']); ?> - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/upload.css">
    <style>
        .job-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .company-info {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .job-content {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1.5rem;
        }
        .match-score {
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .category-badge {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="job-header">
        <div class="container">
            <h1 class="mb-3"><?php echo htmlspecialchars($job['judul']); ?></h1>
            <div class="d-flex align-items-center flex-wrap gap-3">
                <span class="match-score">
                    <i class="fas fa-chart-line me-2"></i>
                    <?php echo isset($_SESSION['matching_jobs']) ? round($job['match_score'] * 100) : ''; ?>% Match
                </span>
                <span class="category-badge">
                    <?php echo htmlspecialchars($job['nama_kategori']); ?>
                </span>
                <span class="category-badge">
                    <?php echo htmlspecialchars($job['sumber']); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-md-8">
                <div class="job-content mb-4">
                    <h4 class="mb-4">Deskripsi Pekerjaan</h4>
                    <div class="job-description mb-4">
                        <?php echo nl2br(htmlspecialchars($job['deskripsi'])); ?>
                    </div>

                    <h4 class="mb-4">Persyaratan</h4>
                    <div class="job-requirements">
                        <?php echo nl2br(htmlspecialchars($job['persyaratan'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="company-info sticky-top" style="top: 2rem;">
                    <h5 class="mb-4">Informasi Perusahaan</h5>
                    
                    <div class="mb-3">
                        <i class="fas fa-building me-2"></i>
                        <strong>Perusahaan:</strong><br>
                        <?php echo htmlspecialchars($job['perusahaan']); ?>
                    </div>
                    
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <strong>Lokasi:</strong><br>
                        <?php echo htmlspecialchars($job['lokasi']); ?>
                    </div>
                    
                    <div class="mb-4">
                        <i class="fas fa-calendar me-2"></i>
                        <strong>Tanggal Posting:</strong><br>
                        <?php echo date('d M Y', strtotime($job['tanggal_posting'])); ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="results.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Hasil
                        </a>
                        <a href="index.html" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
