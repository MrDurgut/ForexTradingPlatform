<?php
require '../connect.php';

if (!isset($_COOKIE['user']) || $_COOKIE['user'] == null) {
  header('Location: ../index.php');
  exit;
}

$kullanici_sor = $conn->prepare('SELECT * FROM users WHERE userCookie = ?');
$kullanici_sor->execute([$_COOKIE['user']]);
$kullanici = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

if (!$kullanici) {
  header('Location: ../index.php');
  exit;
}

// Bakiyeyi çek
try {
  $bakiye_sor = $conn->prepare('SELECT balance FROM wallet WHERE user_id = ?');
  $bakiye_sor->execute([$kullanici['userID']]);
  $bakiye = $bakiye_sor->fetch(PDO::FETCH_ASSOC);
  $total_bakiye = $bakiye['balance'] ?? 0;
} catch (PDOException $e) {
  $total_bakiye = 0;
}

// İşlem geçmişini çek
try {
  $islemler_sor = $conn->prepare('SELECT * FROM operations WHERE user_id = ? ORDER BY opDate DESC LIMIT 10');
  $islemler_sor->execute([$kullanici['userID']]);
  $islemler = $islemler_sor->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $islemler = [];
}

?>

<!DOCTYPE html>

<html lang="tr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title><?php echo $Setting['st_name'] ?></title>

  <meta name="description" content="<?php echo $Setting['st_description'] ?>" />
  <meta name="keywords" content="<?php echo $Setting['st_keywords'] ?>">
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?php echo $Setting['st_logo'] ?>" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="assets/js/config.js"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->

      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="index.php" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bold ms-2"><?php echo $Setting['st_name'] ?></span>
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
          <li class="menu-item">
            <a href="index.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Dashboards">Ana Sayfa</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="operations.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-objects-vertical-bottom"></i>
              <div data-i18n="Dashboards">İşlem Yap</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="wallet.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-wallet-alt"></i>
              <div data-i18n="Dashboards">Cüzdan</div>
            </a>
          </li>
          <li class="menu-item active">
            <a href="profile.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-user"></i>
              <div data-i18n="Dashboards">Profil</div>
            </a>
          </li>
          <li class="menu-item ">
            <a href="../logout.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-log-out-circle"></i>
              <div data-i18n="Dashboards">Çıkış Yap</div>
            </a>
          </li>
      </aside>
      <div class="layout-page">
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>
        </nav>
        <div class="content-wrapper">
          <!-- Content -->

          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <!-- Profil Kartı -->
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card">
                  <div class="card-body text-center">
                    <div class="avatar avatar-xl mx-auto mb-3">
                      <img src="../assets/img/avatars/user.png" alt="Avatar" class="rounded-circle bg-white">
                    </div>
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($kullanici['userName']); ?></h5>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($kullanici['mailAdress']); ?></p>
                    <div class="d-flex justify-content-center gap-2">
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#editProfileModal">
                        <i class="bx bx-edit-alt me-1"></i> Profili Düzenle
                      </button>
                      <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                        data-bs-target="#changePasswordModal">
                        <i class="bx bx-key me-1"></i> Şifre Değiştir
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bakiye Kartı -->
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div class="content-left">
                        <h5 class="card-title mb-0">Toplam Bakiye</h5>
                        <small class="text-muted">Güncel bakiyeniz</small>
                        <h2 class="mb-0 mt-2">$<?php echo number_format($total_bakiye, 2); ?></h2>
                      </div>
                      <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
                          <i class="bx bx-dollar bx-sm"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- İşlem İstatistikleri -->
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">İşlem İstatistikleri</h5>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-trending-up"></i>
                          </span>
                        </div>
                        <div>
                          <h6 class="mb-0">Toplam İşlem</h6>
                          <small class="text-muted"><?php echo count($islemler); ?> işlem</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Son İşlemler -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Son İşlemler</h5>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Tarih</th>
                          <th>İşlem Tipi</th>
                          <th>Miktar</th>
                          <th>Kur</th>
                          <th>Durum</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($islemler as $islem): ?>
                          <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($islem['opDate'])); ?></td>
                            <td>
                              <?php if ($islem['opType'] == 1): ?>
                                <span class="badge bg-label-success">Alış</span>
                              <?php else: ?>
                                <span class="badge bg-label-danger">Satış</span>
                              <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($islem['price'], 2); ?></td>
                            <td><?php echo $islem['alisFiyat']; ?></td>
                            <td>
                              <?php if ($islem['opFinish'] == 1): ?>
                                <span class="badge bg-label-success">Tamamlandı</span>
                              <?php else: ?>
                                <span class="badge bg-label-warning">Beklemede</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profil Düzenleme Modal -->
          <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Profili Düzenle</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form id="editProfileForm">
                    <div class="mb-3">
                      <label class="form-label">Kullanıcı Adı</label>
                      <input type="text" class="form-control" name="userName"
                        value="<?php echo htmlspecialchars($kullanici['userName']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">E-posta</label>
                      <input type="email" class="form-control" name="email"
                        value="<?php echo htmlspecialchars($kullanici['mailAdress']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Şifre Değiştirme Modal -->
          <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Şifre Değiştir</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form id="changePasswordForm">
                    <div class="mb-3">
                      <label class="form-label">Mevcut Şifre</label>
                      <input type="password" class="form-control" name="currentPassword" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Yeni Şifre</label>
                      <input type="password" class="form-control" name="newPassword" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Yeni Şifre (Tekrar)</label>
                      <input type="password" class="form-control" name="confirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Şifreyi Değiştir</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Content -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->

  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/vendor/libs/popper/popper.js"></script>
  <script src="assets/vendor/js/bootstrap.js"></script>
  <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="assets/vendor/js/menu.js"></script>

  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>

  <!-- Main JS -->
  <script src="assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="assets/js/dashboards-analytics.js"></script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>

  <script>
    $(document).ready(function () {
      // Profil düzenleme formu
      $('#editProfileForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('update_profile.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                title: 'Başarılı!',
                text: 'Profil bilgileriniz güncellendi.',
                icon: 'success'
              }).then(() => {
                window.location.reload();
              });
            } else {
              Swal.fire({
                title: 'Hata!',
                text: data.message || 'Bir hata oluştu',
                icon: 'error'
              });
            }
          })
          .catch(error => {
            Swal.fire({
              title: 'Hata!',
              text: 'Bir hata oluştu',
              icon: 'error'
            });
          });
      });

      // Şifre değiştirme formu
      $('#changePasswordForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        if (formData.get('newPassword') !== formData.get('confirmPassword')) {
          Swal.fire({
            title: 'Hata!',
            text: 'Yeni şifreler eşleşmiyor',
            icon: 'error'
          });
          return;
        }

        fetch('change_password.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                title: 'Başarılı!',
                text: 'Şifreniz başarıyla değiştirildi.',
                icon: 'success'
              }).then(() => {
                window.location.reload();
              });
            } else {
              Swal.fire({
                title: 'Hata!',
                text: data.message || 'Bir hata oluştu',
                icon: 'error'
              });
            }
          })
          .catch(error => {
            Swal.fire({
              title: 'Hata!',
              text: 'Bir hata oluştu',
              icon: 'error'
            });
          });
      });
    });
  </script>
</body>

</html>