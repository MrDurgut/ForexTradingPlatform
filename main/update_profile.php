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
$userName = $_POST['userName'] ?? '';
$email = $_POST['email'] ?? '';

// Boş alan kontrolü
if (empty($userName) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Tüm alanları doldurun']);
    exit;
}

// Email formatı kontrolü
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz email formatı']);
    exit;
}

// Email kullanımda mı kontrolü (kendi emaili hariç)
$email_check = $conn->prepare('SELECT * FROM users WHERE email = ? AND userID != ?');
$email_check->execute([$email, $kullanici['userID']]);
if ($email_check->rowCount() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Bu email adresi başka bir kullanıcı tarafından kullanılıyor']);
    exit;
}

try {
    // Profil bilgilerini güncelle
    $guncelle = $conn->prepare('UPDATE users SET userName = ?, email = ? WHERE userID = ?');
    $guncelle->execute([$userName, $email, $kullanici['userID']]);

    echo json_encode(['status' => 'success', 'message' => 'Profil bilgileri güncellendi']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>