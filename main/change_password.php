<?php
require '../connect.php';
header('Content-Type: application/json');

if (!isset($_COOKIE['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum bulunamadı']);
    exit;
}

$kullanici_sor = $conn->prepare('SELECT * FROM users WHERE userCookie = ?');
$kullanici_sor->execute([$_COOKIE['user']]);
$kullanici = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

if (!$kullanici) {
    echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı']);
    exit;
}

// POST verilerini al
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Boş alan kontrolü
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['status' => 'error', 'message' => 'Tüm alanları doldurun']);
    exit;
}

// Yeni şifre kontrolü
if ($newPassword !== $confirmPassword) {
    echo json_encode(['status' => 'error', 'message' => 'Yeni şifreler eşleşmiyor']);
    exit;
}

// Mevcut şifre kontrolü
if (!password_verify($currentPassword, $kullanici['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Mevcut şifre yanlış']);
    exit;
}

// Yeni şifre uzunluk kontrolü
if (strlen($newPassword) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Yeni şifre en az 6 karakter olmalıdır']);
    exit;
}

try {
    // Şifreyi güncelle
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $guncelle = $conn->prepare('UPDATE users SET password = ? WHERE userID = ?');
    $guncelle->execute([$hashedPassword, $kullanici['userID']]);

    echo json_encode(['status' => 'success', 'message' => 'Şifreniz başarıyla değiştirildi']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>