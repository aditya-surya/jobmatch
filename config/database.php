<?php
// Konfigurasi database
$db_host = "localhost";
$db_user = "root";  // Ganti dengan username MySQL Anda
$db_pass = "";      // Ganti dengan password MySQL Anda
$db_name = "jobmatch_db";

// Membuat koneksi MySQLi dengan error handling
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Cek koneksi
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    // Set karakter set koneksi
    if (!$conn->set_charset("utf8")) {
        error_log("Error loading character set utf8: " . $conn->error);
        throw new Exception("Error setting character set: " . $conn->error);
    }

    // Enable error reporting for debugging
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Maaf, terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.");
}
?>
