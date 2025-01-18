<?php
require '../connect.php';

if (isset($_COOKIE['user']) && $_COOKIE['user'] != null) {
	$kullanici_sor = $conn->prepare('SELECT permission FROM users WHERE userCookie = ?');
	$kullanici_sor->execute([$_COOKIE['user']]);

	$result = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

	if ($result) {
		// Veritabanından gelen permission değerini doğru şekilde kontrol edin
		if ($result['permission'] == 2 || $result['permission'] == 3) {
			// Yetkili işlemler burada yapılabilir
		} else {
			// Yetki olmayanlar için yönlendirme yapın
			header('Refresh:2, ../index.php');
			exit(); // İşlemi sonlandır
		}
	} else {
		// Kullanıcı bulunamadıysa veya başka bir hata olduysa yönlendirme yapın
		header('Refresh:2, ../index.php');
		exit(); // İşlemi sonlandır
	}
} else {
	// Kullanıcı çerez bilgisine sahip değilse yönlendirme yapın
	header('Refresh:2, ../index.php');
	exit(); // İşlemi sonlandır
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?php echo $Setting['st_description'] ?>">
	<meta name="keywords" content="<?php echo $Setting['st_keywords'] ?>">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="<?php echo $Setting['st_logo'] ?>" />
	<title><?php echo $Setting['st_name'] ?></title>
	<link href="css/app.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
	<div class="wrapper">
		<nav id="sidebar" class="sidebar js-sidebar">
			<div class="sidebar-content js-simplebar">
				<a class="sidebar-brand" href="index.html">
					<span class="align-middle"><?php echo $Setting['st_name'] ?></span>
				</a>

				<ul class="sidebar-nav">

					<li class="sidebar-item active">
						<a class="sidebar-link" href="index.php">
							<i class="align-middle" data-feather="activity"></i> <span class="align-middle">Ana
								Sayfa</span>
						</a>
					</li>
					<li class="sidebar-item">
						<a class="sidebar-link" href="users.php">
							<i class="align-middle" data-feather="user"></i> <span
								class="align-middle">Kullanıcılar</span>
						</a>
					</li>
					<li class="sidebar-item">
						<a class="sidebar-link" href="products.php">
							<i class="align-middle" data-feather="briefcase"></i> <span
								class="align-middle">Ürünler</span>
						</a>
					</li>
					<li class="sidebar-item">
						<a class="sidebar-link" href="operation.php">
							<i class="align-middle" data-feather="target"></i> <span class="align-middle">Yapılan
								İşlemler</span>
						</a>
					</li>
					<li class="sidebar-item">
						<a class="sidebar-link" href="settings.php">
							<i class="align-middle" data-feather="sliders"></i> <span
								class="align-middle">Ayarlar</span>
						</a>
					</li>
			</div>
		</nav>

		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>

				<div class="navbar-collapse collapse">
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#"
								data-bs-toggle="dropdown">
								<i class="align-middle" data-feather="settings"></i>
							</a>

							<a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#"
								data-bs-toggle="dropdown">
								<img src="img/user.png" class="avatar img-fluid rounded me-1" /> <span
									class="text-dark"></span>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href=""><i class="align-middle me-1" data-feather="user"></i>
									Profil</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="#">Çıkış Yap</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">
				<div class="container-fluid p-0">

					<h1 class="h3 mb-3"><strong>Analiz</strong> Arayüzü</h1>

					<div class="row">
						<div class="col-xl-6 col-xxl-5 d-flex">
							<div class="w-100">
								<div class="row">
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Ziyaretçiler</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="users"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">14.212</h1>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Kazanç</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="dollar-sign"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">$21.300</h1>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
			</main>

			<footer class="footer">
				<div class="container-fluid">
				</div>
			</footer>
		</div>
	</div>

	<script src="js/app.js"></script>
</body>

</html>