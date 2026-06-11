<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$upload_dir = __DIR__ . '/../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $jenis = $_POST['jenis_donasi'] ?? '';
    $program = $_POST['program'] ?? '';
    $nominal = floatval($_POST['nominal'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    
    if (empty($nama) || $nominal <= 0 || empty($no_hp)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }
    
    $bukti_path = null;
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['bukti'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Format file harus JPG, PNG, atau PDF.']);
            exit;
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal 2MB.']);
            exit;
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $bukti_path = 'uploads/' . $filename;
        }
    }
    
    $sql = "INSERT INTO donasi (nama_donatur, no_hp, jenis_donasi, program, nominal, catatan, bukti_pembayaran, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$nama, $no_hp, $jenis, $program, $nominal, $catatan, $bukti_path]);
    
    if ($success) {
        echo json_encode([
            'status' => 'success', 
            'message' => "Terima kasih, $nama. Donasi Rp " . number_format($nominal,0,',','.') . " telah tercatat."
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan donasi.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}
?>