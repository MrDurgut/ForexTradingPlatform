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

// Tüm kullanıcıları çek
$kullanicilar_sor = $conn->prepare('SELECT * FROM users ORDER BY userID DESC');
$kullanicilar_sor->execute();
$kullanicilar = $kullanicilar_sor->fetchAll(PDO::FETCH_ASSOC);
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
    <title><?php echo $Setting['st_name'] ?> - Kullanıcı Yönetimi</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin template CSS -->
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        .modal {
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-backdrop {
            display: none;
        }

        .modal-dialog {
            margin: 8rem auto;
        }
    </style>
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
                    <li class="sidebar-item active">
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
                                    <h5 class="card-title">Kullanıcı Listesi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Kullanıcı Adı</th>
                                                    <th>E-posta</th>
                                                    <th>Yetki</th>
                                                    <th>Bakiye</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($kullanicilar as $kullanici):
                                                    // Kullanıcının bakiyesini çek
                                                    $bakiye_sor = $conn->prepare('SELECT balance FROM wallet WHERE user_id = ?');
                                                    $bakiye_sor->execute([$kullanici['userID']]);
                                                    $bakiye = $bakiye_sor->fetch(PDO::FETCH_ASSOC);
                                                    $total_bakiye = $bakiye ? $bakiye['balance'] : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $kullanici['userID']; ?></td>
                                                        <td><?php echo htmlspecialchars($kullanici['userName']); ?></td>
                                                        <td><?php echo htmlspecialchars($kullanici['mailAdress']); ?></td>
                                                        <td>
                                                            <?php if ($kullanici['permission'] == 3): ?>
                                                                <span class="badge bg-danger">Admin</span>
                                                            <?php elseif ($kullanici['permission'] == 2): ?>
                                                                <span class="badge bg-warning">Moderatör</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Kullanıcı</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>$<?php echo number_format($total_bakiye, 2); ?></td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-success btn-sm edit-balance"
                                                                data-bs-toggle="modal" data-bs-target="#editBalanceModal"
                                                                data-id="<?php echo $kullanici['userID']; ?>"
                                                                data-username="<?php echo htmlspecialchars($kullanici['userName']); ?>"
                                                                data-balance="<?php echo $total_bakiye; ?>">
                                                                <i data-feather="dollar-sign"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm edit-user"
                                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                                data-id="<?php echo $kullanici['userID']; ?>"
                                                                data-username="<?php echo htmlspecialchars($kullanici['userName']); ?>"
                                                                data-email="<?php echo htmlspecialchars($kullanici['mailAdress']); ?>"
                                                                data-permission="<?php echo $kullanici['permission']; ?>">
                                                                <i data-feather="edit-2"></i>
                                                            </button>
                                                            <?php if ($kullanici['permission'] != 3): ?>
                                                                <button type="button" class="btn btn-danger btn-sm delete-user"
                                                                    data-id="<?php echo $kullanici['userID']; ?>">
                                                                    <i data-feather="trash-2"></i>
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

    <!-- Kullanıcı Düzenleme Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kullanıcı Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" name="userID" id="editUserID">
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="userName" id="editUserName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" id="editUserEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yetki</label>
                            <select class="form-select" name="permission" id="editUserPermission">
                                <option value="1">Kullanıcı</option>
                                <option value="2">Moderatör</option>
                                <option value="3">Admin</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bakiye Düzenleme Modal -->
    <div class="modal fade" id="editBalanceModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bakiye Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editBalanceForm">
                        <input type="hidden" name="userID" id="editBalanceUserID">
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı</label>
                            <input type="text" class="form-control" id="editBalanceUsername" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mevcut Bakiye ($)</label>
                            <input type="text" class="form-control" id="editBalanceCurrentBalance" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İşlem Tipi</label>
                            <select class="form-select" name="type" id="editBalanceType">
                                <option value="add">Para Ekle (+)</option>
                                <option value="subtract">Para Çıkar (-)</option>
                                <option value="set">Bakiyeyi Ayarla (=)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Miktar ($)</label>
                            <input type="number" class="form-control" name="amount" id="editBalanceAmount" required
                                min="0" step="0.01">
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-success">Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-backdrop {
            z-index: 9998;
        }
    </style>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Admin template JS -->
    <script src="js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Kullanıcı düzenleme
            const editButtons = document.querySelectorAll('.edit-user');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const username = this.dataset.username;
                    const email = this.dataset.email;
                    const permission = this.dataset.permission;

                    document.getElementById('editUserID').value = id;
                    document.getElementById('editUserName').value = username;
                    document.getElementById('editUserEmail').value = email;
                    document.getElementById('editUserPermission').value = permission;
                });
            });

            // Kullanıcı silme
            const deleteButtons = document.querySelectorAll('.delete-user');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;

                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: "Bu kullanıcıyı silmek istediğinize emin misiniz?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Evet, Sil',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // AJAX ile silme işlemi
                            const formData = new FormData();
                            formData.append('action', 'delete');
                            formData.append('userID', id);

                            fetch('user_actions.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire('Başarılı!', 'Kullanıcı başarıyla silindi.', 'success')
                                            .then(() => {
                                                window.location.reload();
                                            });
                                    } else {
                                        Swal.fire('Hata!', data.message || 'Bir hata oluştu.', 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Hata!', 'Bir hata oluştu.', 'error');
                                });
                        }
                    });
                });
            });

            // Kullanıcı düzenleme formu
            document.getElementById('editUserForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'edit');

                fetch('user_actions.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire('Başarılı!', 'Kullanıcı bilgileri güncellendi.', 'success')
                                .then(() => {
                                    window.location.reload();
                                });
                        } else {
                            Swal.fire('Hata!', data.message || 'Bir hata oluştu.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Hata!', 'Bir hata oluştu.', 'error');
                    });
            });

            // Bakiye düzenleme
            const editBalanceButtons = document.querySelectorAll('.edit-balance');
            editBalanceButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const username = this.dataset.username;
                    const balance = this.dataset.balance;

                    document.getElementById('editBalanceUserID').value = id;
                    document.getElementById('editBalanceUsername').value = username;
                    document.getElementById('editBalanceCurrentBalance').value = parseFloat(balance).toFixed(2);
                });
            });

            // Bakiye düzenleme formu
            document.getElementById('editBalanceForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'edit_balance');

                Swal.fire({
                    title: 'Emin misiniz?',
                    text: "Bu işlem kullanıcının bakiyesini değiştirecek!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, Değiştir',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('user_actions.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Başarılı!',
                                        html: `
                                        Bakiye güncellendi.<br>
                                        <b>Yeni Bakiye:</b> $${data.newBalance}
                                    `,
                                        icon: 'success'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Hata!', data.message || 'Bir hata oluştu.', 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Hata!', 'Bir hata oluştu.', 'error');
                            });
                    }
                });
            });
        });
    </script>
</body>

</html>