<?php
// Konfigurasi database
$db_host = "localhost";
$db_user = "root";  // Ganti dengan username MySQL Anda
$db_pass = "";      // Ganti dengan password MySQL Anda
$db_name = "jobmatch_db";

// Membuat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set karakter set koneksi
$conn->set_charset("utf8");
?>
