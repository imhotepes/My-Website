<?php
// Ambil kode dari URL
$code = isset($_GET['code']) ? $_GET['code'] : '';

if (!$code) {
    die("Error: No code provided.");
}

// Koneksi ke database
$host = getenv("RAILWAY_TCP_PROXY_DOMAIN");
$port = getenv("RAILWAY_TCP_PROXY_PORT");
$username = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$database = getenv("MYSQLDATABASE");

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Cari URL berdasarkan kode
$stmt = $conn->prepare("SELECT original_url FROM short_urls WHERE short_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $original_url = $row['original_url'];
    header("Location: " . $original_url);
    exit();
} else {
    die("Error: URL not found.");
}

$conn->close();
?>
