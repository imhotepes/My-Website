<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "MYSQLHOST: " . getenv("MYSQLHOST") . "<br>";
echo "MYSQLUSER: " . getenv("MYSQLUSER") . "<br>";
echo "MYSQLDATABASE: " . getenv("MYSQLDATABASE") . "<br>";
echo "MYSQLPORT: " . getenv("MYSQLPORT") . "<br>";
?>
