<?php
session_start();

// HARDCODED LOGIN - SEMENTARA (tanpa database)
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Username dan password default
$valid_username = 'admin';
$valid_password = 'admin123';

if ($username === $valid_username && $password === $valid_password) {
    $_SESSION['admin_logged'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'admin';
    header('Location: laporan_keuangan.php');
    exit;
} else {
    header('Location: login.php?error=invalid');
    exit;
}
?>