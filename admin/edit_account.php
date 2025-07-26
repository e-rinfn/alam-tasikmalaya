<?php
session_start();
$successMessage = isset($_SESSION['message']) ? $_SESSION['message'] : "";
unset($_SESSION['message']);

include '../config.php';

// Cek apakah user memiliki hak akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: no_access.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT name, email, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_info'])) {
        // Update Nama, Email, dan Role
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $role, $id);
        $stmt->execute();

        header("Location: manage_account.php");
        exit;
    } elseif (isset($_POST['update_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_password, $id);
        $stmt->execute();

        $_SESSION['message'] = "Password berhasil diperbarui.";
        header("Location: edit_account.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Benjana</title>
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

</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'admin_header.php'; ?>

    <main class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0 fs-3">Ubah Pengguna</h1>
        </div>

        <hr>
        <a href="manage_account.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>

        <form method="post" class="mt-4 p-3 rounded shadow-sm bg-light">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" name="update_info" class="btn btn-success"><i class="bi bi-floppy"></i> Simpan Perubahan</button>
        </form>

        <hr>

        <h4 class="text-center ">Ubah Password</h4>
        <form method="post" class="mt-4 p-3 rounded shadow-sm bg-light">
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <button type="submit" name="update_password" class="btn btn-warning"><i class="bi bi-arrow-up"></i> Update Password</button>
        </form>

    </main>



    <?php include 'admin_footer.php'; ?>

</body>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($successMessage)) : ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= addslashes($successMessage); ?>',
            timer: 2500,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>

</html>