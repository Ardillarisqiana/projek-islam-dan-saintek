<?php
session_start();
if (!isset($_SESSION['admin_logged'])) die('Akses ditolak');
require_once '../config/database.php';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Data pemasukan
$donasi = $pdo->prepare("SELECT * FROM donasi WHERE status = 'confirmed' AND MONTH(created_at) = ? AND YEAR(created_at) = ?");
$donasi->execute([$bulan, $tahun]);
$data_masuk = $donasi->fetchAll();

// Data pengeluaran
$pengeluaran = $pdo->prepare("SELECT * FROM pengeluaran WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?");
$pengeluaran->execute([$bulan, $tahun]);
$data_keluar = $pengeluaran->fetchAll();

$total_masuk = array_sum(array_column($data_masuk, 'nominal'));
$total_keluar = array_sum(array_column($data_keluar, 'jumlah'));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_keuangan_{$tahun}_{$bulan}.xls");

echo "LAPORAN KEUANGAN LAZISNU\n";
echo "Periode: " . date('F Y', mktime(0,0,0,$bulan,1,$tahun)) . "\n\n";
echo "=== PEMASUKAN ===\n";
echo "Tanggal\tDonatur\tJenis\tProgram\tNominal\n";
foreach ($data_masuk as $d) {
    echo date('d/m/Y', strtotime($d['created_at'])) . "\t{$d['nama_donatur']}\t{$d['jenis_donasi']}\t{$d['program']}\t{$d['nominal']}\n";
}
echo "\nTotal Pemasukan: Rp " . number_format($total_masuk,0,',','.') . "\n\n";

echo "=== PENGELUARAN ===\n";
echo "Tanggal\tJudul\tKategori\tJumlah\n";
foreach ($data_keluar as $k) {
    echo date('d/m/Y', strtotime($k['tanggal_keluar'])) . "\t{$k['judul']}\t{$k['kategori']}\t{$k['jumlah']}\n";
}
echo "\nTotal Pengeluaran: Rp " . number_format($total_keluar,0,',','.') . "\n";
echo "\nSALDO: Rp " . number_format($total_masuk - $total_keluar,0,',','.') . "\n";
?>