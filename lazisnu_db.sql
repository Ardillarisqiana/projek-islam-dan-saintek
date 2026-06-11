-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Jun 2026 pada 03.56
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lazisnu_db`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_donasi` (IN `p_nama` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_jenis` ENUM('Zakat Maal','Zakat Fitrah','Infak','Sedekah'), IN `p_program` VARCHAR(100), IN `p_nominal` DECIMAL(15,2), IN `p_catatan` TEXT)   BEGIN
    INSERT INTO donasi (nama_donatur, email, jenis_donasi, program, nominal, catatan, status)
    VALUES (p_nama, p_email, p_jenis, p_program, p_nominal, p_catatan, 'success');
    
    -- Update program terkumpul jika program spesifik
    IF p_program != 'Umum (Prioritas)' THEN
        UPDATE program 
        SET terkumpul = terkumpul + p_nominal 
        WHERE nama_program = p_program;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `donasi`
--

CREATE TABLE `donasi` (
  `id` int(11) NOT NULL,
  `nama_donatur` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_donasi` enum('Zakat Maal','Zakat Fitrah','Infak','Sedekah') NOT NULL,
  `program` varchar(100) DEFAULT 'Umum (Prioritas)',
  `nominal` decimal(15,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `donasi`
--

INSERT INTO `donasi` (`id`, `nama_donatur`, `no_hp`, `email`, `jenis_donasi`, `program`, `nominal`, `catatan`, `bukti_pembayaran`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ana', NULL, 'ana@gmail.com', 'Sedekah', 'Umum (Prioritas)', 500000.00, '', NULL, 'success', '2026-05-14 03:31:58', '2026-06-03 04:16:46'),
(2, 'dina', NULL, 'dina@gmai.com', 'Zakat Maal', 'Santunan Yatim', 500000.00, '', NULL, 'success', '2026-05-14 03:51:08', '2026-05-14 04:09:16'),
(3, 'ali', NULL, '', 'Infak', 'Umum (Prioritas)', 100000.00, '', NULL, 'success', '2026-05-14 04:37:16', '2026-05-14 06:58:04'),
(4, 'alin', NULL, '', 'Sedekah', 'Santunan Yatim', 100000.00, '', NULL, 'success', '2026-05-14 04:47:24', '2026-05-14 07:14:39'),
(5, 'alin', NULL, 'test@gmail.com', 'Zakat Maal', 'Umum (Prioritas)', 50000.00, 'xss\"><img src=x onerror=alert(1)>', NULL, 'success', '2026-05-14 05:39:51', '2026-05-14 14:49:58'),
(6, 'ai', NULL, '', 'Zakat Maal', 'Umum (Prioritas)', 250000.00, '', NULL, 'success', '2026-05-14 14:37:00', '2026-05-14 14:50:03'),
(7, 'mingo', '088899990001', NULL, 'Zakat Maal', 'Umum (Prioritas)', 500000.00, '', 'uploads/1778769573_6a05dea5f247e.png', 'success', '2026-05-14 14:39:33', '2026-05-14 14:50:07'),
(8, 'manusia', '088899990000', NULL, 'Infak', 'Umum (Prioritas)', 100000.00, '', 'uploads/1780360250_6a1e243ae54d8.jpeg', 'success', '2026-06-02 00:30:50', '2026-06-02 00:30:59');

--
-- Trigger `donasi`
--
DELIMITER $$
CREATE TRIGGER `trg_update_program` AFTER UPDATE ON `donasi` FOR EACH ROW BEGIN
    IF NEW.status = 'success' AND OLD.status != 'success' THEN
        IF NEW.program != 'Umum (Prioritas)' THEN
            UPDATE program 
            SET terkumpul = terkumpul + NEW.nominal 
            WHERE nama_program = NEW.program;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `jenis_program` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar_url` text DEFAULT NULL,
  `target_dana` decimal(15,2) DEFAULT NULL,
  `tanggal_kegiatan` date DEFAULT curdate(),
  `terkumpul` decimal(15,2) DEFAULT 0.00,
  `status` enum('aktif','selesai') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `judul`, `jenis`, `jenis_program`, `deskripsi`, `gambar_url`, `target_dana`, `tanggal_kegiatan`, `terkumpul`, `status`) VALUES
(4, 'Kewajiban Membayar Zakat Fitrah', 'Zakat Maal', 'Ekonomi Sosial', '\r\nزَكَاةَ الْفِطْرِأِذَا غَرُبَتْ شَمْسُ آخِرِرَمَضَانَ وَكَانَ لَكَ سَاعَةٌ فَأَدِّ زَكَاةَ الْفِطْرِصَاعًا مِنْ طَعَامِكَ قَبْلَ الصَّلَاةِ طُهْرَةً لِصَوْمِكَ طُعْمَةً لِلْمَسَاكِيْنَ\r\nبَادِرْ بِصَرْفِ زَكَاتِكَ عَلَى مُسْتَحِقِّيْهَاالثَّمَانِيَةِ ……..  إِلَّا زَكَاةَ الْفِطْرفَاصْرِفْهَا عَلَى الْفُقَرَاءِ وَالْمَسَكِيْنِ وَلَا بَأْسَ أَنْ تُعَجِّلَ بِأِخرَاجِهَاقَبْلَ أَوَانِهَا\r\n\r\nZakat Fitri. Apabila terbenam matahari pada akhir Ramadhan, sedang kamu berkelapangan rezeki, maka keluarkanlah zakat fitrah sebanyak satu sha’ dari bahan makananmu sebelum shalat Id, untuk membersihkan puasamu dan untuk makanan orang-orang miskin.\r\n\r\nSegerakanlah keluarkan zakatmu kepada delapan golongan yang berhak menerimanya … adapun zakat fitrah, bagikanlah kepada orang-orang fakir dan miskin. Zakat itu boleh kamu keluarkan sebelum waktunya.', '[\"uploads_kegiatan\\/1780980352_6a279a8058649_0.jpeg\"]', 0.00, '2026-06-09', 0.00, 'aktif'),
(5, 'Hadist Tentang Berlomba-lomba dalam sedekah', 'Sedekah', 'Ekonomi Sosial', 'مَنْ تَصَدَّقَ بِعَدْلِ تَمْرَةٍ مِنْ كَسْبٍ طَيِّبٍ، فَإِنَّ اللَّهَ يَقْبَلُهَا بِيَمِينِهِ، ثُمَّ يُرَبِّيهَا لِصَاحِبِهَا\r\n\r\n“Barangsiapa bersedekah dengan sebiji kurma dari harta halal, maka Allah akan menerimanya, lalu mengembangkannya seperti seseorang memelihara anak kudanya.” (HR Bukhari, Muslim)', '[\"uploads_kegiatan\\/1780981888_6a27a0802ec42_0.jpeg\"]', 0.00, '2026-06-09', 600000.00, 'aktif'),
(6, 'Pengajian Pagi Kitab Lubabul Hadits di Masjid Al Karomah Bojongwetan', 'Sedekah', 'Ekonomi Sosial', 'Dalam upaya meningkatkan pemahaman ilmu agama dan mempererat ukhuwah Islamiyah di tengah masyarakat, Masjid Al Karomah Desa Bojongwetan secara rutin menyelenggarakan pengajian pagi Kitab Lubabul Hadits yang dilaksanakan setiap hari Jumat ba\'da Subuh.\r\n\r\nKegiatan ini diikuti oleh jamaah dari berbagai kalangan, khususnya ibu-ibu dan masyarakat sekitar yang memiliki semangat untuk menambah wawasan keislaman. Dengan suasana yang tenang dan penuh kekhusyukan di waktu pagi, para peserta mengikuti kajian kitab yang berisi kumpulan hadits dan nasihat tentang akhlak, ibadah, keutamaan amal, serta tuntunan kehidupan sehari-hari.\r\nPengajian Kitab Lubabul Hadits menjadi sarana yang bermanfaat untuk memperdalam ilmu agama sekaligus mengamalkan nilai-nilai Islam dalam kehidupan bermasyarakat. Selain menambah pengetahuan, kegiatan ini juga menjadi ajang silaturahmi yang mempererat hubungan antarjamaah.\r\n\r\nMelalui kegiatan rutin ini, diharapkan masyarakat Desa Bojongwetan semakin termotivasi untuk mencintai ilmu, memperbaiki akhlak, serta meningkatkan kualitas ibadah kepada Allah SWT. Semoga pengajian yang dilaksanakan setiap Jumat ba\'da Subuh di Masjid Al Karomah ini terus istiqamah dan membawa keberkahan bagi seluruh jamaah serta masyarakat sekitar. Aamiin.', '[\"uploads_kegiatan\\/1780982077_6a27a13ddcc4a_0.jpeg\"]', 0.00, '2026-06-09', 0.00, 'aktif'),
(8, 'Semangat Berkurban, Menebar Kepedulian di Hari Raya Iduladha 2026', 'Sedekah', 'Ekonomi Sosial', 'Dalam rangka memperingati Hari Raya Iduladha 1447 H/2026 M, LAZISNU Bojongwetan melaksanakan kegiatan penyembelihan dan penyaluran hewan kurban sebagai wujud kepedulian sosial dan pengamalan nilai-nilai keislaman.\r\n\r\nTahun ini, amanah dari para mudhohi/shohibul qurban berhasil dihimpun sebanyak 6 ekor sapi dan 11 ekor kambing, yang berasal dari partisipasi masyarakat dan para donatur. Hewan kurban tersebut kemudian disalurkan kepada masyarakat yang berhak menerima sehingga manfaatnya dapat dirasakan secara luas.\r\n\r\nKegiatan kurban tidak hanya menjadi bentuk ketaatan kepada Allah SWT, tetapi juga sarana mempererat silaturahmi, menumbuhkan semangat berbagi, dan memperkuat kebersamaan di tengah masyarakat.\r\nLAZISNU Bojongwetan mengucapkan terima kasih kepada seluruh shohibul qurban, panitia, dan relawan yang telah berkontribusi dalam menyukseskan kegiatan ini. Semoga amal ibadah kurban yang telah ditunaikan diterima oleh Allah SWT dan menjadi keberkahan bagi semua pihak.\r\n\r\nJazakumullahu Khairan Katsiran. Selamat Hari Raya Iduladha 2026. Semoga semangat kurban terus menginspirasi kita untuk berbagi dan peduli kepada sesama.', '[\"uploads_kegiatan\\/1780987789_6a27b78d0c50b_0.jpeg\"]', 0.00, '2026-05-27', 0.00, 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_keuangan`
--

CREATE TABLE `laporan_keuangan` (
  `periode` varchar(20) NOT NULL,
  `total_pemasukan` decimal(15,2) DEFAULT 0.00,
  `total_penyaluran` decimal(15,2) DEFAULT 0.00,
  `saldo_akhir` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `kategori` varchar(50) DEFAULT 'Program Sosial',
  `tanggal_keluar` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `judul`, `deskripsi`, `jumlah`, `kategori`, `tanggal_keluar`, `created_at`) VALUES
(1, 'donasi bencana', '', 1500000.00, 'Bencana', '2026-05-14', '2026-05-14 14:50:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `program`
--

CREATE TABLE `program` (
  `id_program` int(11) NOT NULL,
  `nama_program` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `target_dana` decimal(15,2) DEFAULT NULL,
  `terkumpul` decimal(15,2) DEFAULT 0.00,
  `status` enum('aktif','selesai','ditutup') DEFAULT 'aktif',
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `program`
--

INSERT INTO `program` (`id_program`, `nama_program`, `deskripsi`, `target_dana`, `terkumpul`, `status`, `gambar`, `created_at`) VALUES
(1, 'Santunan Yatim & Dhuafa', 'Program rutin setiap bulan dengan santunan tunai, paket sembako, dan bimbingan rohani', 100000000.00, 0.00, 'aktif', NULL, '2026-04-18 06:08:29'),
(2, 'Beasiswa NU Cendekia', 'Beasiswa penuh untuk santri berprestasi dan mahasiswa kurang mampu dari lingkungan NU', 250000000.00, 0.00, 'aktif', NULL, '2026-04-18 06:08:29'),
(3, 'Siaga Bencana & Kemanusiaan', 'Respon cepat untuk korban banjir, gempa, distribusi logistik & layanan kesehatan darurat', 200000000.00, 0.00, 'aktif', NULL, '2026-04-18 06:08:29'),
(4, 'Layanan Kesehatan Gratis', 'Klinik keliling, operasi katarak gratis, dan layanan ibu & anak di daerah terpencil', 150000000.00, 0.00, 'aktif', NULL, '2026-04-18 06:08:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rekening_bank`
--

CREATE TABLE `rekening_bank` (
  `id_rekening` int(11) NOT NULL,
  `nama_bank` varchar(100) NOT NULL,
  `nomor_rekening` varchar(50) NOT NULL,
  `pemilik_rekening` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `rekening_bank`
--

INSERT INTO `rekening_bank` (`id_rekening`, `nama_bank`, `nomor_rekening`, `pemilik_rekening`, `is_active`, `created_at`) VALUES
(1, 'Bank Syariah Indonesia (BSI)', '708012345678', 'LAZISNU Pusat', 1, '2026-04-18 06:08:30'),
(2, 'Bank Mandiri Syariah', '7010004567890', 'LAZISNU', 1, '2026-04-18 06:08:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff','supervisor') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(2, 'admin', '$2y$10$TtKz5JqL9ZqL9ZqL9ZqL9uO7eX7eX7eX7eX7eX7eX7eX7eX7e', '', '', 'admin', 1, NULL, '2026-04-21 03:57:17');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_ringkasan_donasi_bulanan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_ringkasan_donasi_bulanan` (
`bulan` varchar(7)
,`jumlah_donasi` bigint(21)
,`total_nominal` decimal(37,2)
,`jenis_donasi` enum('Zakat Maal','Zakat Fitrah','Infak','Sedekah')
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_statistik_donasi`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_statistik_donasi` (
`jenis_donasi` enum('Zakat Maal','Zakat Fitrah','Infak','Sedekah')
,`jumlah_transaksi` bigint(21)
,`total_terkumpul` decimal(37,2)
,`rata_rata_nominal` decimal(19,6)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_ringkasan_donasi_bulanan`
--
DROP TABLE IF EXISTS `v_ringkasan_donasi_bulanan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ringkasan_donasi_bulanan`  AS SELECT date_format(`donasi`.`created_at`,'%Y-%m') AS `bulan`, count(0) AS `jumlah_donasi`, sum(`donasi`.`nominal`) AS `total_nominal`, `donasi`.`jenis_donasi` AS `jenis_donasi` FROM `donasi` WHERE `donasi`.`status` = 'success' GROUP BY date_format(`donasi`.`created_at`,'%Y-%m'), `donasi`.`jenis_donasi` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_statistik_donasi`
--
DROP TABLE IF EXISTS `v_statistik_donasi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_statistik_donasi`  AS SELECT `donasi`.`jenis_donasi` AS `jenis_donasi`, count(0) AS `jumlah_transaksi`, sum(`donasi`.`nominal`) AS `total_terkumpul`, avg(`donasi`.`nominal`) AS `rata_rata_nominal` FROM `donasi` WHERE `donasi`.`status` = 'success' GROUP BY `donasi`.`jenis_donasi` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `donasi`
--
ALTER TABLE `donasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nama` (`nama_donatur`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user` (`id_user`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`id_program`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `rekening_bank`
--
ALTER TABLE `rekening_bank`
  ADD PRIMARY KEY (`id_rekening`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `donasi`
--
ALTER TABLE `donasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `program`
--
ALTER TABLE `program`
  MODIFY `id_program` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `rekening_bank`
--
ALTER TABLE `rekening_bank`
  MODIFY `id_rekening` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
