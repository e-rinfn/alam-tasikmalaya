<?php
session_start();
require '../config.php';

// Cek apakah user login dan memiliki role admin ATAU user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: no_access.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Ambil pesan dari session jika ada
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : "";

// Hapus pesan dari session setelah ditampilkan
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Bencana</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="icon" type="image/png" href="../img/Logo-Putih.png">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">


</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'admin_header.php'; ?>
    <main class="container mt-5 mb-4">
        <h1 class="mb-0 fs-3">Ubah Password</h1>
        <hr>
        <a href="javascript:history.go(-1)" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <!-- Card Ubah Password -->
                    <div class="shadow-sm border-0">
                        <div class="card-header bg-success text-white p-3">
                            <h5 class="mb-0"><i class="bi bi-key me-2"></i>Ubah Password - <b><?= htmlspecialchars($admin['name']); ?></b></h5>
                        </div>
                        <div class="card-body">
                            <form action="update_password.php" method="post">
                                <div class="mb-3">
                                    <label class="form-label">Password Lama</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="old_password" class="form-control" placeholder="Masukkan password lama" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                        <input type="password" name="new_password" class="form-control" placeholder="Masukkan password baru" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-25">
                                    <i class="bi bi-arrow-repeat"></i> Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'admin_footer.php'; ?>

    <!-- Modal Notifikasi -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header <?= $message_type === 'success' ? 'bg-success' : 'bg-danger' ?> text-white">
                    <h5 class="modal-title" id="messageModalLabel"><?= $message_type === 'success' ? 'Berhasil' : 'Error' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= htmlspecialchars($message); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk menampilkan modal jika ada pesan -->
    <script>
        var message = "<?= $message ?>";
        if (message !== "") {
            var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        }
    </script>
</body>

</html>