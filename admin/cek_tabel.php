<?php
require_once __DIR__ . '/../config/database.php';

echo "<h2>Debug Database LAZISNU</h2>";

// Cek tabel donasi
$cek = $pdo->query("SHOW TABLES LIKE 'donasi'");
if ($cek->rowCount() > 0) {
    echo "<h3>✅ Tabel donasi ditemukan</h3>";
    
    // Tampilkan struktur kolom
    $columns = $pdo->query("DESCRIBE donasi");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Kolom</th><th>Tipe</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tampilkan data
    $data = $pdo->query("SELECT * FROM donasi");
    echo "<h3>Data Donasi:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    for ($i = 0; $i < $data->columnCount(); $i++) {
        $col = $data->getColumnMeta($i);
        echo "<th>{$col['name']}</th>";
    }
    echo "</tr>";
    
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<h3>❌ Tabel donasi TIDAK ditemukan!</h3>";
    echo "<p>Silakan jalankan SQL berikut untuk membuat tabel:</p>";
    echo "<pre>
CREATE TABLE donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_donatur VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    jenis_donasi ENUM('Zakat Maal', 'Zakat Fitrah', 'Infak', 'Sedekah') NOT NULL,
    program VARCHAR(100),
    nominal DECIMAL(15,2) NOT NULL,
    catatan TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
    </pre>";
}
?>