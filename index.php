<?php
// Konfigurasi Database
$host = getenv("RAILWAY_TCP_PROXY_DOMAIN");
$port = getenv("RAILWAY_TCP_PROXY_PORT");
$username = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$database = getenv("MYSQLDATABASE");

// Koneksi ke Database
$conn = new mysqli($host, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// **1️⃣ Jika pertama kali diakses, buat tabel**
$sql = "CREATE TABLE IF NOT EXISTS short_urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_code VARCHAR(10) NOT NULL UNIQUE,
    long_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// **2️⃣ Jika URL pendek diakses, redirect ke URL panjang**
$short_code = trim($_SERVER["REQUEST_URI"], "/");
if (!empty($short_code) && $short_code !== "index.php") {
    $stmt = $conn->prepare("SELECT long_url FROM short_urls WHERE short_code = ?");
    $stmt->bind_param("s", $short_code);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($long_url);
        $stmt->fetch();
        $stmt->close();
        $conn->close();
        header("Location: " . $long_url);
        exit();
    } else {
        die("URL tidak ditemukan.");
    }
}

// **3️⃣ Jika ada request POST, buat URL pendek**
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["long_url"])) {
    $long_url = trim($_POST["long_url"]);

    // Validasi URL
    if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
        die("URL tidak valid.");
    }

    // Generate kode pendek
    $short_code = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO short_urls (short_code, long_url) VALUES (?, ?)");
    $stmt->bind_param("ss", $short_code, $long_url);

    if ($stmt->execute()) {
        echo "URL pendek Anda: <a href='https://api.zulfah.me/$short_code'>https://api.zulfah.me/$short_code</a>";
    } else {
        echo "Terjadi kesalahan.";
    }

    $stmt->close();
    $conn->close();
    exit();
}

// **4️⃣ Jika tidak ada request, tampilkan form input**
?>
<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener</title>
</head>
<body>
    <h2>URL Shortener</h2>
    <form method="POST">
        <input type="text" name="long_url" placeholder="Masukkan URL" required>
        <button type="submit">Shorten</button>
    </form>
</body>
</html>
