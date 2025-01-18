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

// Aktif işlemleri çek
try {
  $aktif_islemler = $conn->prepare('SELECT COUNT(*) as count FROM operations WHERE user_id = ? AND opFinish = 0');
  $aktif_islemler->execute([$kullanici['userID']]);
  $aktif_islem_sayisi = $aktif_islemler->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
  $aktif_islem_sayisi = 0;
}

// Son işlemleri çek
try {
  $son_islemler = $conn->prepare('SELECT * FROM operations WHERE user_id = ? ORDER BY opDate DESC LIMIT 5');
  $son_islemler->execute([$kullanici['userID']]);
  $islemler = $son_islemler->fetchAll(PDO::FETCH_ASSOC);
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
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
          <li class="menu-item active">
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
            <!-- Hoşgeldin Mesajı -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="card bg-primary text-white">
                  <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl me-3">
                      <img src="../assets/img/avatars/user.png" alt="Avatar" class="rounded-circle bg-white">
                    </div>
                    <div>
                      <h3 class="card-title text-white mb-1">Hoş Geldin,
                        <?php echo htmlspecialchars($kullanici['userName']); ?>!
                      </h3>
                      <p class="mb-0">Forex işlemlerine başlamak için hazırsın. İyi kazançlar!</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row">
              <!-- Toplam Bakiye -->
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

              <!-- Aktif İşlemler -->
              <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="d-flex flex-column">
                        <div class="card-title mb-auto">
                          <h5 class="mb-1">Aktif İşlemler</h5>
                          <small class="text-muted">Devam eden işlemleriniz</small>
                        </div>
                        <div class="d-flex align-items-center mt-3">
                          <h4 class="mb-0 me-2"><?php echo $aktif_islem_sayisi; ?></h4>
                          <small class="text-muted">işlem</small>
                        </div>
                      </div>
                      <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                          <i class="bx bx-trending-up bx-sm"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Hızlı İşlemler -->
              <div class="col-lg-4 col-md-12 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <h5 class="card-title mb-3">Hızlı İşlemler</h5>
                    <div class="d-grid gap-2">
                      <a href="operations.php" class="btn btn-primary">
                        <i class="bx bx-plus-circle me-1"></i> Yeni İşlem
                      </a>
                      <a href="wallet.php" class="btn btn-success">
                        <i class="bx bx-money me-1"></i> Para Yatır/Çek
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- Canlı Kur -->
              <div class="col-12 mb-4">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title mb-0">EUR/USD Canlı Kur</h5>
                  </div>
                  <div class="card-body">
                    <!-- TradingView Widget BEGIN -->
                    <div class="tradingview-widget-container">
                      <div id="tradingview_chart"></div>
                      <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
                      <script type="text/javascript">
                        new TradingView.widget({
                          "width": "100%",
                          "height": 400,
                          "symbol": "FX:EURUSD",
                          "interval": "1",
                          "timezone": "Europe/Istanbul",
                          "theme": "light",
                          "style": "1",
                          "locale": "tr",
                          "toolbar_bg": "#f1f3f6",
                          "enable_publishing": false,
                          "hide_side_toolbar": false,
                          "allow_symbol_change": false,
                          "container_id": "tradingview_chart"
                        });
                      </script>
                    </div>
                    <!-- TradingView Widget END -->
                  </div>
                </div>
              </div>

              <!-- Son İşlemler -->
              <div class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Son İşlemler</h5>
                    <a href="operations.php" class="btn btn-primary btn-sm">
                      <i class="bx bx-list-ul me-1"></i> Tüm İşlemler
                    </a>
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
                          <th>İşlem</th>
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
                            <td>
                              <?php if ($islem['opFinish'] == 0): ?>
                                <button type="button" class="btn btn-sm btn-danger stop-operation"
                                  data-symbol="<?php echo $islem['symbol']; ?>" data-type="<?php echo $islem['opType']; ?>"
                                  data-price="<?php echo $islem['price']; ?>"
                                  data-rate="<?php echo $islem['alisFiyat']; ?>">
                                  <i class="bx bx-stop-circle"></i> Durdur
                                </button>
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
      // İşlem durdurma butonu
      $('.stop-operation').on('click', function () {
        const button = $(this);
        const symbol = button.data('symbol');
        const opType = button.data('type');
        const price = button.data('price');
        const alisFiyat = button.data('rate');

        Swal.fire({
          title: 'İşlemi Durdur',
          text: 'Bu işlemi durdurmak istediğinize emin misiniz?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Evet, Durdur',
          cancelButtonText: 'İptal',
          showLoaderOnConfirm: true,
          preConfirm: () => {
            const formData = new FormData();
            formData.append('symbol', symbol);
            formData.append('opType', opType);
            formData.append('price', price);
            formData.append('alisFiyat', alisFiyat);

            return fetch('oprupt.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.status === 'success') {
                  return data;
                }
                throw new Error(data.message || 'Bir hata oluştu');
              })
              .catch(error => {
                Swal.showValidationMessage(`İşlem başarısız: ${error.message}`);
              });
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const data = result.value;
            Swal.fire({
              title: 'İşlem Durduruldu!',
              html: `
                İşlem başarıyla durduruldu.<br>
                Kar/Zarar: ${data.karZarar} USD<br>
                Yeni Bakiye: ${data.newBalance} USD
              `,
              icon: 'success'
            }).then(() => {
              window.location.reload();
            });
          }
        });
      });

      // Hoşgeldin mesajı
      Swal.fire({
        title: 'Hoş Geldin!',
        text: '<?php echo htmlspecialchars($kullanici['userName']); ?>, forex platformuna hoş geldin!',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    });
  </script>
</body>

</html>