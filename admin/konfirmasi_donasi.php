<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

// Konfirmasi donasi (satuan)
if (isset($_GET['konfirmasi_id'])) {
    $id = (int)$_GET['konfirmasi_id'];
    
    // Ambil nominal dan program donasi
    $stmt = $pdo->prepare("SELECT nominal, program FROM donasi WHERE id = ?");
    $stmt->execute([$id]);
    $donasi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($donasi) {
        // Update status donasi
        $pdo->prepare("UPDATE donasi SET status = 'success' WHERE id = ?")->execute([$id]);
        
        // Update terkumpul di tabel kegiatan (cari kegiatan yang sesuai dengan program)
        // Asumsi: program donasi sama dengan judul kegiatan
        $stmt = $pdo->prepare("UPDATE kegiatan SET terkumpul = terkumpul + ? WHERE judul = ? AND status = 'aktif'");
        $stmt->execute([$donasi['nominal'], $donasi['program']]);
    }
    
    header('Location: konfirmasi_donasi.php');
    exit;
}

// Konfirmasi semua donasi pending
if (isset($_GET['konfirmasi_semua'])) {
    // Ambil semua donasi pending
    $donasi_list = $pdo->query("SELECT nominal, program FROM donasi WHERE status != 'success' OR status IS NULL")->fetchAll();
    
    // Update status semua donasi
    $pdo->prepare("UPDATE donasi SET status = 'success' WHERE status != 'success' OR status IS NULL")->execute();
    
    // Update terkumpul per kegiatan
    foreach ($donasi_list as $d) {
        $stmt = $pdo->prepare("UPDATE kegiatan SET terkumpul = terkumpul + ? WHERE judul = ? AND status = 'aktif'");
        $stmt->execute([$d['nominal'], $d['program']]);
    }
    
    header('Location: konfirmasi_donasi.php');
    exit;
}

// Hapus donasi
if (isset($_GET['hapus_id'])) {
    $id = (int)$_GET['hapus_id'];
    $pdo->prepare("DELETE FROM donasi WHERE id = ?")->execute([$id]);
    header('Location: konfirmasi_donasi.php');
    exit;
}

$pending = $pdo->query("SELECT * FROM donasi WHERE status != 'success' OR status IS NULL ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$confirmed = $pdo->query("SELECT * FROM donasi WHERE status = 'success' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_pending = $pdo->query("SELECT SUM(nominal) as total FROM donasi WHERE status != 'success' OR status IS NULL")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$jumlah_pending = count($pending);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Donasi - LAZISNU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar { background: #0f281f; min-height: 100vh; position: fixed; left: 0; top: 0; width: 260px; }
        .sidebar a { color: #cfdfd3; text-decoration: none; display: block; padding: 12px 20px; margin: 4px 12px; border-radius: 10px; }
        .sidebar a:hover, .sidebar a.active { background: #1b5e20; color: white; }
        .main-content { margin-left: 260px; padding: 20px 30px; }
        .btn-konfirmasi-semua { background: #ff9800; color: white; font-weight: bold; padding: 12px 24px; border-radius: 8px; border: none; }
        .btn-konfirmasi-semua:hover { background: #fb8c00; }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="p-3">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="../foto/logo.png" alt="LAZISNU" style="height: 45px; width: auto; object-fit: contain;">
            <div>
                <h4 class="text-white" style="margin: 0; font-size: 1.3rem; font-weight: 800;">LAZISNU</h4>
                <span style="color: #c9a03d; font-size: 0.7rem;">Bojongwetan</span>
            </div>
        </div>
    </div>
    <a href="dashboard.php"><i class="fas fa-hand-holding-heart"></i> Donasi</a>
    <a href="konfirmasi_donasi.php" class="active"><i class="fas fa-check-double"></i> Konfirmasi</a>
    <a href="laporan_keuangan.php"><i class="fas fa-chart-line"></i> Laporan Keuangan</a>
    <a href="kegiatan.php"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
    <a href="pengeluaran.php"><i class="fas fa-money-bill-wave"></i> Pengeluaran</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-check-double"></i> Konfirmasi Donasi</h2>
    
    <?php if ($jumlah_pending > 0): ?>
    <div class="mb-4 text-center">
        <a href="?konfirmasi_semua=1" class="btn btn-konfirmasi-semua btn-lg" onclick="return confirm('Yakin ingin mengkonfirmasi SEMUA <?= $jumlah_pending ?> donasi pending?')">
            <i class="fas fa-check-double"></i> KONFIRMASI SEMUA (<?= $jumlah_pending ?> Donasi)
        </a>
    </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">DONASI PENDING</div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-warning">
                    <tr><th>No</th><th>Donatur</th><th>Jenis</th><th>Program</th><th>Nominal</th><th>Tanggal</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($jumlah_pending > 0): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pending as $d): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($d['nama_donatur']) ?></td>
                            <td><?= $d['jenis_donasi'] ?></td>
                            <td><?= $d['program'] ?></td>
                            <td class="text-end">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                            <td>
                                <a href="?konfirmasi_id=<?= $d['id'] ?>" class="btn btn-sm btn-success">Konfirmasi</a>
                                <a href="?hapus_id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus donasi ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-success">✅ Semua donasi sudah dikonfirmasi!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-success text-white">DONASI SUCCESS</div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-success">
                    <tr><th>No</th><th>Donatur</th><th>Jenis</th><th>Program</th><th>Nominal</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                    <?php if (count($confirmed) > 0): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($confirmed as $d): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($d['nama_donatur']) ?></td>
                            <td><?= $d['jenis_donasi'] ?></td>
                            <td><?= $d['program'] ?></td>
                            <td class="text-end">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Belum ada donasi yang dikonfirmasi</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="laporan_keuangan.php" class="btn btn-primary">Lihat Laporan Keuangan</a>
    </div>
</div>
</body>
</html>