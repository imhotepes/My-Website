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
$code = $_GET['code'] ?? '';

if ($code) {
    // Cek apakah kode ada di database
    $stmt = $conn->prepare("SELECT long_url FROM urls WHERE short_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $long_url = $row['long_url'];

        // Redirect ke URL asli
        header("Location: $long_url", true, 301);
        exit();
    } else {
        echo "URL tidak ditemukan.";
    }

    $stmt->close();
} else {
    echo "Kode tidak valid.";
}

$conn->close();
?>
