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

// Ambil ID dari GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID wisata tidak valid!'); window.location.href='dashboard.php';</script>";
    exit;
}

$id = $_GET['id'];

// Ambil data wisata untuk ditampilkan di form
$query = "SELECT * FROM wisata WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Wisata tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit;
}

$wisata = $result->fetch_assoc();
$stmt->close();

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $image_url = $wisata['image_url']; // Default gambar yang sudah ada

    // Proses upload gambar jika ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $image_size = $_FILES['image']['size'];

        // Validasi ukuran file (maks 2MB)
        if ($image_size > 2 * 1024 * 1024) {
            echo "<script>alert('Ukuran gambar terlalu besar! Maksimal 2MB.');</script>";
            exit;
        }

        // Validasi ekstensi file (hanya gambar)
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_ext, $valid_ext)) {
            echo "<script>alert('Format gambar tidak valid! Hanya JPG, JPEG, PNG, atau GIF yang diperbolehkan.');</script>";
            exit;
        }

        // Tentukan nama file baru dan pindahkan gambar ke folder uploads
        $upload_dir = '../img/thumbnail/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $new_image_name = uniqid() . '.' . $image_ext;
        if (move_uploaded_file($image_tmp, $upload_dir . $new_image_name)) {
            $image_url = 'img/thumbnail/' . $new_image_name; // Simpan path gambar relatif
        } else {
            echo "<script>alert('Gagal mengunggah gambar!');</script>";
            exit;
        }
    }

    // Update data wisata di database
    $query = "UPDATE wisata SET name = ?, description = ?, location = ?, image_url = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssii", $name, $description, $location, $image_url, $id, $user_id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Wisata berhasil diupdate!');
            window.location.href='edit_wisata.php?id=" . $_GET['id'] . "';
        </script>";
    } else {
        echo "<script>alert('Gagal mengupdate wisata!');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wisata</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>


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

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<!-- Bootstrap JS & Popper.js (Wajib untuk Dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<div class="container mt-4 p-3" style="min-height: 80vh;">
    <h2>Edit Wisata</h2>
    <hr>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($wisata['id']) ?>">
        
        <div class="d-flex justify-content-around">
            <!-- <div class="mb-3">
                <label for="location" class="form-label">Lokasi</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($wisata['location']) ?>" required>
            </div> -->

            <div class="mb-3">
                <label for="name" class="form-label">Nama Wisata</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($wisata['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Lokasi</label>
                <input type="text" class="form-control" id="location" name="location" 
                    value="<?= htmlspecialchars($wisata['location']) ?>" required>

                <!-- Link ke Google Maps -->
                <small class="form-text">
                    <a href="https://www.google.com/maps/search/?q=<?= urlencode($wisata['location']) ?>" 
                    target="_blank" class="d-block mt-2">
                        <i class="bi bi-geo-alt"></i> Lihat di Google Maps
                    </a>
                </small>
            </div>


            <div class="mb-3">
                <label for="image" class="form-label">Gambar (URL atau Upload)</label>
                <input type="file" class="form-control" id="image" name="image">
                <small class="form-text text-muted">Maksimal ukuran file 2MB. Jika ingin mengubah gambar, unggah gambar baru.</small>
                <?php if (!empty($wisata['image_url'])): ?>
                    <div class="mt-2">
                        <img src="<?= htmlspecialchars('../' . $wisata['image_url']) ?>" alt="Gambar Wisata" class="img-thumbnail" width="150">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($wisata['description']) ?></textarea>
        </div>

        <script>
            ClassicEditor
                .create(document.querySelector('#description'))
                .catch(error => {
                    console.error(error);
                });
        </script>
        
        

        <button type="submit" class="btn btn-success">Save</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>

</div>

<!-- Footer -->
<footer style="flex-shrink: 0;">
    <p>&copy; 2025 Erin Fajrin Nugraha - Alam Tasikmalaya 360.</p>
</footer>




</body>
</html>
