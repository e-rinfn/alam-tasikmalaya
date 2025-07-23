<?php
session_start();
require_once '../config.php';

// Cek apakah user memiliki hak akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: no_access.php");
    exit;
}

// Inisialisasi variabel
$name = '';
$email = '';
$role = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? '';

    // Cek apakah email sudah digunakan
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Email sudah digunakan
        $_SESSION['error'] = "Email <strong>$email</strong> sudah digunakan. Silakan gunakan email lain.";
        // Simpan data ke session agar tidak hilang
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
        header("Location: add_account.php");
        exit;
    }

    // Email belum digunakan, insert user baru
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Akun berhasil ditambahkan.";
        unset($_SESSION['old']);
    } else {
        $_SESSION['error'] = "Terjadi kesalahan. Gagal menambahkan akun.";
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
    }

    header("Location: manage_account.php");
    exit;
}

// Ambil data lama jika ada
$old = $_SESSION['old'] ?? ['name' => '', 'email' => '', 'role' => 'user'];
unset($_SESSION['old']);
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

    <main class="container mt-4 p-4 rounded shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Tambah Pengguna</h3>
            <a href="manage_account.php" class="btn btn-secondary">Kembali</a>
        </div>

        <hr>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>



        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan nama"
                    value="<?= htmlspecialchars($old['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email"
                    value="<?= htmlspecialchars($old['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    <option value="user" <?= $old['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>

    </main>



    <?php include 'admin_footer.php'; ?>

</body>

</html>