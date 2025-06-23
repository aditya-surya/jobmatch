<?php
require_once 'config/database.php';

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.html');
    exit();
}

$job_id = (int)$_GET['id'];

try {
    // Menggunakan prepared statement dengan MySQLi
    $query = "SELECT l.*, k.nama_kategori 
              FROM lowongan l 
              JOIN kategori k ON l.kategori_id = k.id 
              WHERE l.id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
    
    if (!$job) {
        throw new Exception("Lowongan tidak ditemukan");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in job_detail.php: " . $e->getMessage());
    die("Maaf, terjadi kesalahan saat mengambil detail lowongan.");
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
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
        }
        
        body {
            background-color: #f3f4f6;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500') center/cover;
            opacity: 0.1;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .job-detail-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -4rem;
            position: relative;
            z-index: 2;
            background: white;
        }
        
        .category-badge {
            background: #e0e7ff;
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            display: inline-block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .category-badge:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .detail-section {
            margin-bottom: 2rem;
            padding: 2rem;
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .detail-section:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .detail-section h3 {
            color: #1f2937;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .btn-custom {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 2rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }
        
        .btn-custom.btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-custom.btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        
        .btn-custom.btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-custom.btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }
        
        .info-item i {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-right: 1rem;
            width: 24px;
            text-align: center;
        }
        
        .job-content {
            line-height: 1.8;
            color: #4b5563;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        /* Animasi untuk detail sections */
        .detail-section {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Delay animasi untuk setiap section */
        .detail-section:nth-child(1) { animation-delay: 0.2s; }
        .detail-section:nth-child(2) { animation-delay: 0.4s; }
        .detail-section:nth-child(3) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <!-- Toast Container for Notifications -->
    <div class="toast-container"></div>

    <!-- Hero Section -->
    <header class="hero-section text-white">
        <div class="container hero-content text-center">
            <div class="company-logo">
                <i class="fas fa-building fa-2x" style="color: var(--primary-color);"></i>
            </div>
            <h1 class="display-5 mb-3 fw-bold"><?php echo htmlspecialchars($job['judul']); ?></h1>
            <p class="lead mb-0 fw-normal"><?php echo htmlspecialchars($job['perusahaan']); ?></p>
        </div>
    </header>

    <div class="container mb-5">
        <div class="card job-detail-card">
            <div class="card-body p-4">
                <!-- Quick Info -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-item">
                            <i class="fas fa-building"></i>
                            <div>
                                <small class="text-muted d-block">Perusahaan</small>
                                <strong><?php echo htmlspecialchars($job['perusahaan']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <small class="text-muted d-block">Lokasi</small>
                                <strong><?php echo htmlspecialchars($job['lokasi']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <small class="text-muted d-block">Tanggal Posting</small>
                                <strong><?php echo date('d M Y', strtotime($job['tanggal_posting'])); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="mb-4 text-center">
                    <span class="category-badge">
                        <i class="fas fa-tag me-1"></i>
                        <?php echo htmlspecialchars($job['nama_kategori']); ?>
                    </span>
                    <span class="category-badge">
                        <i class="fas fa-globe me-1"></i>
                        <?php echo htmlspecialchars($job['sumber']); ?>
                    </span>
                </div>

                <!-- Job Description -->
                <div class="detail-section">
                    <h3>
                        <i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>
                        Deskripsi Pekerjaan
                    </h3>
                    <div class="job-content">
                        <?php echo nl2br(htmlspecialchars($job['deskripsi'])); ?>
                    </div>
                </div>

                <!-- Job Requirements -->
                <div class="detail-section">
                    <h3>
                        <i class="fas fa-list-check me-2" style="color: var(--primary-color);"></i>
                        Persyaratan
                    </h3>
                    <div class="job-content">
                        <?php echo nl2br(htmlspecialchars($job['persyaratan'])); ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary btn-custom" onclick="showNotification()">
                        <i class="fas fa-external-link-alt me-2"></i>
                        Lihat di Web
                    </button>
                    <a href="results.php" class="btn btn-outline-primary btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali ke Hasil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    function showNotification() {
        const toastContainer = document.querySelector('.toast-container');
        
        const toastHTML = `
            <div class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-info-circle me-2"></i>
                        Maaf, fitur ini belum tersedia dalam versi prototype.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.innerHTML = toastHTML;
        
        const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'), {
            animation: true,
            autohide: true,
            delay: 3000
        });
        
        toast.show();
    }
    </script>
</body>
</html>
