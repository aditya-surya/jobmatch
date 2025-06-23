<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');
error_log("Starting CV processing...");

// Check upload directory
$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        error_log("Failed to create uploads directory");
        die("Failed to create uploads directory. Please check permissions.");
    }
    error_log("Created uploads directory");
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    error_log("Uploads directory is not writable");
    die("Uploads directory is not writable. Please check permissions.");
}

// Load required files and check database connection
try {
    require_once 'config/database.php';
    require_once 'classes/NaiveBayes.php';
    require_once 'classes/CVParser.php';
} catch (Exception $e) {
    error_log("Error during initialization: " . $e->getMessage());
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
        
        // Log informasi file
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
            // Generate nama file unik
            $new_file_name = uniqid() . "_" . $file_name;
            $upload_file = $upload_dir . $new_file_name;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $upload_file)) {
                error_log("File successfully uploaded to: $upload_file");
                
                // Parse CV berdasarkan tipe file
                $parser = new CVParser();
                $cv_text = $parser->parseCV($upload_file, $file_type);
                
                // Cek hasil parsing
                if ($cv_text && !empty($cv_text) && strpos($cv_text, "Error:") !== 0) {
                    error_log("CV successfully parsed, text length: " . strlen($cv_text));
                    
                    try {
                        // Inisialisasi Naive Bayes
                        $naive_bayes = new NaiveBayes($conn);

                        // Deteksi bahasa secara otomatis dari teks CV
                        $cv_language = $naive_bayes->detectLanguage($cv_text);
                        error_log("Detected language: " . $cv_language);
                        
                        // Proses CV dengan Naive Bayes
                        $matching_jobs = $naive_bayes->findMatchingJobs($cv_text, $cv_language);
                        error_log("Found " . count($matching_jobs) . " matching jobs");
                        
                        // Simpan hasil di session untuk ditampilkan
                        $_SESSION["matching_jobs"] = $matching_jobs;
                        $_SESSION["cv_file"] = $new_file_name;
                        $_SESSION["cv_text"] = substr($cv_text, 0, 300) . "..."; // Simpan sebagian teks CV
                        
                        // Redirect ke halaman hasil
                        header("Location: results.php");
                        exit();
                    } catch (Exception $e) {
                        error_log("Error processing CV: " . $e->getMessage());
                        $errors[] = "Terjadi kesalahan saat memproses CV: " . $e->getMessage();
                    }
                } else {
                    // Handling error pada ekstraksi teks
                    if (strpos($cv_text, "Error:") === 0) {
                        $errors[] = $cv_text;
                    } else {
                        $errors[] = "Gagal mengekstrak teks dari CV. Pastikan file tidak rusak.";
                    }
                    error_log("Error parsing CV: " . ($cv_text ?: "Empty result"));
                }
            } else {
                $errors[] = "Gagal mengupload file. Silakan coba lagi.";
                error_log("Error uploading file: Could not move file to $upload_file");
            }
        }
    } else {
        $error_code = $_FILES["cv_file"]["error"] ?? 'unknown';
        $errors[] = "Silakan pilih file CV untuk diupload. (Error code: $error_code)";
        error_log("Upload error: File not present or error code: $error_code");
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
</head>
<body>
    <div class="container">
        <div class="upload-container text-center">
            <h1 class="mt-5 mb-4">Memproses CV</h1>
            <div class="card p-4 shadow upload-form">
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h5 class="alert-heading mb-3"><i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan!</h5>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><i class="fas fa-times-circle me-2"></i><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="upload.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-4" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0 text-muted">Memproses CV Anda, mohon tunggu sebentar...</p>
                            <small class="text-muted">Sistem sedang menganalisis CV dan mencari lowongan yang sesuai</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
