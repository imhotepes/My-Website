<?php
// Konfigurasi koneksi ke database Railway
$host = getenv("RAILWAY_TCP_PROXY_DOMAIN");
$port = getenv("RAILWAY_TCP_PROXY_PORT");
$username = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$database = getenv("MYSQLDATABASE");

// Koneksi ke database
$conn = new mysqli($host, $username, $password, $database, $port);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil kode dari URL
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// Validasi kode pendek agar tidak salah membaca
if (empty($code) || $code === "redirect.php") {
    die("Kode tidak valid atau tidak ditemukan.");
}

// Cek apakah kode ada di database
$stmt = $conn->prepare("SELECT long_url FROM urls WHERE short_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $long_url = $row['long_url'];

    // Pastikan tidak mengarahkan ke dirinya sendiri
    if ($long_url === "https://api.zulfah.me/redirect.php") {
        die("Error: Redirect loop terdeteksi.");
    }

    // Redirect ke URL asli
    header("Location: $long_url", true, 301);
    exit();
} else {
    die("URL tidak ditemukan.");
}

$stmt->close();
$conn->close();
?>
