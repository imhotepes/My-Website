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
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #4a76b8, #6a90d4);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .container {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        h2 {
            margin-bottom: 20px;
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
        }

        input {
            background: white;
            color: black;
        }

        button {
            background: #ffd700;
            color: black;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #ffcc00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>URL Shortener</h2>
        <form method="POST">
            <input type="text" name="long_url" placeholder="Masukkan URL" required>
            <input type="text" name="custom_code" placeholder="Custom short URL (opsional)">
            <button type="submit">Shorten</button>
        </form>
    </div>
</body>
</html>
