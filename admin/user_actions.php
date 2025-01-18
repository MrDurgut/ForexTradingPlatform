<?php
require '../connect.php';
header('Content-Type: application/json');

// Yetki kontrolü
if (!isset($_COOKIE['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum bulunamadı']);
    exit;
}

$kullanici_sor = $conn->prepare('SELECT permission FROM users WHERE userCookie = ?');
$kullanici_sor->execute([$_COOKIE['user']]);
$result = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

if (!$result || ($result['permission'] != 2 && $result['permission'] != 3)) {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'edit':
        // Kullanıcı düzenleme
        $userID = $_POST['userID'] ?? 0;
        $userName = $_POST['userName'] ?? '';
        $email = $_POST['email'] ?? '';
        $permission = $_POST['permission'] ?? 1;

        // Veri kontrolü
        if (!$userID || !$userName || !$email) {
            echo json_encode(['status' => 'error', 'message' => 'Eksik bilgi']);
            exit;
        }

        try {
            // Email kontrolü
            $email_check = $conn->prepare('SELECT userID FROM users WHERE mailAdress = ? AND userID != ?');
            $email_check->execute([$email, $userID]);
            if ($email_check->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor']);
                exit;
            }

            // Kullanıcı adı kontrolü
            $username_check = $conn->prepare('SELECT userID FROM users WHERE userName = ? AND userID != ?');
            $username_check->execute([$userName, $userID]);
            if ($username_check->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Bu kullanıcı adı başka bir kullanıcı tarafından kullanılıyor']);
                exit;
            }

            // Kullanıcıyı güncelle
            $guncelle = $conn->prepare('UPDATE users SET userName = ?, mailAdress = ?, permission = ? WHERE userID = ?');
            $guncelle->execute([$userName, $email, $permission, $userID]);

            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']);
        }
        break;

    case 'delete':
        // Kullanıcı silme
        $userID = $_POST['userID'] ?? 0;

        if (!$userID) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz kullanıcı ID']);
            exit;
        }

        try {
            // Kullanıcının yetkisini kontrol et
            $yetki_sor = $conn->prepare('SELECT permission FROM users WHERE userID = ?');
            $yetki_sor->execute([$userID]);
            $yetki = $yetki_sor->fetch(PDO::FETCH_ASSOC);

            if ($yetki && $yetki['permission'] == 3) {
                echo json_encode(['status' => 'error', 'message' => 'Admin kullanıcısı silinemez']);
                exit;
            }

            $conn->beginTransaction();

            // Kullanıcının işlemlerini sil
            $conn->prepare('DELETE FROM operations WHERE user_id = ?')->execute([$userID]);

            // Kullanıcının cüzdanını sil
            $conn->prepare('DELETE FROM wallet WHERE user_id = ?')->execute([$userID]);

            // Kullanıcının cüzdan işlemlerini sil
            $conn->prepare('DELETE FROM wallet_transactions WHERE user_id = ?')->execute([$userID]);

            // Kullanıcıyı sil
            $conn->prepare('DELETE FROM users WHERE userID = ?')->execute([$userID]);

            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']);
        }
        break;

    case 'edit_balance':
        // Bakiye düzenleme
        $userID = $_POST['userID'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $type = $_POST['type'] ?? 'add';

        // Veri kontrolü
        if (!$userID || !is_numeric($amount) || $amount < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // Mevcut bakiyeyi çek
            $bakiye_sor = $conn->prepare('SELECT * FROM wallet WHERE user_id = ?');
            $bakiye_sor->execute([$userID]);
            $bakiye = $bakiye_sor->fetch(PDO::FETCH_ASSOC);
            $current_balance = $bakiye ? $bakiye['balance'] : 0;

            // Yeni bakiyeyi hesapla
            switch ($type) {
                case 'add':
                    $new_balance = $current_balance + $amount;
                    break;
                case 'subtract':
                    $new_balance = $current_balance - $amount;
                    if ($new_balance < 0) {
                        throw new Exception('Bakiye negatif olamaz');
                    }
                    break;
                case 'set':
                    $new_balance = $amount;
                    break;
                default:
                    throw new Exception('Geçersiz işlem tipi');
            }

            // İşlemi kaydet
            $islem_ekle = $conn->prepare('INSERT INTO wallet_transactions (user_id, type, amount, status, transaction_date) VALUES (?, ?, ?, ?, NOW())');
            $islem_ekle->execute([
                $userID,
                $type == 'add' ? 'deposit' : 'withdraw',
                $amount,
                'completed'
            ]);

            // Bakiyeyi güncelle
            if ($bakiye) {
                $guncelle = $conn->prepare('UPDATE wallet SET balance = ? WHERE user_id = ?');
                $guncelle->execute([$new_balance, $userID]);
            } else {
                $ekle = $conn->prepare('INSERT INTO wallet (user_id, balance) VALUES (?, ?)');
                $ekle->execute([$userID, $new_balance]);
            }

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Bakiye güncellendi',
                'newBalance' => number_format($new_balance, 2)
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem']);
}
?>