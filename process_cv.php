<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

// Check upload directory
$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        error_log("Failed to create uploads directory");
        die("Failed to create uploads directory. Please check permissions.");
    }
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    error_log("Uploads directory is not writable");
    die("Uploads directory is not writable. Please check permissions.");
}

// Load required files
try {
    require_once 'config/database.php';
    require_once 'classes/CVParser.php';
    require_once 'classes/NaiveBayes.php';
} catch (Exception $e) {
    error_log("Error loading required files: " . $e->getMessage());
    die("System initialization error: " . $e->getMessage());
}

// Array untuk menyimpan pesan error
$errors = [];
$cv_text = "";

// Cek apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Form submitted via POST");
    
    // Validasi file CV
    if (isset($_FILES["cv_file"]) && $_FILES["cv_file"]["error"] == 0) {
        $allowed_types = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "text/plain"
        ];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES["cv_file"]["type"];
        $file_size = $_FILES["cv_file"]["size"];
        $file_name = $_FILES["cv_file"]["name"];
        $file_tmp = $_FILES["cv_file"]["tmp_name"];
        
        error_log("Processing file: $file_name, Type: $file_type, Size: $file_size bytes");
        
        // Cek tipe file
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Format file tidak didukung. Silakan upload file PDF, DOC, DOCX, atau TXT.";
            error_log("Error: Unsupported file format: $file_type");
        }
        
        // Cek ukuran file
        if ($file_size > $max_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB.";
            error_log("Error: File too large: $file_size bytes");
        }
        
        // Jika tidak ada error, proses file
        if (empty($errors)) {
            try {
                // Generate nama file unik
                $new_file_name = uniqid() . "_" . basename($file_name);
                $upload_file = $upload_dir . $new_file_name;
                
                // Upload file
                if (move_uploaded_file($file_tmp, $upload_file)) {
                    error_log("File successfully uploaded to: $upload_file");
                    
                    // Parse CV
                    $parser = new CVParser();
                    $cv_text = $parser->parseCV($upload_file, $file_type);
                    
                    if ($cv_text && !empty($cv_text)) {
                        error_log("CV successfully parsed, text length: " . strlen($cv_text));
                        
                        // Inisialisasi Naive Bayes dengan koneksi MySQLi
                        $naive_bayes = new NaiveBayes($conn);
                        
                        // Deteksi bahasa
                        $cv_language = $naive_bayes->detectLanguage($cv_text);
                        error_log("Detected language: " . $cv_language);
                        
                        // Cari lowongan yang cocok
                        $matching_jobs = $naive_bayes->findMatchingJobs($cv_text, $cv_language);
                        
                        // Always save results and redirect, even if no matches found
                        error_log("Found " . count($matching_jobs) . " matching jobs");
                        
                        // Simpan hasil di session
                        $_SESSION["matching_jobs"] = $matching_jobs;
                        $_SESSION["cv_file"] = $new_file_name;
                        $_SESSION["cv_text"] = substr($cv_text, 0, 300) . "...";
                        
                        // Redirect ke halaman hasil
                        header("Location: results.php");
                        exit();
                    } else {
                        $errors[] = "Gagal mengekstrak teks dari CV. Pastikan file tidak rusak.";
                        error_log("Failed to extract text from CV");
                    }
                } else {
                    $errors[] = "Gagal mengupload file. Silakan coba lagi.";
                    error_log("Failed to move uploaded file to: $upload_file");
                }
            } catch (Exception $e) {
                $errors[] = "Terjadi kesalahan: " . $e->getMessage();
                error_log("Error processing CV: " . $e->getMessage());
            }
        }
    } else {
        $error_code = isset($_FILES["cv_file"]) ? $_FILES["cv_file"]["error"] : 'No file uploaded';
        $errors[] = "Silakan pilih file CV untuk diupload.";
        error_log("Upload error: " . $error_code);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memproses CV - JobMatch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/upload.css">
    <style>
        .processing-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        .error-list {
            text-align: left;
            margin-bottom: 2rem;
        }
        .spinner-container {
            margin: 2rem 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="processing-container">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h1 class="h3 mb-4">Memproses CV</h1>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h5 class="alert-heading mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Terjadi Kesalahan
                            </h5>
                            <ul class="error-list list-unstyled">
                                <?php foreach ($errors as $error): ?>
                                    <li>
                                        <i class="fas fa-times-circle me-2"></i>
                                        <?php echo htmlspecialchars($error); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="upload.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="spinner-container">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Mohon tunggu sebentar...</p>
                        <small class="text-muted d-block mt-2">
                            Sistem sedang menganalisis CV Anda untuk menemukan lowongan yang paling sesuai
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
