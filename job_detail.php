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
    <link rel="stylesheet" href="css/job_detail.css">
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
                        <?php
                        // Format persyaratan menjadi list bullet yang rapi
                        $req = $job['persyaratan'];
                        $req = str_replace(["\r", "\\n"], "\n", $req); // Ganti literal \n dan carriage return ke newline
                        // Jika hanya satu baris tapi mengandung banyak '- ', pecah berdasarkan '- '
                        if (substr_count($req, '- ') > 1 && substr_count($req, "\n") < 1) {
                            $lines = preg_split('/- /', $req);
                            $list_items = [];
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if ($line === '' || strtolower($line) === 'persyaratan') continue;
                                $list_items[] = htmlspecialchars($line);
                            }
                        } else {
                            $lines = preg_split('/\n|<br\s*\/?>/', $req);
                        $list_items = [];
                        foreach ($lines as $line) {
                            $line = trim($line);
                                if ($line === '' || strtolower($line) === 'persyaratan') continue;
                            if (strpos($line, '- ') === 0) {
                                $line = substr($line, 2);
                            }
                            $list_items[] = htmlspecialchars($line);
                        }
                        }
                        // Tampilkan sebagai list jika ada lebih dari satu item
                        $list_items = array_filter($list_items, function($v) { return $v !== ''; });
                        if (count($list_items) > 0) {
                            echo '<ul class="mb-0">';
                            foreach ($list_items as $item) {
                                echo '<li>' . $item . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo nl2br(htmlspecialchars($req));
                        }
                        ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if (isset($_GET['from']) && $_GET['from'] === 'admin'): ?>
                    <div class="text-center mt-4">
                        <a href="admin.php?action=list" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                <?php elseif (!isset($_GET['from']) || $_GET['from'] !== 'admin'): ?>
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
                <?php endif; ?>
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
