<?php
require '../connect.php';
header('Content-Type: application/json');

$response = ['status' => false, 'message' => '', 'redirect' => ''];

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $response['message'] = "Lütfen tüm alanları doldurunuz.";
} else {
    try {
        $kullanici_sor = $conn->prepare('SELECT password, userCookie, permission FROM users WHERE mailAdress = ?');
        $kullanici_sor->execute([$email]);

        $result = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            $userCookie = $result['userCookie'];
            setcookie("user", $userCookie, time() + 3600, "/");

            $response['status'] = true;
            $response['message'] = "Giriş başarılı!";

            if ($result['permission'] == 2 || $result['permission'] == 3) {
                $response['redirect'] = '../admin/index.php';
            } else {
                $response['redirect'] = '../main/index.php';
            }
        } else {
            $response['message'] = "Geçersiz kullanıcı adı veya şifre!";
        }
    } catch (PDOException $e) {
        $response['message'] = "Bir hata oluştu, lütfen tekrar deneyin.";
    }
}

echo json_encode($response);
?>