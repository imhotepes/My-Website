<?php
$host = getenv('MYSQLHOST'); // Host database
$user = getenv('MYSQLUSER'); // User database
$pass = getenv('MYSQLPASSWORD'); // Password database
$dbname = getenv('MYSQLDATABASE'); // Nama database
$port = getenv('MYSQLPORT'); // Port database (3306)

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    echo "Koneksi berhasil ke database!";
}
?>