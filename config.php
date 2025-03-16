<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv("RAILWAY_TCP_PROXY_DOMAIN"); // Public Host
$port = getenv("RAILWAY_TCP_PROXY_PORT");   // Public Port
$username = getenv("MYSQLUSER");            // root
$password = getenv("MYSQLPASSWORD");        // gHyUoHqpdvRWOuWDTcqOHjdQyibyfgJM
$database = getenv("MYSQLDATABASE");        // railway

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

echo "Koneksi berhasil!";
?>
