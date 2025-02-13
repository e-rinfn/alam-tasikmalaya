<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil daftar wisata
$wisata = $conn->query("SELECT * FROM wisata");

// Jika wisata dipilih, ambil daftar scene yang terkait
$wisata_id = isset($_GET['wisata_id']) ? $_GET['wisata_id'] : null;
$scenes = ($wisata_id) ? $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id") : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Scene</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Bootstrap JS & Popper.js (Wajib untuk Dropdown) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-secondary">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand text-white" href="index.php">Alam Tasikmalaya 360</a>      

        <!-- Menu Navbar -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Dropdown -->
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle text-white" style="text-decoration: none;" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> ADMINISTRATOR
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-3" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="akun.php"><i class="bi bi-person"></i> Akun</a></li>
                        <li><a class="dropdown-item" href="petunjuk.php"><i class="bi bi-book"></i> Petunjuk Penggunaan</a></li>
                        <li><a class="dropdown-item" href="tentang.php"><i class="bi bi-info-circle"></i> Tentang</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="container mt-4">
    <h2>Kelola Scene</h2>
    
    <!-- Pilih Wisata -->
    <form method="GET">
        <div class="mb-3">
            <label for="wisata_id" class="form-label">Pilih Wisata</label>
            <select class="form-control" name="wisata_id" id="wisata_id" onchange="this.form.submit()">
                <option value="">-- Pilih Wisata --</option>
                <?php while ($row = $wisata->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" <?= ($wisata_id == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['name'] ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </form>

    <!-- Tampilkan daftar scene jika wisata dipilih -->
    <?php if ($wisata_id && $scenes): ?>
        <a href="add_scene.php?wisata_id=<?= $wisata_id ?>" class="btn btn-success mb-3">Tambah Scene</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Scene</th>
                    <th>Gambar Panorama</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($scenes->num_rows > 0): ?>
                    <?php $no = 1; while ($scene = $scenes->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $scene['name'] ?></td>
                            <td><img src="<?= $scene['panorama'] ?>" width="300"></td>
                            <td>
                                <a href="edit_scene.php?id=<?= $scene['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_scene.php?id=<?= $scene['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus scene ini?')">Hapus</a>
                                <a href="manage_hotspots.php?scene_id=<?= $scene['id'] ?>" class="btn btn-info btn-sm">Kelola Hotspot</a> <!-- Tombol Kelola Hotspot -->
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Belum ada scene</td></tr>
                <?php endif; ?>
            </tbody>

        </table>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary">Kembali</a>
</div>

</body>
</html>
