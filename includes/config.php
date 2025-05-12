<?php
$host = 'localhost';
$dbname = 'study_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Replace session_start() with this:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>