<?php
require '../connect.php';
header('Content-Type: application/json');

$response = ['status' => false, 'message' => '', 'redirect' => ''];

$userCookie = (int) microtime(true);
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$repassword = $_POST['repassword'] ?? '';
$userPermission = "0";
$validateAccount = "0";

// Tüm alanların dolu olup olmadığını kontrol et
if (empty($username) || empty($password) || empty($email) || empty($repassword)) {
    $response['message'] = "Lütfen tüm alanları doldurunuz.";
}
// Şifrelerin eşleşip eşleşmediğini kontrol et
else if ($password !== $repassword) {
    $response['message'] = "Şifreler eşleşmiyor!";
}
// Email formatını kontrol et
else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = "Geçerli bir email adresi giriniz!";
} else {
    try {
        // Önce email'in kullanımda olup olmadığını kontrol et
        $email_check = $conn->prepare('SELECT COUNT(*) FROM users WHERE mailAdress = ?');
        $email_check->execute([$email]);
        if ($email_check->fetchColumn() > 0) {
            $response['message'] = "Bu email adresi zaten kullanımda!";
        } else {
            // Kullanıcı adının kullanımda olup olmadığını kontrol et
            $username_check = $conn->prepare('SELECT COUNT(*) FROM users WHERE userName = ?');
            $username_check->execute([$username]);
            if ($username_check->fetchColumn() > 0) {
                $response['message'] = "Bu kullanıcı adı zaten kullanımda!";
            } else {
                // Şifreyi hashle
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sorgu = $conn->prepare('INSERT INTO users SET userCookie = ?, userName = ?, password = ?, mailAdress = ?, permission = ?, validateAccount = ?');
                $ekle = $sorgu->execute([$userCookie, $username, $hashedPassword, $email, $userPermission, $validateAccount]);

                if ($ekle) {
                    setcookie("user", $userCookie, time() + 3600, "/");
                    $response['status'] = true;
                    $response['message'] = "Kayıt başarılı!";
                    $response['redirect'] = '../main/index.php';
                } else {
                    $response['message'] = "Kayıt sırasında bir hata oluştu, lütfen tekrar deneyin.";
                }
            }
        }
    } catch (PDOException $e) {
        $response['message'] = "Bir hata oluştu, lütfen tekrar deneyin.";
    }
}

echo json_encode($response);
?>