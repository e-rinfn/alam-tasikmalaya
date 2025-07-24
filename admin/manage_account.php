<?php
session_start();
include '../config.php';

// Cek apakah user memiliki hak akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: no_access.php");
    exit;
}

// Ambil daftar akun
$sql = "SELECT id, name, email, role FROM users";
$result = $conn->query($sql);

// Notifikasi jika ada pesan
$successMessage = isset($_SESSION['message']) ? $_SESSION['message'] : "";
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Bencana</title>
    <link rel="icon" type="image/png" href="../img/Logo-Putih.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'admin_header.php'; ?>

    <main class="container mt-4 p-4 rounded bg-white shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Kelola Pengguna</h3>
            <a href="add_account.php" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Tambah Akun
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td class="text-center"><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $row['role'] === 'admin' ? 'success' : 'secondary'; ?>">
                                    <?= $row['role']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit_account.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include 'admin_footer.php'; ?>

    <!-- SweetAlert for Delete Confirmation -->
    <script>
        function confirmDelete(userId) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_account.php?id=' + userId;
                }
            });
        }

        <?php if (!empty($successMessage)) : ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= addslashes($successMessage); ?>',
                timer: 2500,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>

</body>

</html>