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

// İşlem tipini kontrol et
$action = $_POST['action'] ?? '';

if ($action === 'stop') {
    // İşlem durdurma
    $user_id = $_POST['id'] ?? null;
    $symbol = $_POST['symbol'] ?? '';
    $opType = $_POST['opType'] ?? null;
    $price = $_POST['price'] ?? 0;
    $targetRate = $_POST['targetRate'] ?? 0;

    // Veri kontrolü
    if (!$user_id || !$symbol || !$opType || !is_numeric($price) || !is_numeric($targetRate)) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem verileri']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // İşlemi bul ve güncelle
        $islem_guncelle = $conn->prepare('UPDATE operations SET opFinish = 1 WHERE user_id = ? AND symbol = ? AND opType = ? AND price = ? AND targetRate = ? AND opFinish = 0');
        $islem_guncelle->execute([$user_id, $symbol, $opType, $price, $targetRate]);

        if ($islem_guncelle->rowCount() === 0) {
            throw new Exception('İşlem bulunamadı veya zaten tamamlanmış');
        }

        // Kar/Zarar hesapla (örnek olarak)
        $currentRate = 1.02880; // Gerçek API'den alınmalı
        $karZarar = 0;

        if ($opType == 1) { // Alış
            $karZarar = ($currentRate - $targetRate) * $price;
        } else { // Satış
            $karZarar = ($targetRate - $currentRate) * $price;
        }

        // Bakiyeyi güncelle
        $bakiye_sor = $conn->prepare('SELECT * FROM wallet WHERE user_id = ?');
        $bakiye_sor->execute([$user_id]);
        $bakiye = $bakiye_sor->fetch(PDO::FETCH_ASSOC);

        if (!$bakiye) {
            throw new Exception('Cüzdan bulunamadı');
        }

        $yeni_bakiye = $bakiye['balance'] + $price + $karZarar;

        $guncelle = $conn->prepare('UPDATE wallet SET balance = ? WHERE user_id = ?');
        $guncelle->execute([$yeni_bakiye, $user_id]);

        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'İşlem başarıyla durduruldu',
            'karZarar' => number_format($karZarar, 2),
            'newBalance' => number_format($yeni_bakiye, 2)
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Normal işlem başlatma kodu
$symbol = 'EURUSD';
$opType = $_POST['opType'] ?? null;
$price = $_POST['price'] ?? 0;
$targetRate = $_POST['targetRate'] ?? 0;

// Veri kontrolü
if (!$opType || !is_numeric($price) || !is_numeric($targetRate)) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem verileri']);
    exit;
}

// Minimum işlem tutarı kontrolü
if ($price < 1000) {
    echo json_encode(['status' => 'error', 'message' => 'Minimum işlem tutarı 1,000 USD\'dir']);
    exit;
}

try {
    $conn->beginTransaction();

    // Bakiyeyi kontrol et
    $bakiye_sor = $conn->prepare('SELECT * FROM wallet WHERE user_id = ?');
    $bakiye_sor->execute([$kullanici['userID']]);
    $bakiye = $bakiye_sor->fetch(PDO::FETCH_ASSOC);
    $current_balance = $bakiye ? $bakiye['balance'] : 0;

    if ($price > $current_balance) {
        throw new Exception('Yetersiz bakiye');
    }

    // İşlemi kaydet
    $islem_ekle = $conn->prepare('INSERT INTO operations (user_id, symbol, opType, alisFiyat, price, targetRate, opDate) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $islem_ekle->execute([
        $kullanici['userID'],
        $symbol,
        $opType,
        $targetRate,
        $price,
        $targetRate
    ]);

    // Bakiyeyi güncelle
    if ($bakiye) {
        $guncelle = $conn->prepare('UPDATE wallet SET balance = balance - ? WHERE user_id = ?');
        $guncelle->execute([$price, $kullanici['userID']]);
    } else {
        throw new Exception('Cüzdan bulunamadı');
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'İşlem başarıyla başlatıldı',
        'newBalance' => number_format($current_balance - $price, 2)
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>