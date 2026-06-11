<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';

$upload_dir = __DIR__ . '/../uploads_kegiatan/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// TAMBAH KEGIATAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $jenis = $_POST['jenis'];
    $jenis_program = $_POST['jenis_program'];
    $deskripsi = $_POST['deskripsi'];
    $target = $_POST['target'];
    $tanggal_kegiatan = $_POST['tanggal_kegiatan'];
    $gambar_list = [];
    
    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $total_files = count($_FILES['gambar']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['gambar']['tmp_name'][$i];
                $file_type = $_FILES['gambar']['type'][$i];
                $file_size = $_FILES['gambar']['size'][$i];
                
                if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) {
                    $ext = pathinfo($_FILES['gambar']['name'][$i], PATHINFO_EXTENSION);
                    $filename = time() . '_' . uniqid() . '_' . $i . '.' . $ext;
                    $destination = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $gambar_list[] = 'uploads_kegiatan/' . $filename;
                    }
                }
            }
        }
    }
    
    $gambar_json = !empty($gambar_list) ? json_encode($gambar_list) : null;
    
    $stmt = $pdo->prepare("INSERT INTO kegiatan (judul, jenis, jenis_program, deskripsi, gambar_url, target_dana, tanggal_kegiatan, terkumpul, status) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'aktif')");
    $stmt->execute([$judul, $jenis, $jenis_program, $deskripsi, $gambar_json, $target, $tanggal_kegiatan]);
    header('Location: kegiatan.php');
    exit;
}

// EDIT KEGIATAN
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $jenis = $_POST['jenis'];
    $jenis_program = $_POST['jenis_program'];
    $deskripsi = $_POST['deskripsi'];
    $target = $_POST['target'];
    $tanggal_kegiatan = $_POST['tanggal_kegiatan'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("SELECT gambar_url FROM kegiatan WHERE id = ?");
    $stmt->execute([$id]);
    $gambar_json_lama = $stmt->fetchColumn();
    $gambar_list = json_decode($gambar_json_lama, true) ?: [];
    
    if (isset($_FILES['gambar_baru']) && !empty($_FILES['gambar_baru']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $total_files = count($_FILES['gambar_baru']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar_baru']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['gambar_baru']['tmp_name'][$i];
                $file_type = $_FILES['gambar_baru']['type'][$i];
                $file_size = $_FILES['gambar_baru']['size'][$i];
                
                if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) {
                    $ext = pathinfo($_FILES['gambar_baru']['name'][$i], PATHINFO_EXTENSION);
                    $filename = time() . '_' . uniqid() . '_' . $i . '.' . $ext;
                    $destination = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $gambar_list[] = 'uploads_kegiatan/' . $filename;
                    }
                }
            }
        }
    }
    
    if (isset($_POST['hapus_gambar']) && is_array($_POST['hapus_gambar'])) {
        foreach ($_POST['hapus_gambar'] as $gambar_hapus) {
            $file_path = __DIR__ . '/../' . $gambar_hapus;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $key = array_search($gambar_hapus, $gambar_list);
            if ($key !== false) {
                unset($gambar_list[$key]);
            }
        }
        $gambar_list = array_values($gambar_list);
    }
    
    $gambar_json = !empty($gambar_list) ? json_encode($gambar_list) : null;
    
    $stmt = $pdo->prepare("UPDATE kegiatan SET judul = ?, jenis = ?, jenis_program = ?, deskripsi = ?, gambar_url = ?, target_dana = ?, tanggal_kegiatan = ?, status = ? WHERE id = ?");
    $stmt->execute([$judul, $jenis, $jenis_program, $deskripsi, $gambar_json, $target, $tanggal_kegiatan, $status, $id]);
    header('Location: kegiatan.php');
    exit;
}

// HAPUS KEGIATAN
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("SELECT gambar_url FROM kegiatan WHERE id = ?");
    $stmt->execute([$_GET['hapus']]);
    $gambar_json = $stmt->fetchColumn();
    if ($gambar_json) {
        $gambar_list = json_decode($gambar_json, true);
        if (is_array($gambar_list)) {
            foreach ($gambar_list as $gambar) {
                $file_path = __DIR__ . '/../' . $gambar;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM kegiatan WHERE id = ?");
    $stmt->execute([$_GET['hapus']]);
    header('Location: kegiatan.php');
    exit;
}

// UBAH STATUS
if (isset($_GET['ubah_status'])) {
    $id = $_GET['ubah_status'];
    $status_baru = $_GET['status'];
    
    $stmt = $pdo->prepare("UPDATE kegiatan SET status = ? WHERE id = ?");
    $stmt->execute([$status_baru, $id]);
    
    if ($status_baru == 'selesai') {
        $stmt = $pdo->prepare("SELECT jenis FROM kegiatan WHERE id = ?");
        $stmt->execute([$id]);
        $jenis = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM donasi WHERE status = 'success' AND jenis_donasi = ?");
        $stmt->execute([$jenis]);
        $total = $stmt->fetchColumn() ?? 0;
        
        $stmt = $pdo->prepare("UPDATE kegiatan SET terkumpul = ? WHERE id = ?");
        $stmt->execute([$total, $id]);
    }
    
    header('Location: kegiatan.php');
    exit;
}

// AMBIL DATA
$kegiatan = $pdo->query("SELECT * FROM kegiatan ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_kegiatan = count($kegiatan);
$total_target = $pdo->query("SELECT SUM(target_dana) as total FROM kegiatan")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$total_terkumpul = $pdo->query("SELECT SUM(terkumpul) as total FROM kegiatan")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM kegiatan WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kegiatan - LAZISNU Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar { background: #0f281f; min-height: 100vh; position: fixed; left: 0; top: 0; width: 260px; }
        .sidebar a { color: #cfdfd3; text-decoration: none; display: block; padding: 12px 20px; margin: 4px 12px; border-radius: 10px; }
        .sidebar a:hover, .sidebar a.active { background: #1b5e20; color: white; }
        .main-content { margin-left: 260px; padding: 20px 30px; }
        .card-stats { border-radius: 16px; padding: 20px; }
        .bg-kegiatan { background: linear-gradient(135deg, #1b5e20, #2e7d32); color: white; }
        .bg-target { background: linear-gradient(135deg, #1565c0, #1976d2); color: white; }
        .bg-terkumpul { background: linear-gradient(135deg, #ff9800, #fb8c00); color: white; }
        .deskripsi-pendek {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            color: #1b5e20;
        }
        .deskripsi-pendek:hover { text-decoration: underline; }
        .btn-aksi-group { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; }
        .gallery-img img { width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin: 2px; }
        .preview-img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin: 5px; }
        .gallery-preview { display: flex; flex-wrap: wrap; }
        
        .modal-detail {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }
        .modal-detail-content {
            background: white; border-radius: 16px; max-width: 500px; width: 90%;
            padding: 25px; position: relative;
        }
        .modal-detail-close {
            position: absolute; top: 15px; right: 20px; font-size: 24px;
            cursor: pointer; color: #999;
        }
        .modal-detail-close:hover { color: #333; }
        
        .btn-simpan, .btn-laporan {
            background-color: #0d6efd;
            border: none;
            color: white;
        }
        .btn-simpan:hover, .btn-laporan:hover {
            background-color: #0b5ed7;
            color: white;
        }
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
    <a href="kegiatan.php" class="active"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
    <a href="pengeluaran.php"><i class="fas fa-money-bill-wave"></i> Pengeluaran</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-calendar-alt"></i> Kelola Program Kegiatan</h2>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card-stats bg-kegiatan">
                <h6>Total Kegiatan</h6>
                <h3><?php echo $total_kegiatan; ?> Kegiatan</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stats bg-target">
                <h6>Total Target Dana</h6>
                <h3>Rp <?php echo number_format($total_target, 0, ',', '.'); ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stats bg-terkumpul">
                <h6>Total Terkumpul</h6>
                <h3>Rp <?php echo number_format($total_terkumpul, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header <?php echo $edit_data ? 'bg-warning' : 'bg-success'; ?> text-white">
            <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-plus'; ?>"></i>
            <?php echo $edit_data ? 'Edit Kegiatan' : 'Tambah Kegiatan Baru'; ?>
            <?php if ($edit_data): ?>
                <a href="kegiatan.php" class="btn btn-sm btn-light float-end">Batal Edit</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Kegiatan</label>
                    <input type="text" name="judul" class="form-control" placeholder="Masukkan judul kegiatan" value="<?php echo $edit_data ? htmlspecialchars($edit_data['judul']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Jenis Donasi</label>
                    <select name="jenis" class="form-select" required>
                        <option value="">-- Pilih Jenis Donasi --</option>
                        <option value="Sedekah" <?php echo ($edit_data && $edit_data['jenis'] == 'Sedekah') ? 'selected' : ''; ?>>Sedekah</option>
                        <option value="Infak" <?php echo ($edit_data && $edit_data['jenis'] == 'Infak') ? 'selected' : ''; ?>>Infak</option>
                        <option value="Zakat Maal" <?php echo ($edit_data && $edit_data['jenis'] == 'Zakat Maal') ? 'selected' : ''; ?>>Zakat Maal</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Jenis Program</label>
                    <select name="jenis_program" class="form-select" required>
                        <option value="">-- Pilih Jenis Program --</option>
                        <option value="Pendidikan" <?php echo ($edit_data && $edit_data['jenis_program'] == 'Pendidikan') ? 'selected' : ''; ?>>Pendidikan</option>
                        <option value="Dakwah" <?php echo ($edit_data && $edit_data['jenis_program'] == 'Dakwah') ? 'selected' : ''; ?>>Dakwah</option>
                        <option value="Ekonomi Sosial" <?php echo ($edit_data && $edit_data['jenis_program'] == 'Ekonomi Sosial') ? 'selected' : ''; ?>>Ekonomi Sosial</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Target Dana (Rp)</label>
                    <input type="number" name="target" class="form-control" placeholder="Masukkan target dana" value="<?php echo $edit_data ? $edit_data['target_dana'] : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Kegiatan</label>
                    <input type="date" name="tanggal_kegiatan" class="form-control" value="<?php echo $edit_data ? $edit_data['tanggal_kegiatan'] : date('Y-m-d'); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" class="form-control" rows="4" placeholder="Masukkan deskripsi kegiatan" required><?php echo $edit_data ? htmlspecialchars($edit_data['deskripsi']) : ''; ?></textarea>
                </div>
                
                <?php if ($edit_data): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif" <?php echo $edit_data['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="selesai" <?php echo $edit_data['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Gambar (Bisa pilih banyak)</label>
                    <input type="file" name="gambar<?php echo $edit_data ? '_baru' : ''; ?>[]" class="form-control" accept="image/*" multiple onchange="previewImages(event)">
                    <div class="gallery-preview" id="imagePreview"></div>
                    <small class="text-muted">Format: JPG, PNG, WEBP. Maksimal 2MB per file. Bisa pilih banyak gambar (Ctrl+klik).</small>
                </div>
                
                <?php if ($edit_data && !empty(json_decode($edit_data['gambar_url'], true))): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Klik gambar untuk menandai dihapus</label>
                    <div id="galleryLama">
                        <?php 
                        $gambar_lama_list = json_decode($edit_data['gambar_url'], true);
                        foreach ($gambar_lama_list as $gbr): 
                        ?>
                            <div style="display: inline-block; margin: 5px;">
                                <img src="../<?php echo $gbr; ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer;" onclick="toggleHapusGambar(this)">
                                <input type="hidden" name="hapus_gambar[]" value="<?php echo $gbr; ?>" class="hapus-gambar-input" disabled>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-simpan">
                        <i class="fas fa-save"></i> <?php echo $edit_data ? 'Update Kegiatan' : 'Simpan Kegiatan'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-success text-white">Daftar Kegiatan</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-success">
                        <tr>
                            <th width="40">No</th>
                            <th width="150">Judul</th>
                            <th width="120">Jenis Donasi</th>
                            <th width="120">Jenis Program</th>
                            <th width="100">Tanggal</th>
                            <th width="150">Deskripsi</th>
                            <th width="80">Gambar</th>
                            <th width="100">Target</th>
                            <th width="100">Terkumpul</th>
                            <th width="100">Sisa</th>
                            <th width="70">Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_kegiatan > 0): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($kegiatan as $k): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($k['judul']); ?></strong></td>
                                <td><?php echo htmlspecialchars($k['jenis']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($k['jenis_program']); ?></td>
                                <td><?php echo $k['tanggal_kegiatan'] ? date('d/m/Y', strtotime($k['tanggal_kegiatan'])) : '-'; ?></td>
                                <td>
                                    <div class="deskripsi-pendek" 
                                         data-judul="<?php echo htmlspecialchars($k['judul'], ENT_QUOTES); ?>" 
                                         data-deskripsi="<?php echo htmlspecialchars($k['deskripsi'], ENT_QUOTES); ?>"
                                         onclick="showDeskripsiModal(this)">
                                        <?php echo htmlspecialchars(substr($k['deskripsi'], 0, 60)); ?>...
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $gambar_list = json_decode($k['gambar_url'], true);
                                    if (!empty($gambar_list) && is_array($gambar_list)):
                                    ?>
                                        <div class="gallery-img">
                                            <?php foreach (array_slice($gambar_list, 0, 2) as $gbr): ?>
                                                <img src="../<?php echo $gbr; ?>" alt="Gambar">
                                            <?php endforeach; ?>
                                            <?php if (count($gambar_list) > 2): ?>
                                                <span class="badge bg-secondary">+<?php echo count($gambar_list) - 2; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">Rp <?php echo number_format($k['target_dana'], 0, ',', '.'); ?></td>
                                <td class="text-end">Rp <?php echo number_format($k['terkumpul'] ?? 0, 0, ',', '.'); ?></td>
                                <td class="text-end">
                                    <?php 
                                    $sisa = ($k['target_dana'] - ($k['terkumpul'] ?? 0));
                                    $warna_sisa = $sisa <= 0 ? '#28a745' : '#fd7e14';
                                    ?>
                                    <span style="color: <?php echo $warna_sisa; ?>; font-weight: bold;">
                                        Rp <?php echo number_format(max(0, $sisa), 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($k['status'] == 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-aksi-group">
                                        <a href="?edit=<?php echo $k['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <?php if ($k['status'] == 'aktif'): ?>
                                            <a href="?ubah_status=<?php echo $k['id']; ?>&status=selesai" class="btn btn-info btn-sm" onclick="return confirm('Ubah status menjadi Selesai?')">Selesai</a>
                                        <?php else: ?>
                                            <a href="?ubah_status=<?php echo $k['id']; ?>&status=aktif" class="btn btn-info btn-sm" onclick="return confirm('Aktifkan kembali?')">Aktifkan</a>
                                        <?php endif; ?>
                                        <a href="?hapus=<?php echo $k['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center py-4">Belum ada kegiatan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-3 text-center">
        <a href="laporan_keuangan.php" class="btn btn-laporan">
            <i class="fas fa-chart-line"></i> Lihat Laporan Keuangan
        </a>
    </div>
</div>

<div id="deskripsiModal" class="modal-detail" style="display: none;">
    <div class="modal-detail-content">
        <span class="modal-detail-close" onclick="closeModal()">&times;</span>
        <h4 id="modalJudul" style="color: #1b5e20;"></h4>
        <p id="modalDeskripsi" style="line-height: 1.6; margin-top: 15px;"></p>
    </div>
</div>

<script>
function previewImages(event) {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    var files = event.target.files;
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-img-thumb';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    }
}

function toggleHapusGambar(img) {
    var parent = img.parentElement;
    var hiddenInput = parent.querySelector('.hapus-gambar-input');
    if (hiddenInput.disabled) {
        hiddenInput.disabled = false;
        img.style.border = '3px solid red';
        img.style.opacity = '0.6';
    } else {
        hiddenInput.disabled = true;
        img.style.border = 'none';
        img.style.opacity = '1';
    }
}

function showDeskripsiModal(element) {
    var judul = element.getAttribute('data-judul');
    var deskripsi = element.getAttribute('data-deskripsi');
    document.getElementById('modalJudul').innerHTML = judul;
    document.getElementById('modalDeskripsi').innerHTML = deskripsi.replace(/\n/g, '<br>');
    document.getElementById('deskripsiModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('deskripsiModal').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('deskripsiModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>