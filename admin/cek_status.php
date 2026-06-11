<?php
require_once __DIR__ . '/../config/database.php';

echo "<h2>Cek Status Donasi di Database</h2>";

$donasi = $pdo->query("SELECT id_donasi, nama_donatur, nominal, status FROM donasi ORDER BY id_donasi DESC")->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Donatur</th><th>Nominal</th><th>Status</th></tr>";
foreach ($donasi as $d) {
    $warna = $d['status'] == 'confirmed' ? 'green' : 'orange';
    echo "<tr>";
    echo "<td>{$d['id_donasi']}</td>";
    echo "<td>{$d['nama_donatur']}</td>";
    echo "<td>Rp " . number_format($d['nominal'], 0, ',', '.') . "</td>";
    echo "<td style='color: $warna; font-weight: bold;'>{$d['status']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='dashboard.php' class='btn btn-primary'>Kembali ke Dashboard</a>";
echo " <a href='konfirmasi_donasi.php' class='btn btn-warning'>Buka Halaman Konfirmasi</a>";
?>