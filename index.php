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
        echo "
        <div class='message success'>
            <h3>✅ URL Berhasil Dipendekkan!</h3>
            <p>Short URL Anda:</p>
            <a href='https://zulfah.me/$short_code' target='_blank'>
                https://zulfah.me/$short_code
            </a>
        </div>";
        exit();
    } else {
        echo "
        <div class='message error'>
            <h3>❌ Short URL sudah digunakan!</h3>
            <p>Coba gunakan nama lain untuk short URL Anda.</p>
        </div>";
        exit();
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
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        input {
            width: calc(100% - 20px);
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: white;
            color: black;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }

        .custom-url {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }

        .custom-url span {
            padding: 10px;
            background: #ddd;
            font-weight: bold;
            color: rgb(245, 0, 0); /* Warna teks menjadi hitam */
        }

        .custom-url input {
            flex: 1;
            border: none;
            outline: none;
            padding-left: 5px;
        }

        button {
            width: 100%;
            padding: 10px; /* Dikembalikan ke ukuran semula */
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            background: #ffd700;
            color: black;
            font-weight: bold;
            font-size: 16px; /* Ukuran font kembali seperti sebelumnya */
            font-family: 'Montserrat', sans-serif; /* Font lebih stylish dan modern */
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover {
            background: #ffcc00;
            transform: scale(1.05);
        }

        .message {
            max-width: 400px;
            margin: 20px auto;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-in-out;
        }

        .success {
            background: #4CAF50;
            color: white;
        }

        .error {
            background: #FF4D4D;
            color: white;
        }

        .message h3 {
            margin: 0;
            font-size: 18px;
        }

        .message a {
            color: white;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }

        .message a:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>URL Shortener</h2>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="long_url" placeholder="Masukkan URL" required>
                <div class="custom-url">
                    <span>https://zulfah.me/</span>
                    <input type="text" name="custom_code" placeholder="Alias (opsional)">
                </div>
                <button type="submit">Shorten</button>
            </div>
        </form>
    </div>
</body>
</html>
