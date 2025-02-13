<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $image_url = null; // Default null jika tidak ada gambar diunggah

    // Proses upload gambar jika ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $image_size = $_FILES['image']['size'];

        // Validasi ukuran file (maks 2MB)
        if ($image_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran gambar terlalu besar! Maksimal 2MB.";
        }

        // Validasi ekstensi file (hanya gambar)
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_ext, $valid_ext)) {
            $errors[] = "Format gambar tidak valid! Hanya JPG, JPEG, PNG, atau GIF.";
        }

        // Jika validasi berhasil, simpan gambar
        if (empty($errors)) {
            $upload_dir = '../img/thumbnail/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_image_name = uniqid() . '.' . $image_ext;
            if (move_uploaded_file($image_tmp, $upload_dir . $new_image_name)) {
                $image_url = 'img/thumbnail/' . $new_image_name; // Simpan path gambar relatif
            } else {
                $errors[] = "Gagal mengunggah gambar!";
            }
        }
    }

    // Simpan ke database dengan user_id, deskripsi, lokasi, dan image_url
    $query = "INSERT INTO wisata (name, description, location, user_id, image_url) 
              VALUES ('$name', '$description', '$location', '$user_id', '$image_url')";

    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Wisata berhasil ditambahkan!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan wisata!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Wisata</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

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


<div class="container mt-3 p-3" style="min-height: 80vh;">
    <h3>Tambah Wisata</h3>
    <hr>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3 p-3 w-50">
            <label for="name" class="form-label">Nama Wisata</label>
            <input type="text" class="form-control" id="name" name="name" required>
<hr>
        
            <label for="location" class="form-label mt-5">Lokasi</label>
            <input type="text" class="form-control" id="location" name="location" required>

            <!-- Link ke Google Maps -->
            <small class="form-text">
                <a href="https://www.google.com/maps/search/?q=<?= urlencode($wisata['location']) ?>" 
                target="_blank" class="d-block mt-2">
                    <i class="bi bi-geo-alt"></i> Lihat di Google Maps
                </a>
            </small>
        </div>

        <div class="mb-3 p-3">
            <label for="image" class="form-label">Gambar (URL atau Upload)</label>
            <input type="file" class="form-control" id="image" name="image">
            <small class="form-text text-muted">Maksimal ukuran file 2MB. Jika ingin mengubah gambar, unggah gambar baru.</small>
            <?php if (!empty($wisata['image_url'])): ?>
                <div class="mt-2 text-center">
                    <img src="<?= htmlspecialchars('../' . $wisata['image_url']) ?>" alt="Gambar Wisata" class="img-thumbnail" width="300">
                </div>
            <?php endif; ?>
        </div>

        <!-- <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div> -->

        <div class="mb-3 text-center">
            <label for="description" class="form-label">Deskripsi Wisata</label>
            <textarea class="form-control" id="description" name="description" style="height: 200px;" required></textarea>
        </div>

        <script>
            ClassicEditor
                .create(document.querySelector('#description'))
                .catch(error => {
                    console.error(error);
                });
        </script>

        
        <button type="submit" class="btn btn-success">Tambah</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<!-- Bootstrap Modal untuk Error -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">Terjadi Kesalahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?= $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal untuk Sukses -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Berhasil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= $success; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="redirectPage()">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk Menampilkan Modal -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($errors)) : ?>
            var errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
            errorModal.show();
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            var successModal = new bootstrap.Modal(document.getElementById("successModal"));
            successModal.show();
        <?php endif; ?>
    });

    function redirectPage() {
        window.location.href = "edit_wisata.php?id=<?= $id ?>";
    }
</script>

<!-- Footer -->
<footer style="flex-shrink: 0;">
    <p>&copy; 2025 Erin Fajrin Nugraha - Alam Tasikmalaya 360.</p>
</footer>


</body>
</html>
