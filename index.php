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

// Buat tabel jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS short_urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_code VARCHAR(10) NOT NULL UNIQUE,
    long_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Redirect jika URL pendek diakses
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
        die("<h2 style='color:red;'>URL tidak ditemukan.</h2>");
    }
}

// Proses pembuatan URL pendek
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["long_url"])) {
    $long_url = trim($_POST["long_url"]);
    $custom_code = trim($_POST["custom_code"]);
    
    if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
        die("<h2 style='color:red;'>URL tidak valid.</h2>");
    }
    
    if (!empty($custom_code)) {
        $stmt = $conn->prepare("SELECT id FROM short_urls WHERE short_code = ?");
        $stmt->bind_param("s", $custom_code);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            die("<h2 style='color:red;'>Short URL sudah digunakan, coba yang lain.</h2>");
        }
        $short_code = $custom_code;
    } else {
        $short_code = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
    }
    
    $stmt = $conn->prepare("INSERT INTO short_urls (short_code, long_url) VALUES (?, ?)");
    $stmt->bind_param("ss", $short_code, $long_url);
    
    if ($stmt->execute()) {
        echo "<h2>URL pendek Anda: <a href='https://zulfah.me/$short_code'>https://zulfah.me/$short_code</a></h2>";
    } else {
        echo "<h2 style='color:red;'>Terjadi kesalahan.</h2>";
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container text-center mt-5">
    <h2 class="mb-4">URL Shortener</h2>
    <form method="POST" class="w-50 mx-auto">
        <input type="text" name="long_url" class="form-control mb-2" placeholder="Masukkan URL" required>
        <input type="text" name="custom_code" class="form-control mb-2" placeholder="Custom short URL (opsional)">
        <button type="submit" class="btn btn-primary">Shorten</button>
    </form>
</body>
</html>