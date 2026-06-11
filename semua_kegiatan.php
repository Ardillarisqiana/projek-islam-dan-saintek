<?php
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Semua Kegiatan - LAZISNU</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background-color: #fefef7; }
    .container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
    .navbar { background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 50; }
    .nav-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; flex-wrap: wrap; gap: 16px; }
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo img { height: 50px; width: auto; max-width: 180px; object-fit: contain; }
    .logo-text h1 { font-size: 1.7rem; font-weight: 800; color: var(--nu-green); letter-spacing: -0.3px; margin: 0; line-height: 1.2; color: #1b5e20; margin: 0; }
    .logo-text span { font-size: 0.75rem; font-weight: 500; color: var(--nu-gold); display: block; color: #c9a03d; }
    .nav-links { display: flex; gap: 2rem; align-items: center; }
    .nav-links a { text-decoration: none; font-weight: 600; color: #2d3e3a; }
    .btn-outline-donate { background: #1b5e20; color: white !important; padding: 8px 20px; border-radius: 40px; }
    .kegiatan-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; margin: 40px 0; }
    .card { background: white; border-radius: 28px; overflow: hidden; border: 1px solid #eef2ec; transition: all 0.3s ease; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    .card-img { width: 100%; height: 350px; object-fit: contain ; object-position:center; }
    .card-content { padding: 24px 20px; }
    .card-title { font-size: 1.4rem; font-weight: 700; color: #1f3d34; margin-bottom: 10px; }
    .card-desc { color: #4b5e58; margin-bottom: 20px; line-height: 1.6; }    .card-link { color: #1b5e20; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .card-link:hover { gap: 8px; }
    .copyright { text-align: center; padding-top: 40px; margin-top: 40px; border-top: 1px solid #2f4940; }
    .btn-back { background: #1b5e20; color: white; padding: 10px 25px; border-radius: 40px; text-decoration: none; display: inline-block; transition: 0.2s; }
    .btn-back:hover { background: #0f4814; }
    .badge-custom { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; margin-right: 5px; }
    .badge-donasi { background: #e8f5e9; color: #1b5e20; }
    .badge-program { background: #fff3e0; color: #ff9800; }
    .badge-selesai { background: #e0e0e0; color: #666; }

    :root {
    --nu-green: #1b5e20;
    --nu-green-light: #2e7d32;
    --nu-soft-green: #e8f5e9;
    --nu-gold: #c9a03d;
    --nu-cream: #fdfaf3;
    --nu-dark: #1f2f2a;
    --nu-gray: #4b5563;
}

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
    color: #c9a03d;  /* Warna emas langsung */
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
    color: #c9a03d;  /* Warna emas langsung */
}
.copyright {
    text-align: center;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 1px solid #2f4940;
    font-size: 0.9rem;
}
    @media (max-width: 768px) {
        .kegiatan-grid { grid-template-columns: 1fr; }
        .card-img { height: 220px; }
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

<!-- Hero Section -->
<section class="container" style="padding: 60px 0 40px;">
  <div style="text-align: center;">
    <h1 style="font-size: 2.5rem; color: #1b5e20; font-weight: 700;">Semua Program & Kegiatan</h1>
    <p style="color: #5f6c6a; max-width: 600px; margin: 20px auto;">Kumpulan semua program kegiatan LAZISNU dari waktu ke waktu</p>
    <a href="index.php#kegiatan" class="btn-back">← Kembali ke Beranda</a>
  </div>
</section>

<!-- Semua Kegiatan -->
<section class="container">
  <div class="kegiatan-grid">
    <?php 
    $stmt = $pdo->query("SELECT * FROM kegiatan ORDER BY id DESC");
    $semua_kegiatan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($semua_kegiatan) > 0):
      foreach ($semua_kegiatan as $k):
        $gambar_list = json_decode($k['gambar_url'] ?? '', true);
        $gambar_pertama = (!empty($gambar_list) && is_array($gambar_list)) ? $gambar_list[0] : '';
    ?>
      <div class="card">
        <?php if (!empty($gambar_pertama)): ?>
          <img class="card-img" src="../<?= $gambar_pertama ?>" alt="<?= htmlspecialchars($k['judul']) ?>">
        <?php else: ?>
          <div class="card-img" style="background: linear-gradient(135deg, #1b5e20, #2e7d32); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-calendar-alt fa-4x" style="color: white;"></i>
          </div>
        <?php endif; ?>
        <div class="card-content">
          <div class="card-title"><?= htmlspecialchars($k['judul']) ?></div>
          
          <div style="margin-bottom: 12px;">
            <?php if (!empty($k['jenis'])): ?>
              <span class="badge-custom badge-donasi"><i class="fas fa-tag"></i> <?= htmlspecialchars($k['jenis']) ?></span>
            <?php endif; ?>
            <?php if (!empty($k['jenis_program'])): ?>
              <span class="badge-custom badge-program"><i class="fas fa-folder"></i> <?= htmlspecialchars($k['jenis_program']) ?></span>
            <?php endif; ?>
            <?php if ($k['status'] == 'selesai'): ?>
              <span class="badge-custom badge-selesai"><i class="fas fa-check-circle"></i> Selesai</span>
            <?php endif; ?>
          </div>
          
          <?php if (!empty($k['tanggal_kegiatan'])): ?>
            <div style="font-size: 13px; color: #6c757d; margin-bottom: 12px;">
              <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($k['tanggal_kegiatan'])) ?>
            </div>
          <?php endif; ?>
          
          <div class="card-desc"><?= htmlspecialchars(substr($k['deskripsi'], 0, 120)) ?>...</div>
          
          <a href="index.php?kegiatan_id=<?= $k['id'] ?>#kegiatan" class="card-link">
            Selengkapnya <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    <?php 
      endforeach;
    else: 
    ?>
      <p style="text-align: center; padding: 40px;">Belum ada kegiatan</p>
    <?php endif; ?>
  </div>
</section>

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


</body>
</html>