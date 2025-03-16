<?php
// Koneksi ke database
$host = getenv("RAILWAY_TCP_PROXY_DOMAIN");
$port = getenv("RAILWAY_TCP_PROXY_PORT");
$username = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$database = getenv("MYSQLDATABASE");

$conn = new mysqli($host, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil kode dari URL
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// **Pastikan kode tidak kosong atau mengandung 'redirect.php'**
if (empty($code) || strpos($code, 'redirect.php') !== false) {
    die("Error: Kode pendek tidak valid.");
}

// Cek apakah kode ada di database
$stmt = $conn->prepare("SELECT long_url FROM urls WHERE short_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

// Jika kode ditemukan, redirect ke long_url
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $long_url = $row['long_url'];

    // **Cegah redirect loop (pastikan URL tujuan bukan `redirect.php`)**
    if (strpos($long_url, 'redirect.php') !== false) {
        die("Error: Redirect loop terdeteksi.");
    }

    // Redirect ke URL asli
    header("Location: $long_url", true, 301);
    exit();
} else {
    die("Error: Kode tidak ditemukan.");
}

$stmt->close();
$conn->close();
?>
