<?php
session_start();
require_once 'config/database.php';

// Cek apakah ada data hasil matching
if (!isset($_SESSION["matching_jobs"]) || empty($_SESSION["matching_jobs"])) {
    // Redirect ke halaman upload jika tidak ada data
    header("Location: upload.php");
    exit();
}

$matching_jobs = $_SESSION["matching_jobs"];

// Ambil data kategori untuk semua lowongan yang cocok
if (count($matching_jobs) > 0) {
    $job_ids = array_column($matching_jobs, 'id');
    $id_placeholders = implode(',', array_fill(0, count($job_ids), '?'));
    
    $query = "SELECT lowongan.id, kategori.nama_kategori 
              FROM lowongan 
              JOIN kategori ON lowongan.kategori_id = kategori.id 
              WHERE lowongan.id IN ($id_placeholders)";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters for all job IDs
    $types = str_repeat('i', count($job_ids));
    $stmt->bind_param($types, ...$job_ids);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['id']] = $row['nama_kategori'];
    }
}

// Define category CSS classes
function getCategoryClass($kategori) {
    $kategori = strtolower($kategori);
    $class_map = [
        'finance' => 'bg-info',
        'development' => 'bg-primary',
        'marketing' => 'bg-success',
        'database' => 'bg-secondary',
        'hr' => 'bg-warning',
        'content' => 'bg-dark',
        'design' => 'bg-primary'
    ];
    
    foreach ($class_map as $key => $class) {
        if (strpos($kategori, $key) !== false) {
            return $class;
        }
    }
    
    return 'bg-primary'; // Default class
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .category-badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-container">
            <h1 class="mt-5 mb-4 text-center">Lowongan Kerja yang Cocok untuk Anda</h1>
            
            <?php if (count($matching_jobs) > 0): ?>
                <div class="row">
                    <?php foreach ($matching_jobs as $job): ?>
                        <?php 
                        // Get category from the database if available
                        $kategori = isset($categories[$job['id']]) ? $categories[$job['id']] : '';
                        
                        // If not available, try to get it from the job data
                        if (empty($kategori) && isset($job['kategori']) && !empty($job['kategori'])) {
                            $kategori = $job['kategori'];
                        }
                        
                        // If still empty, use a fallback based on the job title
                        if (empty($kategori)) {
                            $judul = strtolower($job['judul']);
                            if (strpos($judul, 'designer') !== false || strpos($judul, 'design') !== false) {
                                $kategori = 'Design';
                            } elseif (strpos($judul, 'developer') !== false || strpos($judul, 'software') !== false) {
                                $kategori = 'Development';
                            } elseif (strpos($judul, 'marketing') !== false) {
                                $kategori = 'Marketing';
                            } elseif (strpos($judul, 'finance') !== false || strpos($judul, 'analyst') !== false) {
                                $kategori = 'Finance';
                            } elseif (strpos($judul, 'hr') !== false || strpos($judul, 'recruitment') !== false) {
                                $kategori = 'HR';
                            } elseif (strpos($judul, 'content') !== false || strpos($judul, 'writer') !== false) {
                                $kategori = 'Content';
                            } elseif (strpos($judul, 'database') !== false || strpos($judul, 'data') !== false) {
                                $kategori = 'Database';
                            } else {
                                $kategori = 'General';
                            }
                        }
                        
                        // Get the appropriate Bootstrap class for the category
                        $categoryClass = getCategoryClass($kategori);
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card job-card h-100 shadow-sm">
                                <div class="card-body">
                                    <span class="category-badge <?php echo $categoryClass; ?> mb-2"><?php echo htmlspecialchars($kategori); ?></span>
                                    <h5 class="card-title job-title"><?php echo isset($job['judul']) ? htmlspecialchars($job['judul']) : 'Tidak Ada Judul'; ?></h5>
                                    <h6 class="card-subtitle mb-2 company-name"><?php echo isset($job['perusahaan']) ? htmlspecialchars($job['perusahaan']) : 'Perusahaan Tidak Disebutkan'; ?></h6>
                                    <p class="card-text"><?php echo isset($job['deskripsi']) ? substr(htmlspecialchars($job['deskripsi']), 0, 150) . '...' : 'Tidak ada deskripsi'; ?></p>
                                    <p class="card-text"><small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo isset($job['lokasi']) ? htmlspecialchars($job['lokasi']) : 'Lokasi Tidak Disebutkan'; ?></small></p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="job-source">Sumber: <?php echo isset($job['sumber']) ? htmlspecialchars($job['sumber']) : 'Tidak Diketahui'; ?></small>
                                        <div class="match-score">
                                            <?php 
                                            $match_score = isset($job['match_score']) ? $job['match_score'] : 0;
                                            $match_percent = round($match_score * 100);
                                            ?>
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $match_percent; ?>%;" aria-valuenow="<?php echo $match_percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $match_percent; ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="job_detail.php?id=<?php echo isset($job['id']) ? $job['id'] : '#'; ?>" class="btn btn-outline-primary btn-sm mt-2 w-100">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" role="alert">
                    <h4 class="alert-heading">Tidak Ditemukan Lowongan yang Cocok</h4>
                    <p>Kami tidak menemukan lowongan kerja yang cocok dengan CV Anda. Silakan coba lagi dengan CV yang berbeda atau periksa kembali format CV Anda.</p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4 mb-5">
                <a href="upload.php" class="btn btn-outline-primary me-2">Upload CV Lain</a>
                <a href="index.html" class="btn btn-secondary">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>