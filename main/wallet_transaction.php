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
$amount = $_POST['amount'] ?? 0;
$type = $_POST['type'] ?? 'deposit';

// Miktar kontrolü
if (!is_numeric($amount) || $amount < 100) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz miktar. Minimum işlem tutarı 100 USD\'dir']);
    exit;
}

try {
    $conn->beginTransaction();

    // Mevcut bakiyeyi çek
    $bakiye_sor = $conn->prepare('SELECT * FROM wallet WHERE user_id = ?');
    $bakiye_sor->execute([$kullanici['userID']]);
    $bakiye = $bakiye_sor->fetch(PDO::FETCH_ASSOC);
    $current_balance = $bakiye ? $bakiye['balance'] : 0;

    // Para çekme işleminde bakiye kontrolü
    if ($type == 'withdraw' && $amount > $current_balance) {
        throw new Exception('Yetersiz bakiye');
    }

    // Yeni bakiyeyi hesapla
    $yeni_bakiye = $type == 'deposit' ? $current_balance + $amount : $current_balance - $amount;

    // İşlemi kaydet
    $islem_ekle = $conn->prepare('INSERT INTO wallet_transactions (user_id, type, amount, status, transaction_date) VALUES (?, ?, ?, ?, NOW())');
    $islem_ekle->execute([
        $kullanici['userID'],
        $type,
        $amount,
        'completed'
    ]);

    if ($bakiye) {
        // Mevcut cüzdanı güncelle
        $guncelle = $conn->prepare('UPDATE wallet SET balance = ? WHERE user_id = ?');
        $guncelle->execute([$yeni_bakiye, $kullanici['userID']]);
    } else {
        // Yeni cüzdan oluştur
        $ekle = $conn->prepare('INSERT INTO wallet (user_id, balance) VALUES (?, ?)');
        $ekle->execute([$kullanici['userID'], $yeni_bakiye]);
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'İşlem başarıyla tamamlandı',
        'newBalance' => number_format($yeni_bakiye, 2)
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>