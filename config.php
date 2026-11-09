<?php
declare(strict_types=1);
session_start();

$host = 'localhost';
$db   = 'quan_ly_chi_tieu';
$user = 'root';
$pass = ''; // XAMPP mặc định thường để trống
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Không kết nối được cơ sở dữ liệu. Hãy kiểm tra MySQL trong XAMPP và thông tin config.php.');
}
