<?php
require_once __DIR__ . '/config/database.php';

// Cek apakah sedang melihat detail kegiatan
$detail_kegiatan = null;
if (isset($_GET['kegiatan_id'])) {
    $id = (int)$_GET['kegiatan_id'];
    $stmt = $pdo->prepare("SELECT * FROM kegiatan WHERE id = ?");
    $stmt->execute([$id]);
    $detail_kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>LAZISNU | Lembaga Amil Zakat, Infak & Sedekah Nahdlatul Ulama</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background-color: #fefef7; color: #1e2a2e; scroll-behavior: smooth; line-height: 1.5; }
    :root {
      --nu-green: #1b5e20; --nu-green-light: #2e7d32; --nu-soft-green: #e8f5e9; --nu-gold: #c9a03d;
      --nu-cream: #fdfaf3; --nu-dark: #1f2f2a; --nu-gray: #4b5563;
      --shadow-sm: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
      --shadow-md: 0 20px 25px -12px rgba(0,0,0,0.08);
    }
    .container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
    .navbar { background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 50; border-bottom: 1px solid #e6e9e6; }
    .nav-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; flex-wrap: wrap; gap: 16px; }
    
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo img { height: 50px; width: auto; max-width: 180px; object-fit: contain; }
    .logo .logo-text h1 { font-size: 1.7rem; font-weight: 800; color: var(--nu-green); letter-spacing: -0.3px; margin: 0; line-height: 1.2; }
    .logo .logo-text span { font-size: 0.75rem; font-weight: 500; color: var(--nu-gold); display: block; }
    
    .nav-links { display: flex; gap: 2rem; align-items: center; flex-wrap: wrap; }
    .nav-links a { text-decoration: none; font-weight: 600; color: #2d3e3a; transition: 0.2s; font-size: 1rem; }
    .nav-links a:hover, .nav-links a.active { color: var(--nu-green-light); border-bottom: 2px solid var(--nu-gold); padding-bottom: 4px; }
    .btn-outline-donate { background: var(--nu-green); color: white !important; padding: 8px 20px; border-radius: 40px; border: none; transition: all 0.2s; }
    .btn-outline-donate:hover { background: #0f4814; transform: translateY(-2px); box-shadow: var(--shadow-sm); border-bottom: none !important; }
    .btn-primary { background-color: var(--nu-green); color: white; padding: 12px 32px; border-radius: 40px; font-weight: 600; border: none; cursor: pointer; display: inline-block; font-size: 1rem; text-decoration: none; }
    .btn-primary:hover { background-color: #0f4814; transform: translateY(-2px); box-shadow: var(--shadow-sm); }
    .btn-secondary { background: transparent; border: 2px solid var(--nu-green); color: var(--nu-green); padding: 10px 28px; border-radius: 40px; font-weight: 600; transition: 0.2s; cursor: pointer; display: inline-block; text-decoration: none; }
    .btn-secondary:hover { background: var(--nu-green); color: white; }
    .hero { background: linear-gradient(120deg, #f5f9f0 0%, #ffffff 100%); padding: 60px 0 70px; border-bottom: 1px solid #e2e8e6; }
    .hero-grid { display: flex; flex-wrap: wrap; align-items: center; gap: 40px; justify-content: space-between; }
    .hero-content { flex: 1; }
    .hero-badge { background: #e9f4e9; color: var(--nu-green); display: inline-block; padding: 5px 16px; border-radius: 40px; font-size: 0.85rem; font-weight: 600; margin-bottom: 20px; }
    .hero-content h1 { font-size: 3rem; font-weight: 800; line-height: 1.2; color: #1c3b2a; margin-bottom: 20px; }
    .hero-content p { font-size: 1.1rem; color: #2c423b; margin-bottom: 32px; max-width: 550px; }
    .hero-stats { display: flex; gap: 32px; margin-top: 32px; }
    .stat-number { font-size: 1.8rem; font-weight: 800; color: var(--nu-gold); }
    .hero-image { flex: 0.8; text-align: center; }
    .hero-image img { max-width: 100%; border-radius: 32px; box-shadow: var(--shadow-md); }
    .section-title { font-size: 2.2rem; font-weight: 700; text-align: center; margin-bottom: 12px; color: #1f3d34; }
    .section-sub { text-align: center; color: #5f6c6a; max-width: 650px; margin: 0 auto 48px auto; }
    .kegiatan-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; margin: 40px 0 20px; }
    .card { background: white; border-radius: 28px; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(0,0,0,0.02); border: 1px solid #eef2ec; }
    .card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px -12px rgba(27,94,32,0.12); border-color: #d9e3d4; }
    .card-img { width: 100%; height: 200px; object-fit: cover; }
    .card-content { padding: 24px 20px 28px; }
    .card-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 12px; color: #1f3d34; }
    .card-desc { color: #4b5e58; margin-bottom: 20px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .card-link { color: var(--nu-green); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    
    .detail-section { background: white; border-radius: 28px; overflow: hidden; box-shadow: var(--shadow-md); margin-top: 40px; }
    .detail-header { height: 300px; background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center; color: white; overflow: hidden; }
    .detail-header img { width: 100%; height: 100%; object-fit: cover; }
    .detail-body { padding: 40px; }
    .detail-title { font-size: 2rem; color: var(--nu-green); margin-bottom: 20px; }
    .detail-desc { font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px; color: #2c423b; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: var(--nu-green); text-decoration: none; }
    
    .donasi-section { background: var(--nu-cream); padding: 70px 0; margin-top: 40px; }
    .donasi-wrapper { display: flex; flex-wrap: wrap; gap: 40px; background: white; border-radius: 48px; padding: 40px; box-shadow: var(--shadow-sm); border: 1px solid #e0e9dd; }
    .donasi-form { flex: 1.4; }
    .donasi-info { flex: 1; background: #f9fcf7; border-radius: 32px; padding: 28px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { font-weight: 600; display: block; margin-bottom: 8px; color: #2d4a3c; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 14px 16px; border-radius: 28px; border: 1px solid #cfdfd3; background: white; font-family: 'Inter', sans-serif; }
    .donasi-nominal { display: flex; flex-wrap: wrap; gap: 12px; margin: 12px 0 8px; }
    .nominal-btn { background: #eff3ec; padding: 8px 18px; border-radius: 40px; font-weight: 500; cursor: pointer; border: 1px solid #dde5da; }
    .nominal-btn.active, .nominal-btn:hover { background: var(--nu-green); color: white; border-color: var(--nu-green); }
    .bank-item { display: flex; align-items: center; gap: 14px; background: white; padding: 12px 16px; border-radius: 60px; margin-bottom: 12px; border: 1px solid #e1eae3; }
    .info-zakat { margin-top: 20px; background: #eef4ea; padding: 18px; border-radius: 24px; }
    .alert-success { background: #dff0e2; color: #0f4814; padding: 14px; border-radius: 60px; margin-top: 20px; text-align: center; font-weight: 500; }
    .alert-danger { background: #ffebee; color: #b71c1c; padding: 14px; border-radius: 60px; margin-top: 20px; text-align: center; font-weight: 500; }
    .file-info { font-size: 12px; color: #6c757d; margin-top: 5px; }
    .preview-img { max-width: 100px; margin-top: 10px; border-radius: 8px; border: 1px solid #ddd; display: none; }
    .footer-grid { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 32px; }
    .copyright { text-align: center; padding-top: 40px; margin-top: 40px; border-top: 1px solid #2f4940; font-size: 0.85rem; }
    
    .badge-custom { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; margin-right: 5px; }
    .badge-donasi { background: #e8f5e9; color: #1b5e20; }
    .badge-program { background: #fff3e0; color: #ff9800; }

/* Footer Styles */
footer {
    background: #0f281f;
    color: #cfdfd3;
    padding: 48px 0 24px;
    margin-top: 60px;
}
.footer-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 32px;
    margin-bottom: 40px;
}
.footer-col {
    flex: 1;
    min-width: 200px;
}
.footer-col h3, .footer-col h4 {
    color: white;
    margin-bottom: 16px;
    font-size: 1.4rem;
}
.footer-col p {
    margin-bottom: 10px;
    line-height: 1.6;
    font-size: 1rem;
}
.footer-col i {
    margin-right: 8px;
    color: var(--nu-gold);
    font-size: 1rem;
}
.social-icons a {
    color: #cfdfd3;
    text-decoration: none;
    display: block;
    margin-bottom: 8px;
    font-size: 1rem;
    transition: 0.2s;
}
.social-icons a:hover {
    color: var(--nu-gold);
}
.copyright {
    text-align: center;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 1px solid #2f4940;
    font-size: 0.9rem;
}
    
/* ========== RESPONSIVE UNTUK HP ========== */
@media (max-width: 768px) {
    .container {
        padding: 0 16px;
    }
    .nav-wrapper {
        flex-direction: column;
        text-align: center;
    }
    .nav-links {
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .nav-links a {
        font-size: 0.85rem;
    }
    .btn-outline-donate {
        padding: 6px 15px;
        font-size: 0.8rem;
    }
    .hero-grid {
        flex-direction: column;
        text-align: center;
    }
    .hero-content h1 {
        font-size: 1.8rem;
    }
    .hero-content p {
        font-size: 0.95rem;
    }
    .hero-stats {
        justify-content: center;
        gap: 20px;
    }
    .stat-number {
        font-size: 1.3rem;
    }
    .hero-image {
        order: -1;
    }
    .hero-image img {
        max-width: 90%;
    }
    .kegiatan-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .card-img {
        height: 200px;
    }
    .card-title {
        font-size: 1.2rem;
    }
    .card-desc {
        font-size: 0.85rem;
    }
    .detail-header {
        height: 220px;
    }
    .detail-body {
        padding: 20px;
    }
    .detail-title {
        font-size: 1.4rem;
    }
    .detail-desc {
        font-size: 0.95rem;
    }
    .badge-custom {
        font-size: 10px;
        padding: 3px 8px;
    }
    .donasi-wrapper {
        flex-direction: column;
        padding: 20px;
        gap: 20px;
    }
    .donasi-form h3 {
        font-size: 1.3rem !important;
    }
    .form-group label {
        font-size: 0.85rem;
    }
    .form-group input, 
    .form-group select, 
    .form-group textarea {
        padding: 10px 12px;
        font-size: 0.85rem;
    }
    .donasi-nominal {
        gap: 8px;
    }
    .nominal-btn {
        padding: 5px 12px;
        font-size: 0.75rem;
    }
    .bank-item {
        padding: 8px 12px;
    }
    .bank-item i {
        font-size: 1.2rem;
    }
    .info-zakat {
        font-size: 0.85rem;
    }
    #tentang > div {
        flex-direction: column;
        text-align: center;
    }
    #tentang .section-title {
        text-align: center !important;
    }
    .footer-grid {
        flex-direction: column;
        gap: 25px;
        text-align: center;
    }
    .footer-col {
        min-width: 100%;
    }
    .footer-col h3, .footer-col h4 {
        font-size: 1.2rem;
    }
    .footer-col p {
        font-size: 0.85rem;
    }
    .social-icons a {
        display: inline-block;
        margin: 0 8px;
        font-size: 0.85rem;
    }
    .copyright {
        font-size: 0.7rem;
    }
    .btn-primary, .btn-secondary {
        padding: 8px 20px;
        font-size: 0.85rem;
    }
    .btn-back {
        padding: 8px 20px;
        font-size: 0.85rem;
    }
    .section-title {
        font-size: 1.5rem;
    }
    .section-sub {
        font-size: 0.85rem;
        margin-bottom: 30px;
    }
}

  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="container nav-wrapper">
    <div class="logo">
      <img src="foto/logo.png" alt="LAZISNU">
      <div class="logo-text">
        <h1>LAZISNU</h1>
        <span>Bojongwetan</span>
      </div>
    </div>
    <div class="nav-links">
      <a href="#beranda">Beranda</a>
      <a href="#kegiatan">Kegiatan</a>
      <a href="#donasi">Sedekah & Zakat</a>
      <a href="#tentang">Tentang</a>
      <a href="#donasi" class="btn-outline-donate"><i class="fas fa-hand-holding-heart"></i> Salurkan Sekarang</a>
    </div>
  </div>
</nav>

<?php if ($detail_kegiatan): ?>
<!-- HALAMAN DETAIL KEGIATAN (Tanpa Status, Target, Terkumpul) -->
<section class="container" style="padding: 40px 0;">
  <a href="index.php#kegiatan" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Program Kegiatan</a>
  
  <div class="detail-section">
    <div class="detail-header">
      <?php 
      $gambar_list = json_decode($detail_kegiatan['gambar_url'] ?? '', true);
      $gambar_pertama = (!empty($gambar_list) && is_array($gambar_list)) ? $gambar_list[0] : '';
      ?>
      <?php if (!empty($gambar_pertama) && file_exists($gambar_pertama)): ?>
        <img src="<?= $gambar_pertama ?>" alt="<?= htmlspecialchars($detail_kegiatan['judul']) ?>">
      <?php else: ?>
        <i class="fas fa-calendar-alt" style="font-size: 5rem;"></i>
      <?php endif; ?>
    </div>
    <div class="detail-body">
      <h1 class="detail-title"><?= htmlspecialchars($detail_kegiatan['judul']) ?></h1>
      
      <!-- Badge Jenis Donasi & Jenis Program -->
      <div style="margin-bottom: 15px;">
        <?php if (!empty($detail_kegiatan['jenis'])): ?>
          <span class="badge-custom badge-donasi"><i class="fas fa-tag"></i> <?= htmlspecialchars($detail_kegiatan['jenis']) ?></span>
        <?php endif; ?>
        <?php if (!empty($detail_kegiatan['jenis_program'])): ?>
          <span class="badge-custom badge-program"><i class="fas fa-folder"></i> <?= htmlspecialchars($detail_kegiatan['jenis_program']) ?></span>
        <?php endif; ?>
      </div>
      
      <!-- Tanggal Kegiatan -->
      <?php if (!empty($detail_kegiatan['tanggal_kegiatan'])): ?>
        <div style="margin-bottom: 20px; font-size: 14px; color: #6c757d;">
          <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($detail_kegiatan['tanggal_kegiatan'])) ?>
        </div>
      <?php endif; ?>
      
      <!-- Deskripsi Lengkap -->
      <div class="detail-desc">
        <?= nl2br(htmlspecialchars($detail_kegiatan['deskripsi'])) ?>
      </div>
      
      <!-- Tombol Salurkan -->
      <a href="#donasi" class="btn-primary"><i class="fas fa-hand-holding-heart"></i> Salurkan untuk Program Ini</a>
    </div>
  </div>
</section>

<?php else: ?>
<!-- HERO SECTION -->
<section id="beranda" class="hero">
  <div class="container hero-grid">
    <div class="hero-content">
      <h1>Salurkan Zakat, Infak & Sedekah <span style="color: #1b5e20;">Bersama LAZISNU</span></h1>
      <p>Membangun kemandirian umat, memberdayakan dhuafa, dan menebar manfaat untuk Indonesia yang lebih berkah.</p>
      <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <a href="#donasi" class="btn-primary"><i class="fas fa-hand-holding-usd"></i> Bayar Zakat / Sedekah</a>
        <a href="#kegiatan" class="btn-secondary"><i class="fas fa-chalkboard-user"></i> Lihat Kegiatan</a>
      </div>
    </div>
    <div class="hero-image"><img src="foto/foto1.jpeg" alt="Kegiatan sosial LAZISNU" style="border-radius: 32px; width: 100%; object-fit: cover;"></div>
  </div>
</section>

<!-- KEGIATAN SECTION -->
<section id="kegiatan" class="container" style="padding: 80px 0 60px;">
  <div class="section-title">Program & Kegiatan Unggulan</div>
  <div class="section-sub">Gerakan nyata untuk kemanusiaan dan pemberdayaan umat berbasis masjid & pesantren</div>
  <div class="kegiatan-grid">
    <?php 
    // Hanya ambil 4 kegiatan terbaru yang aktif
    $stmt_keg = $pdo->query("SELECT * FROM kegiatan WHERE status = 'aktif' ORDER BY id DESC LIMIT 4");
    $kegiatan_list = $stmt_keg->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($kegiatan_list) > 0):
      foreach ($kegiatan_list as $k):
        $gambar_list = json_decode($k['gambar_url'] ?? '', true);
        $gambar_pertama = (!empty($gambar_list) && is_array($gambar_list)) ? $gambar_list[0] : '';
    ?>
    <!-- ... sisanya sama ... -->
<div class="card">
  <?php if (!empty($gambar_pertama)): ?>
    <img class="card-img" src="/lazisnu/<?= $gambar_pertama ?>" alt="<?= htmlspecialchars($k['judul']) ?>">
  <?php else: ?>
    <div class="card-img" style="background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center;">
      <i class="fas fa-calendar-alt fa-3x" style="color: white;"></i>
    </div>
  <?php endif; ?>
  <div class="card-content">
    <div class="card-title"><?= htmlspecialchars($k['judul']) ?></div>
    
    <div style="margin-bottom: 10px;">
      <?php if (!empty($k['jenis'])): ?>
        <span style="background: #e8f5e9; color: #1b5e20; padding: 4px 10px; border-radius: 20px; font-size: 11px; display: inline-block; margin-right: 5px;">
          <i class="fas fa-tag"></i> <?= htmlspecialchars($k['jenis']) ?>
        </span>
      <?php endif; ?>
      <?php if (!empty($k['jenis_program'])): ?>
        <span style="background: #fff3e0; color: #ff9800; padding: 4px 10px; border-radius: 20px; font-size: 11px; display: inline-block;">
          <i class="fas fa-folder"></i> <?= htmlspecialchars($k['jenis_program']) ?>
        </span>
      <?php endif; ?>
    </div>
    
    <?php if (!empty($k['tanggal_kegiatan'])): ?>
      <div style="font-size: 12px; color: #6c757d; margin-bottom: 10px;">
        <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($k['tanggal_kegiatan'])) ?>
      </div>
    <?php endif; ?>
    
    <div class="card-desc"><?= htmlspecialchars(substr($k['deskripsi'], 0, 100)) ?>...</div>
    
    <a href="?kegiatan_id=<?= $k['id'] ?>#kegiatan" class="card-link">
      Selengkapnya <i class="fas fa-arrow-right"></i>
    </a>
  </div>
</div>
    <?php 
      endforeach;
    else: 
    ?>
      <div class="card"><div class="card-img" style="background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center;"><i class="fas fa-hands-helping fa-3x" style="color: white;"></i></div><div class="card-content"><div class="card-title">Santunan Yatim & Dhuafa</div><div class="card-desc">Program rutin setiap bulan dengan santunan tunai, paket sembako, dan bimbingan rohani.</div><a href="#donasi" class="card-link">Donasi <i class="fas fa-arrow-right"></i></a></div></div>
      <div class="card"><div class="card-img" style="background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center;"><i class="fas fa-hand-holding-heart fa-3x" style="color: white;"></i></div><div class="card-content"><div class="card-title">Siaga Bencana & Kemanusiaan</div><div class="card-desc">Respon cepat untuk korban banjir, gempa, distribusi logistik & layanan kesehatan darurat.</div><a href="#donasi" class="card-link">Donasi <i class="fas fa-arrow-right"></i></a></div></div>
      <div class="card"><div class="card-img" style="background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center;"><i class="fas fa-graduation-cap fa-3x" style="color: white;"></i></div><div class="card-content"><div class="card-title">Beasiswa NU Cendekia</div><div class="card-desc">Beasiswa penuh untuk santri berprestasi dan mahasiswa kurang mampu dari lingkungan NU.</div><a href="#donasi" class="card-link">Donasi <i class="fas fa-arrow-right"></i></a></div></div>
      <div class="card"><div class="card-img" style="background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center;"><i class="fas fa-hospital-user fa-3x" style="color: white;"></i></div><div class="card-content"><div class="card-title">Layanan Kesehatan Gratis</div><div class="card-desc">Klinik keliling, operasi katarak gratis, dan layanan ibu & anak di daerah terpencil.</div><a href="#donasi" class="card-link">Donasi <i class="fas fa-arrow-right"></i></a></div></div>
    <?php endif; ?>
  </div>
  <div style="text-align: center; margin-top: 20px;">
  <a href="semua_kegiatan.php" class="btn-primary" style="padding: 10px 30px;">Lihat Semua Kegiatan →</a>
</div>
</section>

<!-- DONASI SECTION -->
<section id="donasi" class="donasi-section">
  <div class="container">
    <div class="section-title">Tunaikan Zakat & Sedekah Sekarang</div>
    <div class="section-sub">Setiap rupiah yang Anda berikan menjadi amanah untuk kemaslahatan umat</div>
    <div class="donasi-wrapper">
      <div class="donasi-form">
        <h3 style="font-size: 1.8rem;"><i class="fas fa-file-invoice-dollar"></i> Form Sedekah</h3>
        <form id="donationForm" enctype="multipart/form-data">
          <div class="form-group"><label>Nama Lengkap *</label><input type="text" id="namaDonatur" required></div>
          <div class="form-group"><label>Nomor HP / WhatsApp *</label><input type="tel" id="noHp" placeholder="08xxxxxxxxxx" required></div>
          <div class="form-group"><label>Jenis *</label><select id="jenisDonasi"><option>Sedekah</option><option>Infak</option><option>Zakat Maal</option></select></div>
          <div class="form-group"><label>Program Pilihan</label><select id="programDonasi"><option>Umum (Prioritas)</option><option>Santunan Yatim</option><option>Beasiswa Pendidikan</option><option>Kemanusiaan & Bencana</option></select></div>
          <div class="form-group">
            <label>Nominal (Rp) *</label>
            <div class="donasi-nominal" id="nominalOptions">
              <span class="nominal-btn" data-nom="50000">Rp 50.000</span>
              <span class="nominal-btn" data-nom="100000">Rp 100.000</span>
              <span class="nominal-btn" data-nom="250000">Rp 250.000</span>
              <span class="nominal-btn" data-nom="500000">Rp 500.000</span>
              <span class="nominal-btn" data-nom="custom">Custom</span>
            </div>
            <input type="number" id="nominalCustom" placeholder="Masukkan nominal (Rp)" style="margin-top: 12px; display: none;">
            <input type="hidden" id="selectedNominal" value="">
          </div>
          <div class="form-group">
            <label>Upload Bukti Pembayaran *</label>
            <input type="file" id="buktiBayar" accept="image/*,application/pdf" required>
            <div class="file-info">Format: JPG, PNG, PDF (Max 2MB)</div>
            <img id="preview" class="preview-img" alt="Preview bukti">
          </div>
          <div class="form-group"><label>Catatan (opsional)</label><textarea rows="2" id="catatanDonasi" placeholder="Doa / pesan untuk penerima manfaat"></textarea></div>
          <button type="submit" class="btn-primary" style="width:100%"><i class="fas fa-check-circle"></i> Kirim </button>
          <div id="formFeedback" style="margin-top: 16px;"></div>
        </form>
      </div>
      <div class="donasi-info">
        <h3><i class="fas fa-university"></i> Rekening Resmi LAZISNU</h3>
        <div class="bank-list">
          <div class="bank-item"><i class="fas fa-landmark"></i><div><strong>Bank Jateng</strong><br>3109157812 a.n. LAZISNU Pusat</div></div>
          <div class="bank-item"><i class="fas fa-landmark"></i><div><strong>Bank Rakyat Indonesia (BRI)</strong><br>596801000776507 a.n. LAZISNU</div></div>
        </div>
        <div class="info-zakat"><i class="fas fa-calculator"></i><strong> Zakat Maal:</strong> 2,5% dari harta yang telah mencapai nisab.<br><small>Konsultasi: 0823-1154-6909</small></div>
        <div><i class="fas fa-shield-alt"></i> Sedekah Anda 100% amanah & transparan.</div>
        <div class="mt-3"><i class="fas fa-credit-card"></i> Transfer ke rekening di atas, lalu upload bukti pembayaran.</div>
      </div>
    </div>
  </div>
</section>

<!-- TENTANG SECTION -->
<section id="tentang" class="container" style="padding: 60px 0;">
  <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center;">
    <div style="flex:1"><img src="foto/logo.png" style="border-radius: 40px; width:100%;"></div>
    <div style="flex:1"><div class="section-title" style="text-align: left;">Tentang LAZISNU Bojongwetan</div><p>LAZISNU Bojongwetan adalah lembaga yang menghimpun, mengelola, dan menyalurkan dana zakat, infak, sedekah, serta dana sosial keagamaan lainnya untuk membantu masyarakat yang membutuhkan. Berlandaskan nilai-nilai Islam Ahlussunnah wal Jamaah, LAZISNU Bojongwetan berkomitmen menumbuhkan kepedulian sosial, semangat berbagi, dan pemberdayaan umat.</p><div><i class="fas fa-check-circle"></i> Memiliki Visi Menjadi lembaga pengelola Zakat, Infak, dan Sedekah yang amanah, transparan, dan bermanfaat bagi masyarakat.
    </div><div><i class="fas fa-check-circle"></i> Memiliki MisiMenghimpun dan menyalurkan dana ZIS secara tepat sasaran, membantu masyarakat yang membutuhkan, serta mendukung kegiatan sosial, pendidikan, dan keagamaan.</div></div>
  </div>
</section>

<?php endif; ?>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <h3>LAZISNU <i class="fas fa-hands-helping"></i></h3>
        <p>Menebar Berkah, Membangun Peradaban</p>
        <div class="social-icons">
          <a href="#" style="color: #cfdfd3; text-decoration: none; display: block; margin-bottom: 8px;">
            <i class="fab fa-facebook" style="margin-right: 8px;"></i> Facebook: @Lazisnu Bojongwetan
          </a>
          <a href="#" style="color: #cfdfd3; text-decoration: none; display: block; margin-bottom: 8px;">
            <i class="fab fa-instagram" style="margin-right: 8px;"></i> Instagram: @lazisnu_bojongwetan
          </a>
          <a href="#" style="color: #cfdfd3; text-decoration: none; display: block;">
            <i class="fab fa-youtube" style="margin-right: 8px;"></i> YouTube: Lazisnu Bojongwetan
          </a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Kontak Kami</h4>
        <p><i class="fas fa-map-marker-alt"></i> Desa Bojongwetan, <br>Gang Sekar Arum RT 03 RW 02 NO 181 Kecamatan Bojong, Kabupaten Pekalongan, Jawa Tengah</p>
        <p><i class="fas fa-phone-alt"></i>0823-1154-6909</p>
      </div>
      <div class="footer-col">
        <h4>Layanan 24 Jam</h4>
        <p><i class="fas fa-mosque"></i> Konsultasi Zakat & Sedekah</p>
        <p>Call Center: 0823-1154-6909</p>
      </div>
    </div>
    <div class="copyright">
      <p>&copy; 2025 LAZISNU Bojongwetan - Lembaga Amil Zakat Infaq Sedekah Nahdlatul Ulama</p>
    </div>
  </div>
</footer>

<script>
  const nominalBtns = document.querySelectorAll('.nominal-btn');
  const customInput = document.getElementById('nominalCustom');
  const selectedNominalHidden = document.getElementById('selectedNominal');
  function resetActive() { nominalBtns.forEach(btn => btn.classList.remove('active')); }
  nominalBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const val = this.getAttribute('data-nom');
      if (val === 'custom') {
        customInput.style.display = 'block';
        customInput.value = '';
        selectedNominalHidden.value = '';
        resetActive();
        this.classList.add('active');
      } else {
        customInput.style.display = 'none';
        resetActive();
        this.classList.add('active');
        selectedNominalHidden.value = val;
      }
    });
  });
  customInput.addEventListener('input', function() {
    selectedNominalHidden.value = customInput.value || '';
  });

  const buktiInput = document.getElementById('buktiBayar');
  const preview = document.getElementById('preview');
  if (buktiInput) {
    buktiInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.style.display = 'block';
          preview.src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
      } else {
        preview.style.display = 'none';
      }
    });
  }

  const form = document.getElementById('donationForm');
  const feedbackDiv = document.getElementById('formFeedback');
  
  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const nama = document.getElementById('namaDonatur').value.trim();
      const noHp = document.getElementById('noHp').value.trim();
      const jenis = document.getElementById('jenisDonasi').value;
      const program = document.getElementById('programDonasi').value;
      let nominal = selectedNominalHidden.value;
      const catatan = document.getElementById('catatanDonasi').value;
      const buktiFile = document.getElementById('buktiBayar').files[0];

      if (!nama) {
        feedbackDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-exclamation-circle"></i> Nama lengkap wajib diisi.</div>';
        return;
      }
      if (!noHp) {
        feedbackDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-exclamation-circle"></i> Nomor HP wajib diisi.</div>';
        return;
      }
      if (!buktiFile) {
        feedbackDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-exclamation-circle"></i> Upload bukti pembayaran wajib diisi.</div>';
        return;
      }
      if (!nominal || nominal <= 0) {
        if (customInput.style.display === 'block' && customInput.value) {
          nominal = customInput.value;
          if (nominal <= 0 || isNaN(nominal)) {
            feedbackDiv.innerHTML = '<div class="alert-danger">Masukkan nominal valid.</div>';
            return;
          }
        } else {
          feedbackDiv.innerHTML = '<div class="alert-danger">Pilih atau isi nominal donasi.</div>';
          return;
        }
      }

      const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
      if (!allowedTypes.includes(buktiFile.type)) {
        feedbackDiv.innerHTML = '<div class="alert-danger">Format file harus JPG, PNG, atau PDF.</div>';
        return;
      }
      if (buktiFile.size > 2 * 1024 * 1024) {
        feedbackDiv.innerHTML = '<div class="alert-danger">Ukuran file maksimal 2MB.</div>';
        return;
      }

      const formData = new FormData();
      formData.append('nama', nama);
      formData.append('no_hp', noHp);
      formData.append('jenis_donasi', jenis);
      formData.append('program', program);
      formData.append('nominal', nominal);
      formData.append('catatan', catatan);
      formData.append('bukti', buktiFile);

      try {
        const response = await fetch('api/simpan_donasi.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();
        if (data.status === 'success') {
          feedbackDiv.innerHTML = `<div class="alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
          form.reset();
          customInput.style.display = 'none';
          resetActive();
          preview.style.display = 'none';
          document.querySelector('.nominal-btn[data-nom="50000"]').click();
        } else {
          feedbackDiv.innerHTML = `<div class="alert-danger">${data.message}</div>`;
        }
      } catch (error) {
        feedbackDiv.innerHTML = '<div class="alert-danger">Gagal terhubung ke server. Pastikan server berjalan.</div>';
      }
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      if (targetId === "#" || targetId === "") return;
      const target = document.querySelector(targetId);
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
  });
  
  window.addEventListener('load', () => { 
    const btn = document.querySelector('.nominal-btn[data-nom="50000"]');
    if (btn) btn.click(); 
  });
</script>
</body>
</html>