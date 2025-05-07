<?php
session_start();
require_once 'config/database.php';
require_once 'classes/NaiveBayes.php';
require_once 'classes/CVParser.php';

// Fungsi untuk membersihkan input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Array untuk menyimpan pesan error
$errors = [];
$cv_text = "";

// Cek apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi file CV
    if (isset($_FILES["cv_file"]) && $_FILES["cv_file"]["error"] == 0) {
        $allowed_types = ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES["cv_file"]["type"];
        $file_size = $_FILES["cv_file"]["size"];
        $file_name = $_FILES["cv_file"]["name"];
        $file_tmp = $_FILES["cv_file"]["tmp_name"];
        
        // Log informasi file
        error_log("Memproses file: $file_name, Tipe: $file_type, Ukuran: $file_size bytes");
        
        // Cek tipe file
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Format file tidak didukung. Silakan upload file PDF, DOC, atau DOCX.";
            error_log("Error: Format file tidak didukung: $file_type");
        }
        
        // Cek ukuran file
        if ($file_size > $max_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB.";
            error_log("Error: Ukuran file terlalu besar: $file_size bytes");
        }
        
        // Jika tidak ada error, proses file
        if (empty($errors)) {
            // Buat direktori uploads jika belum ada
            $upload_dir = "uploads/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
                error_log("Membuat direktori uploads");
            }
            
            // Generate nama file unik
            $new_file_name = uniqid() . "_" . $file_name;
            $upload_file = $upload_dir . $new_file_name;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $upload_file)) {
                error_log("File berhasil diupload ke: $upload_file");
                
                // Parse CV berdasarkan tipe file
                $parser = new CVParser();
                $cv_text = $parser->parseCV($upload_file, $file_type);
                
                // Cek hasil parsing
                if ($cv_text && !empty($cv_text) && strpos($cv_text, "Error:") !== 0) {
                    error_log("CV berhasil diparsing, panjang teks: " . strlen($cv_text));
                    
                    // Dapatkan bahasa CV dari form
                    $cv_language = clean_input($_POST["cv_language"] ?? "id");
                    
                    // Inisialisasi Naive Bayes
                    $naive_bayes = new NaiveBayes($conn);
                    
                    // Proses CV dengan Naive Bayes
                    $matching_jobs = $naive_bayes->findMatchingJobs($cv_text, $cv_language);
                    
                    // Simpan hasil di session untuk ditampilkan
                    $_SESSION["matching_jobs"] = $matching_jobs;
                    $_SESSION["cv_file"] = $new_file_name;
                    $_SESSION["cv_text"] = substr($cv_text, 0, 300) . "..."; // Simpan sebagian teks CV
                    
                    // Redirect ke halaman hasil
                    header("Location: results.php");
                    exit();
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
                error_log("Error upload file: Tidak bisa memindahkan file ke $upload_file");
            }
        }
    } else {
        $error_code = $_FILES["cv_file"]["error"] ?? 'unknown';
        $errors[] = "Silakan pilih file CV untuk diupload. (Error code: $error_code)";
        error_log("Error upload: File tidak ada atau error code: $error_code");
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="upload-container text-center">
            <h1 class="mt-5 mb-4">Memproses CV</h1>
            <div class="card p-4 shadow upload-form">
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">Terjadi kesalahan!</h4>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="upload.php" class="btn btn-primary">Kembali</a>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Memproses CV Anda, mohon tunggu sebentar...</p>
                        </div>
                        <script>
                            // Redirect otomatis jika processing selesai
                            setTimeout(function() {
                                window.location.href = "results.php";
                            }, 3000);
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>