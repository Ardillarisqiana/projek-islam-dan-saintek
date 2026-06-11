<?php
require_once '../config/database.php';

// Cek apakah user admin ada
$stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin'");
$user = $stmt->fetch();

if ($user) {
    echo "User ditemukan!<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Hash password: " . $user['password'] . "<br>";
    
    // Test password 'admin123'
    if (password_verify('admin123', $user['password'])) {
        echo "<span style='color:green'>✅ Password 'admin123' cocok!</span><br>";
    } else {
        echo "<span style='color:red'>❌ Password 'admin123' TIDAK cocok. Reset password diperlukan.</span><br>";
    }
} else {
    echo "User admin tidak ditemukan. Insert dulu ke database.<br>";
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')")->execute(['admin', $hash]);
    echo "User admin telah dibuat dengan password 'admin123'";
}
?>