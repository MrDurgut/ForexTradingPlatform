<?php
require '../connect.php';

if (isset($_COOKIE['user']) && $_COOKIE['user'] != null) {
    $kullanici_sor = $conn->prepare('SELECT permission FROM users WHERE userCookie = ?');
    $kullanici_sor->execute([$_COOKIE['user']]);

    $result = $kullanici_sor->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['permission'] != 2 && $result['permission'] != 3) {
            header('Location: ../index.php');
            exit();
        }
    } else {
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}

// Tüm işlemleri çek
$islemler_sor = $conn->prepare('
    SELECT o.*, u.userName, w.balance 
    FROM operations o 
    LEFT JOIN users u ON o.user_id = u.userID 
    LEFT JOIN wallet w ON o.user_id = w.user_id 
    ORDER BY o.opDate DESC
');
$islemler_sor->execute();
$islemler = $islemler_sor->fetchAll(PDO::FETCH_ASSOC);
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
    <title><?php echo $Setting['st_name'] ?> - İşlem Yönetimi</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin template CSS -->
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <nav id="sidebar" class="sidebar js-sidebar">
            <div class="sidebar-content js-simplebar">
                <a class="sidebar-brand" href="index.php">
                    <span class="align-middle"><?php echo $Setting['st_name'] ?></span>
                </a>

                <ul class="sidebar-nav">
                    <li class="sidebar-item">
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
                    <li class="sidebar-item active">
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
                </ul>
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
                            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#"
                                data-bs-toggle="dropdown">
                                <i class="align-middle" data-feather="settings"></i>
                            </a>

                            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#"
                                data-bs-toggle="dropdown">
                                <img src="img/user.png" class="avatar img-fluid rounded me-1" /> <span
                                    class="text-dark">Admin</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="../logout.php">Çıkış Yap</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">İşlem Listesi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tarih</th>
                                                    <th>Kullanıcı</th>
                                                    <th>İşlem Tipi</th>
                                                    <th>Miktar</th>
                                                    <th>Hedef Kur</th>
                                                    <th>Durum</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($islemler as $islem): ?>
                                                    <tr>
                                                        <td><?php echo date('d.m.Y H:i', strtotime($islem['opDate'])); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($islem['userName']); ?>
                                                            <br>
                                                            <small class="text-muted">Bakiye:
                                                                $<?php echo number_format($islem['balance'], 2); ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if ($islem['opType'] == 1): ?>
                                                                <span class="badge bg-success">Alış</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Satış</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>$<?php echo number_format($islem['price'], 2); ?></td>
                                                        <td><?php echo $islem['targetRate']; ?></td>
                                                        <td>
                                                            <?php if ($islem['opFinish'] == 1): ?>
                                                                <span class="badge bg-success">Tamamlandı</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">Beklemede</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($islem['opFinish'] == 0): ?>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm stop-operation"
                                                                    data-id="<?php echo $islem['user_id']; ?>"
                                                                    data-symbol="EURUSD"
                                                                    data-type="<?php echo $islem['opType']; ?>"
                                                                    data-price="<?php echo $islem['price']; ?>"
                                                                    data-rate="<?php echo $islem['targetRate']; ?>">
                                                                    <i data-feather="stop-circle"></i> Durdur
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
                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-start">
                            <p class="mb-0">
                                <a class="text-muted" href="#"
                                    target="_blank"><strong><?php echo $Setting['st_name'] ?></strong></a>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Admin template JS -->
    <script src="js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const stopButtons = document.querySelectorAll('.stop-operation');

            stopButtons.forEach(button => {
                button.addEventListener('click', async function (e) {
                    e.preventDefault();

                    const formData = new FormData();
                    formData.append('id', button.getAttribute('data-id'));
                    formData.append('symbol', 'EURUSD');
                    formData.append('opType', button.getAttribute('data-type'));
                    formData.append('price', button.getAttribute('data-price'));
                    formData.append('targetRate', button.getAttribute('data-rate'));
                    formData.append('action', 'stop');

                    try {
                        const result = await Swal.fire({
                            title: 'Emin misiniz?',
                            text: "Bu işlemi durdurmak istediğinize emin misiniz?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Evet, Durdur',
                            cancelButtonText: 'İptal'
                        });

                        if (result.isConfirmed) {
                            const response = await fetch('../main/oprupt.php', {
                                method: 'POST',
                                body: formData
                            });

                            const data = await response.json();
                            console.log('Sunucu yanıtı:', data);

                            if (data.status === 'success') {
                                await Swal.fire({
                                    title: 'İşlem Durduruldu!',
                                    html: `
                                        İşlem başarıyla durduruldu.<br>
                                        <b>Kar/Zarar:</b> ${data.karZarar} USD<br>
                                        <b>Yeni Bakiye:</b> ${data.newBalance} USD
                                    `,
                                    icon: 'success'
                                });
                                window.location.reload();
                            } else {
                                throw new Error(data.message || 'Bilinmeyen bir hata oluştu');
                            }
                        }
                    } catch (error) {
                        console.error('Hata:', error);
                        Swal.fire('Hata!', error.message, 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>