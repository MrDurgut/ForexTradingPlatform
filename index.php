<?php
require 'connect.php';

if (isset($_COOKIE['user']) && $_COOKIE['user'] != null) {
    $kullanici_sor = $conn->prepare('SELECT permission FROM users WHERE userCookie = ?');
    $kullanici_sor->execute([$_COOKIE['user']]);

    $result = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['permission'] == 2 || $result['permission'] == 3) {
            header('Location: admin/index.php');
            exit();
        } else {
            header('Location: main/index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $Setting['st_description'] ?>">
    <meta name="keywords" content="<?php echo $Setting['st_keywords'] ?>">
    <link rel="shortcut icon" href="<?php echo $Setting['st_logo'] ?>">
    <title><?php echo $Setting['st_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .video-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .video-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 12, 41, 0.8);
            backdrop-filter: blur(8px);
        }

        .video-container video {
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(5px);
        }

        .site-title {
            color: #fff;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .description {
            color: #fff;
            opacity: 0.8;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-login {
            background: #4CAF50;
            color: white;
        }

        .btn-register {
            background: #2196F3;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 2rem;
            }

            .site-title {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="video-container">
        <video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">
            <source src="assets/mp4/bg.mp4" type="video/mp4">
        </video>
    </div>
    <div class="login-box">
        <h1 class="site-title"><?php echo $Setting['st_name'] ?></h1>
        <p class="description">Güvenli ve hızlı işlemler için hemen başlayın</p>
        <form>
            <button type="button" class="btn btn-login" onclick="window.location.href='login/index.php'">
                Giriş Yap
            </button>
            <button type="button" class="btn btn-register" onclick="window.location.href='register/index.php'">
                Kayıt Ol
            </button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>