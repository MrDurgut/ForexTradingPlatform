<?php
session_start();
if (isset($_COOKIE['user'])) {
    setcookie('user', '', time() - 3600, '/'); // Cookie'yi sil
}
session_destroy(); // Session'ı temizle
header('Location: index.php'); // Ana sayfaya yönlendir
exit();
?>