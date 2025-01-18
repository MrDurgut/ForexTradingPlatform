<?php
require '../connect.php';
?>

<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo $Setting['st_description'] ?>">
	<meta name="keywords" content="<?php echo $Setting['st_keywords'] ?>">
	<link rel="shortcut icon" href="<?php echo $Setting['st_logo'] ?>">
	<title>Kayıt Ol - <?php echo $Setting['st_name'] ?></title>
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

		.form-control {
			background: rgba(255, 255, 255, 0.1);
			border: none;
			border-radius: 5px;
			padding: 12px;
			margin-bottom: 15px;
			color: white;
		}

		.form-control::placeholder {
			color: rgba(255, 255, 255, 0.6);
		}

		.form-control:focus {
			background: rgba(255, 255, 255, 0.2);
			box-shadow: none;
			color: white;
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

		.btn-register {
			background: #4CAF50;
			color: white;
		}

		.btn-back {
			background: #2196F3;
			color: white;
		}

		.btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
		}

		.text-white {
			color: #fff !important;
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
			<source src="../assets/mp4/bg.mp4" type="video/mp4">
		</video>
	</div>
	<div class="login-box">
		<h1 class="site-title">Kayıt Ol</h1>
		<div class="alert" id="alertBox" role="alert"></div>
		<form id="registerForm" method="POST">
			<input type="text" class="form-control" name="username" placeholder="Kullanıcı Adı" required>
			<input type="email" class="form-control" name="email" placeholder="E-posta" required>
			<input type="password" class="form-control" name="password" placeholder="Şifre" required>
			<input type="password" class="form-control" name="repassword" placeholder="Şifre Tekrar" required>
			<button type="submit" class="btn btn-register">Kayıt Ol</button>
			<a href="../" class="btn btn-back">Geri Dön</a>
		</form>
	</div>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		$(document).ready(function () {
			$('#registerForm').on('submit', function (e) {
				e.preventDefault();

				const $form = $(this);
				const $alert = $('#alertBox');
				const $submitBtn = $form.find('button[type="submit"]');

				// Şifre kontrolü
				const password = $form.find('input[name="password"]').val();
				const repassword = $form.find('input[name="repassword"]').val();

				if (password !== repassword) {
					$alert
						.removeClass('alert-success')
						.addClass('alert-danger')
						.text('Şifreler eşleşmiyor!')
						.fadeIn();
					return;
				}

				// Butonu devre dışı bırak
				$submitBtn.prop('disabled', true).text('Kayıt Yapılıyor...');

				$.ajax({
					url: 'process.php',
					type: 'POST',
					data: $form.serialize(),
					dataType: 'json',
					success: function (response) {
						$alert
							.removeClass('alert-danger alert-success')
							.addClass(response.status ? 'alert-success' : 'alert-danger')
							.text(response.message)
							.fadeIn();

						if (response.status) {
							setTimeout(function () {
								window.location.href = response.redirect;
							}, 1000);
						} else {
							$submitBtn.prop('disabled', false).text('Kayıt Ol');
						}
					},
					error: function () {
						$alert
							.removeClass('alert-success')
							.addClass('alert-danger')
							.text('Bir hata oluştu, lütfen tekrar deneyin.')
							.fadeIn();

						$submitBtn.prop('disabled', false).text('Kayıt Ol');
					}
				});
			});
		});
	</script>
</body>

</html>