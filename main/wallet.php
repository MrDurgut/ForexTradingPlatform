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

// Para çekme/yatırma işlemlerini çek
try {
  $islemler_sor = $conn->prepare('SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 10');
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
          <li class="menu-item active">
            <a href="wallet.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-wallet-alt"></i>
              <div data-i18n="Dashboards">Cüzdan</div>
            </a>
          </li>
          <li class="menu-item ">
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
            <!-- Bakiye Kartları -->
            <div class="row">
              <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="d-flex flex-column">
                        <div class="card-title mb-auto">
                          <h5 class="mb-1">Toplam Bakiye</h5>
                          <small class="text-muted">Güncel bakiyeniz</small>
                        </div>
                        <div class="d-flex align-items-center mt-3">
                          <h4 class="mb-0 me-2">$<?php echo number_format($total_bakiye, 2); ?></h4>
                        </div>
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

              <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <h5 class="card-title mb-3">Para Yatır</h5>
                    <form id="depositForm">
                      <div class="mb-3">
                        <label class="form-label">Miktar ($)</label>
                        <div class="input-group">
                          <input type="number" class="form-control" name="amount" required min="100" step="100">
                          <span class="input-group-text">USD</span>
                        </div>
                        <small class="text-muted">Minimum yatırma: 100 USD</small>
                      </div>
                      <button type="submit" class="btn btn-success w-100">
                        <i class="bx bx-money me-1"></i> Para Yatır
                      </button>
                    </form>
                  </div>
                </div>
              </div>

              <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <h5 class="card-title mb-3">Para Çek</h5>
                    <div class="alert alert-warning mb-3">
                      <i class="bx bx-info-circle me-1"></i>
                      Demo sürümde para çekme işlemi yapılamaz.
                    </div>
                    <form id="withdrawForm">
                      <div class="mb-3">
                        <label class="form-label">Miktar ($)</label>
                        <div class="input-group">
                          <input type="number" class="form-control" name="amount" required min="100" step="100"
                            disabled>
                          <span class="input-group-text">USD</span>
                        </div>
                        <small class="text-muted">Minimum çekim: 100 USD</small>
                      </div>
                      <button type="submit" class="btn btn-danger w-100" disabled>
                        <i class="bx bx-money-withdraw me-1"></i> Para Çek
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- İşlem Geçmişi -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">İşlem Geçmişi</h5>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Tarih</th>
                          <th>İşlem Tipi</th>
                          <th>Miktar</th>
                          <th>Durum</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($islemler as $islem): ?>
                          <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($islem['transaction_date'])); ?></td>
                            <td>
                              <?php if ($islem['type'] == 'deposit'): ?>
                                <span class="badge bg-label-success">Para Yatırma</span>
                              <?php else: ?>
                                <span class="badge bg-label-danger">Para Çekme</span>
                              <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($islem['amount'], 2); ?></td>
                            <td>
                              <?php if ($islem['status'] == 'completed'): ?>
                                <span class="badge bg-label-success">Tamamlandı</span>
                              <?php elseif ($islem['status'] == 'pending'): ?>
                                <span class="badge bg-label-warning">Beklemede</span>
                              <?php else: ?>
                                <span class="badge bg-label-danger">İptal Edildi</span>
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
          <!-- / Content -->
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#depositForm').on('submit', function (e) {
        e.preventDefault();

        // Form verilerini al
        const formData = new FormData(this);
        const amount = formData.get('amount');
        formData.append('type', 'deposit'); // Para yatırma işlemi olduğunu belirt

        // Loading göster
        Swal.fire({
          title: 'Para Yatırılıyor...',
          text: `$${amount} yatırma işlemi gerçekleştiriliyor`,
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Para yatırma işlemini gerçekleştir
        fetch('wallet_transaction.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            console.log('Response:', response); // Debug için
            return response.json();
          })
          .then(data => {
            console.log('Data:', data); // Debug için
            if (data.status === 'success') {
              Swal.fire({
                title: 'Para Yatırıldı!',
                html: `
                Para yatırma işleminiz başarıyla tamamlandı.<br>
                <b>Yatırılan:</b> $${amount}<br>
                <b>Yeni Bakiye:</b> $${data.newBalance}
              `,
                icon: 'success'
              }).then(() => {
                window.location.reload();
              });
            } else {
              throw new Error(data.message || 'Bir hata oluştu');
            }
          })
          .catch(error => {
            console.error('Error:', error); // Debug için
            Swal.fire({
              title: 'Hata!',
              text: error.message || 'Bir hata oluştu',
              icon: 'error'
            });
          });
      });

      // Para çekme formu - Demo uyarısı
      $('#withdrawForm').on('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: 'Demo Sürüm',
          text: 'Demo sürümde para çekme işlemi yapılamaz.',
          icon: 'warning'
        });
      });
    });
  </script>
</body>

</html>