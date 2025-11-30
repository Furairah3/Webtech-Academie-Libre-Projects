<?php
$host = 'localhost';
$db   = 'webtech_2025A_chidima_ugwu';
$user = 'chidima.ugwu';        // change if you use different user
$pass = '66071288';            // change if you have password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>