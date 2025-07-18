<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
    <title>Halaman Akun Admin</title>
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
<body class="bg-light">

<?php include 'admin_header.php'; ?>
<div class="container mt-4 p-3 mb-3 rounded">
    <h3>Ubah Password - <b><?= htmlspecialchars($admin['name']); ?></b></h3>              
                <hr>

                <form action="update_password.php" method="post" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="old_password" class="form-control" placeholder="Masukkan password lama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Masukkan password baru" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Ubah Password</button>
                </form>
                
    </div>

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
