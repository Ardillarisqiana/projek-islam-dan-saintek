<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

// ============================================
// PROSES EXPORT EXCEL
// ============================================
if (isset($_GET['export_excel'])) {
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    
    // Ambil data
    $sql_masuk = "SELECT SUM(nominal) as total FROM donasi WHERE status = 'success' AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $stmt = $pdo->prepare($sql_masuk);
    $stmt->execute([$bulan, $tahun]);
    $total_masuk = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $sql_keluar = "SELECT SUM(jumlah) as total FROM pengeluaran WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?";
    $stmt = $pdo->prepare($sql_keluar);
    $stmt->execute([$bulan, $tahun]);
    $total_keluar = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $donasi = $pdo->prepare("SELECT * FROM donasi WHERE status = 'success' AND MONTH(created_at) = ? AND YEAR(created_at) = ? ORDER BY created_at DESC");
    $donasi->execute([$bulan, $tahun]);
    $data_masuk = $donasi->fetchAll(PDO::FETCH_ASSOC);
    
    $pengeluaran = $pdo->prepare("SELECT * FROM pengeluaran WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ? ORDER BY tanggal_keluar DESC");
    $pengeluaran->execute([$bulan, $tahun]);
    $data_keluar = $pengeluaran->fetchAll(PDO::FETCH_ASSOC);
    
    // Set header untuk download Excel
    $nama_file = "Laporan_Keuangan_" . date('F_Y', mktime(0,0,0,$bulan,1,$tahun)) . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Output Excel
    echo "LAPORAN KEUANGAN LAZISNU\n";
    echo "Periode: " . date('F Y', mktime(0,0,0,$bulan,1,$tahun)) . "\n\n";
    
    echo "RINGKASAN KEUANGAN\n";
    echo "Pemasukan (Donasi Success)\tRp " . number_format($total_masuk, 0, ',', '.') . "\n";
    echo "Pengeluaran\tRp " . number_format($total_keluar, 0, ',', '.') . "\n";
    echo "Saldo\tRp " . number_format($total_masuk - $total_keluar, 0, ',', '.') . "\n\n";
    
    echo "DETAIL PEMASUKAN\n";
    echo "Tanggal\tDonatur\tJenis Donasi\tProgram\tNominal\n";
    foreach ($data_masuk as $d) {
        echo date('d/m/Y', strtotime($d['created_at'])) . "\t";
        echo $d['nama_donatur'] . "\t";
        echo $d['jenis_donasi'] . "\t";
        echo $d['program'] . "\t";
        echo "Rp " . number_format($d['nominal'], 0, ',', '.') . "\n";
    }
    
    echo "\nDETAIL PENGELUARAN\n";
    echo "Tanggal\tJudul\tDeskripsi\tKategori\tJumlah\n";
    foreach ($data_keluar as $k) {
        echo date('d/m/Y', strtotime($k['tanggal_keluar'])) . "\t";
        echo $k['judul'] . "\t";
        echo ($k['deskripsi'] ?? '-') . "\t";
        echo $k['kategori'] . "\t";
        echo "Rp " . number_format($k['jumlah'], 0, ',', '.') . "\n";
    }
    
    exit;
}

// ============================================
// TAMPILAN NORMAL (TIDAK EXPORT)
// ============================================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$sql_masuk = "SELECT SUM(nominal) as total FROM donasi WHERE status = 'success' AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
$stmt = $pdo->prepare($sql_masuk);
$stmt->execute([$bulan, $tahun]);
$total_masuk = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$sql_keluar = "SELECT SUM(jumlah) as total FROM pengeluaran WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?";
$stmt = $pdo->prepare($sql_keluar);
$stmt->execute([$bulan, $tahun]);
$total_keluar = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$saldo = $total_masuk - $total_keluar;

$donasi = $pdo->prepare("SELECT * FROM donasi WHERE status = 'success' AND MONTH(created_at) = ? AND YEAR(created_at) = ? ORDER BY created_at DESC");
$donasi->execute([$bulan, $tahun]);
$data_masuk = $donasi->fetchAll(PDO::FETCH_ASSOC);

$pengeluaran = $pdo->prepare("SELECT * FROM pengeluaran WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ? ORDER BY tanggal_keluar DESC");
$pengeluaran->execute([$bulan, $tahun]);
$data_keluar = $pengeluaran->fetchAll(PDO::FETCH_ASSOC);

$pending = $pdo->query("SELECT COUNT(*) as jumlah, SUM(nominal) as total FROM donasi WHERE status != 'success' OR status IS NULL")->fetch(PDO::FETCH_ASSOC);

// Grafik 6 bulan
$grafik_data = [];
for ($i = 5; $i >= 0; $i--) {
    $bln = date('m', strtotime("-$i months"));
    $thn = date('Y', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    
    $stmt_masuk = $pdo->prepare("SELECT SUM(nominal) as total FROM donasi WHERE status = 'success' AND MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt_masuk->execute([$bln, $thn]);
    $msk = $stmt_masuk->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $stmt_keluar = $pdo->prepare("SELECT SUM(jumlah) as total FROM pengeluaran WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?");
    $stmt_keluar->execute([$bln, $thn]);
    $klr = $stmt_keluar->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $grafik_data[] = ['bulan' => $label, 'masuk' => $msk, 'keluar' => $klr];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan LAZISNU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar { background: #0f281f; min-height: 100vh; position: fixed; left: 0; top: 0; width: 260px; }
        .sidebar a { color: #cfdfd3; text-decoration: none; display: block; padding: 12px 20px; margin: 4px 12px; border-radius: 10px; }
        .sidebar a:hover, .sidebar a.active { background: #1b5e20; color: white; }
        .main-content { margin-left: 260px; padding: 20px 30px; }
        .card-stats { border-radius: 16px; padding: 20px; }
        .bg-masuk { background: linear-gradient(135deg, #1b5e20, #2e7d32); color: white; }
        .bg-keluar { background: linear-gradient(135deg, #c62828, #e53935); color: white; }
        .bg-saldo { background: linear-gradient(135deg, #f9a825, #fbc02d); color: #1f2f2a; }
        .bg-pending { background: linear-gradient(135deg, #ff9800, #fb8c00); color: white; }
        .btn-excel { background: #1f724c; border: none; }
        .btn-excel:hover { background: #0f5a3a; }
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
    <a href="konfirmasi_donasi.php"><i class="fas fa-check-double"></i> Konfirmasi</a>
    <a href="laporan_keuangan.php" class="active"><i class="fas fa-chart-line"></i> Laporan Keuangan</a>
    <a href="kegiatan.php"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
    <a href="pengeluaran.php"><i class="fas fa-money-bill-wave"></i> Pengeluaran</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-chart-line"></i> Laporan Keuangan</h2>
    
    <?php if (($pending['jumlah'] ?? 0) > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-clock"></i> Ada <strong><?= $pending['jumlah'] ?></strong> donasi pending (Rp <?= number_format($pending['total'] ?? 0, 0, ',', '.') ?>).
        <a href="konfirmasi_donasi.php" class="alert-link">Konfirmasi sekarang</a>
    </div>
    <?php endif; ?>
    
    <form method="GET" class="row g-3 mb-4">
        <div class="col-auto">
            <select name="bulan" class="form-select">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="tahun" class="form-select">
                <?php for ($y = 2023; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button></div>
        <div class="col-auto"><a href="laporan_keuangan.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a></div>
        <!-- TOMBOL EXPORT EXCEL -->
        <div class="col-auto">
            <a href="?export_excel=1&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-success btn-excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </form>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card-stats bg-masuk">
                <h6><i class="fas fa-arrow-down"></i> PEMASUKAN</h6>
                <h3>Rp <?= number_format($total_masuk, 0, ',', '.') ?></h3>
                <small>Donasi success</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stats bg-keluar">
                <h6><i class="fas fa-arrow-up"></i> PENGELUARAN</h6>
                <h3>Rp <?= number_format($total_keluar, 0, ',', '.') ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stats bg-saldo">
                <h6><i class="fas fa-wallet"></i> SALDO</h6>
                <h3>Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Tren Keuangan 6 Bulan</div>
        <div class="card-body">
            <canvas id="keuanganChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">Rincian Pemasukan (Donasi Success)</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-success">
                            <tr><th>Tanggal</th><th>Donatur</th><th>Jenis</th><th>Program</th><th>Nominal</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_masuk as $d): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
                                <td><?= htmlspecialchars($d['nama_donatur']) ?></td>
                                <td><?= $d['jenis_donasi'] ?></td>
                                <td><?= $d['program'] ?></td>
                                <td class="text-end">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($data_masuk)): ?>
                                <tr><td colspan="5" class="text-center">Belum ada pemasukan bulan ini</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">Rincian Pengeluaran</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-danger">
                            <tr><th>Tanggal</th><th>Judul</th><th>Kategori</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_keluar as $k): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($k['tanggal_keluar'])) ?></td>
                                <td><?= htmlspecialchars($k['judul']) ?></td>
                                <td><?= $k['kategori'] ?></td>
                                <td class="text-end">Rp <?= number_format($k['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($data_keluar)): ?>
                                <tr><td colspan="4" class="text-center">Belum ada pengeluaran bulan ini</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('keuanganChart').getContext('2d');
    const chartData = <?= json_encode($grafik_data) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(i => i.bulan),
            datasets: [
                { label: 'Pemasukan', data: chartData.map(i => i.masuk), backgroundColor: 'rgba(27,94,32,0.7)' },
                { label: 'Pengeluaran', data: chartData.map(i => i.keluar), backgroundColor: 'rgba(198,40,40,0.7)' }
            ]
        }
    });
</script>
</body>
</html>