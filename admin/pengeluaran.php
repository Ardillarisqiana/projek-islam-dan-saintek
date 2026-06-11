<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

// TAMBAH DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $stmt = $pdo->prepare("INSERT INTO pengeluaran (judul, deskripsi, jumlah, kategori, tanggal_keluar) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['judul'], $_POST['deskripsi'], $_POST['jumlah'], $_POST['kategori'], $_POST['tanggal_keluar']]);
    header('Location: pengeluaran.php');
    exit;
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM pengeluaran WHERE id = ?")->execute([$_GET['hapus']]);
    header('Location: pengeluaran.php');
    exit;
}

// AMBIL DATA (urutan dari ID paling lama)
$pengeluaran = $pdo->query("SELECT * FROM pengeluaran ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$total = $pdo->query("SELECT SUM(jumlah) as total FROM pengeluaran")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$jumlah = count($pengeluaran);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengeluaran - LAZISNU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar { background: #0f281f; min-height: 100vh; position: fixed; left: 0; top: 0; width: 260px; }
        .sidebar a { color: #cfdfd3; text-decoration: none; display: block; padding: 12px 20px; margin: 4px 12px; border-radius: 10px; }
        .sidebar a:hover, .sidebar a.active { background: #1b5e20; color: white; }
        .main-content { margin-left: 260px; padding: 20px 30px; }
        .card-stats { border-radius: 16px; padding: 20px; }
        .bg-pengeluaran { background: linear-gradient(135deg, #c62828, #e53935); color: white; }
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
    <a href="laporan_keuangan.php"><i class="fas fa-chart-line"></i> Laporan Keuangan</a>
    <a href="kegiatan.php"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
    <a href="pengeluaran.php"><i class="fas fa-money-bill-wave"></i> Pengeluaran</a>
    <a href="logout.php" class="active"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>


<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-money-bill-wave"></i> Kelola Pengeluaran Dana</h2>
    
    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card-stats bg-pengeluaran">
                <h6><i class="fas fa-chart-line"></i> Total Pengeluaran</h6>
                <h3>Rp <?= number_format($total, 0, ',', '.') ?></h3>
                <small><?= $jumlah ?> transaksi</small>
            </div>
        </div>
    </div>
    
  <!-- Form Tambah -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-plus"></i> Tambah Pengeluaran Baru
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Judul Pengeluaran</label>
                <input type="text" name="judul" class="form-control" placeholder="Masukkan judul pengeluaran" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Keluar</label>
                <input type="date" name="tanggal_keluar" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Jumlah (Rp)</label>
                <input type="number" name="jumlah" class="form-control" placeholder="Masukkan jumlah pengeluaran" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Kategori</label>
                <select name="kategori" class="form-select">
                    <option>Operasional</option>
                    <option>Program Sosial</option>
                    <option>Bencana</option>
                    <option>Pendidikan</option>
                    <option>Kesehatan</option>
                    <option>Lainnya</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Masukkan deskripsi (opsional)"></textarea>
            </div>

            <button type="submit" name="tambah" class="btn btn-danger">
                <i class="fas fa-save"></i> Simpan Pengeluaran
            </button>
        </form>
    </div>
</div>
    
    <!-- Tabel Data (pakai nomor urut, ID tidak ditampilkan) -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-list"></i> Daftar Pengeluaran
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-danger">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($jumlah > 0): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pengeluaran as $p): ?>
                        <tr>
                            <td><?= $no++ ?> <!-- Nomor urut, bukan ID -->
                            <td><?= date('d/m/Y', strtotime($p['tanggal_keluar'])) ?></td>
                            <td><?= htmlspecialchars($p['judul']) ?></td>
                            <td><?= htmlspecialchars($p['deskripsi'] ?: '-') ?></td>
                            <td><?= $p['kategori'] ?></td>
                            <td class="text-end">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                            <td>
                                <a href="?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pengeluaran ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted"></i>
                                <p class="mt-2">Belum ada data pengeluaran</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total Keseluruhan:</td>
                        <td class="text-end">Rp <?= number_format($total, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="laporan_keuangan.php" class="btn btn-primary">
            <i class="fas fa-chart-line"></i> Lihat Laporan Keuangan
        </a>
    </div>
</div>
</body>
</html>