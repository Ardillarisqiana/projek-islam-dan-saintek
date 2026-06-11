<?php
session_start();

if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Konfirmasi donasi
if (isset($_GET['konfirmasi_id'])) {
    $id = (int) $_GET['konfirmasi_id'];
    $stmt = $pdo->prepare("UPDATE donasi SET status = 'success' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard.php');
    exit;
}

// Hapus donasi (tambahan)
if (isset($_GET['hapus_id'])) {
    $id = (int) $_GET['hapus_id'];
    $stmt = $pdo->prepare("DELETE FROM donasi WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard.php');
    exit;
}

// Ambil semua donasi urut dari ID paling lama
$donasi = $pdo->query("SELECT * FROM donasi ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Statistik pending
$total_pending = $pdo->query("
    SELECT SUM(nominal) as total, COUNT(*) as jumlah
    FROM donasi
    WHERE status != 'success' OR status IS NULL
")->fetch(PDO::FETCH_ASSOC);

// Statistik success
$total_success = $pdo->query("
    SELECT SUM(nominal) as total
    FROM donasi
    WHERE status = 'success'
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Donasi - LAZISNU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .sidebar { background: linear-gradient(135deg, #0f281f); min-height: 100vh; position: fixed; left: 0; top: 0; width: 260px; }
        .sidebar a { color: #cfdfd3; text-decoration: none; display: block; padding: 12px 20px; margin: 4px 12px; border-radius: 10px; }
        .sidebar a:hover, .sidebar a.active { background: #1b5e20; color: white; }
        .main-content { margin-left: 260px; padding: 20px 30px; }
        .card-stats { border-radius: 16px; padding: 20px; color: white; }
        .bg-success-custom { background: linear-gradient(135deg, #1b5e20, #2e7d32); }
        .bg-pending { background: linear-gradient(135deg, #ff9800, #fb8c00); }
        .table-pending { background: #fff3e0; }
        .table-success-custom { background: #e8f5e9; }
        /* TAMBAHAN CSS UNTUK BUKTI */
        .bukti-img { max-width: 50px; max-height: 50px; cursor: pointer; border-radius: 5px; }
        .modal-img { max-width: 100%; max-height: 80vh; }
    </style>
</head>
<body>

<!-- TAMBAHAN MODAL PREVIEW BUKTI -->
<div class="modal fade" id="buktiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> Bukti Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalBuktiImg" class="modal-img" alt="Bukti Pembayaran">
            </div>
        </div>
    </div>
</div>

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
    <a href="dashboard.php" class="active"><i class="fas fa-hand-holding-heart"></i> Donasi</a>
    <a href="konfirmasi_donasi.php"><i class="fas fa-check-double"></i> Konfirmasi</a>
    <a href="laporan_keuangan.php"><i class="fas fa-chart-line"></i> Laporan Keuangan</a>
    <a href="kegiatan.php"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
    <a href="pengeluaran.php"><i class="fas fa-money-bill-wave"></i> Pengeluaran</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-hand-holding-heart"></i> Dashboard Donasi</h2>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card-stats bg-pending">
                <h6><i class="fas fa-clock"></i> PENDING</h6>
                <h3><?= $total_pending['jumlah'] ?? 0 ?> Donasi</h3>
                <h4>Rp <?= number_format($total_pending['total'] ?? 0, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-stats bg-success-custom">
                <h6><i class="fas fa-check-circle"></i> SUCCESS (Terkonfirmasi)</h6>
                <h3>Rp <?= number_format($total_success, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Tabel Donasi -->
    <div class="card">
        <div class="card-header bg-success text-white">Daftar Semua Donasi</div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Donatur</th>
                        <th>No HP</th>      <!-- TAMBAHAN -->
                        <th>Jenis</th>
                        <th>Program</th>
                        <th>Nominal</th>
                        <th>Tanggal</th>
                        <th>Bukti</th>       <!-- TAMBAHAN -->
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php foreach ($donasi as $d): ?>
                    <tr class="<?= $d['status'] != 'success' ? 'table-pending' : 'table-success-custom' ?>">
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($d['nama_donatur']) ?></td>
                        <td><?= htmlspecialchars($d['no_hp'] ?? '-') ?></td>   <!-- TAMBAHAN -->
                        <td><?= htmlspecialchars($d['jenis_donasi']) ?></td>
                        <td><?= htmlspecialchars($d['program']) ?></td>
                        <td class="text-end">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                        <!-- TAMBAHAN KOLOM BUKTI -->
                        <td class="text-center">
                            <?php if (!empty($d['bukti_pembayaran'])): ?>
                                <a href="#" onclick="showBukti('<?= $d['bukti_pembayaran'] ?>'); return false;">
                                    <img src="../<?= $d['bukti_pembayaran'] ?>" class="bukti-img" alt="Bukti">
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['status'] == 'success'): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Success</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['status'] != 'success'): ?>
                                <a href="?konfirmasi_id=<?= $d['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi donasi ini?')">
                                    <i class="fas fa-check"></i> Konfirmasi
                                </a>
                                <a href="?hapus_id=<?= $d['id'] ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Hapus donasi ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled><i class="fas fa-lock"></i> Selesai</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAMBAHAN SCRIPT UNTUK PREVIEW BUKTI -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showBukti(path) {
    const modalImg = document.getElementById('modalBuktiImg');
    modalImg.src = '../' + path;
    const modal = new bootstrap.Modal(document.getElementById('buktiModal'));
    modal.show();
}
</script>
</body>
</html>