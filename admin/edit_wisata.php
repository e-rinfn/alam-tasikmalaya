<?php
session_start();
include '../config.php';

// Cek apakah user login dan memiliki role admin ATAU user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: no_access.php");
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
// $query = "SELECT * FROM wisata WHERE id = ? AND user_id = ?";
$query = "SELECT * FROM wisata WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Wisata tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit;
}

$wisata = $result->fetch_assoc();
$stmt->close();

// Inisialisasi error
$errors = [];
$success = "";

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $image_url = $wisata['image_url']; // Default gambar yang sudah ada

    // Validasi input kosong
    if (empty($name)) $errors[] = "Nama wisata tidak boleh kosong!";
    if (empty($description)) $errors[] = "Deskripsi wisata tidak boleh kosong!";
    if (empty($location)) $errors[] = "Lokasi wisata tidak boleh kosong!";

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

    // Jika tidak ada error, update database
    if (empty($errors)) {
        $query = "UPDATE wisata SET name = ?, description = ?, location = ?, image_url = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssii", $name, $description, $location, $image_url, $id, $user_id);

        if ($stmt->execute()) {
            $success = "Wisata berhasil diupdate!";
        } else {
            $errors[] = "Gagal mengupdate wisata!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Bencana</title>
    <link rel="icon" type="image/png" href="../img/Logo-Putih.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'admin_header.php'; ?>

    <main class="container mt-4 p-3 mb-3 rounded">
        <h3>Edit Wisata - <?= htmlspecialchars($wisata['name']) ?></h3>
        <hr>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($wisata['id']) ?>">

            <div class="d-flex justify-content-around">
                <!-- <div class="mb-3">
                <label for="location" class="form-label">Lokasi</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($wisata['location']) ?>" required>
            </div> -->

                <div class="mb-3 p-3 w-50">
                    <label for="name" class="form-label">Nama Wisata</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($wisata['name']) ?>" required>
                    <hr>

                    <label for="location" class="form-label mt-5">Lokasi</label>
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
            </div>
            <hr>
            <div class="mb-3 text-center">
                <label for="description" class="form-label">Deskripsi Wisata</label>
                <textarea class="form-control" id="description" name="description" style="height: 200px;" required><?= htmlspecialchars($wisata['description']) ?></textarea>
            </div>

            <script>
                ClassicEditor
                    .create(document.querySelector('#description'), {
                        toolbar: [
                            'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'
                        ],
                        height: '500px' // Atur tinggi editor
                    })
                    .catch(error => {
                        console.error(error);
                    });
            </script>


            <a href="../index_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Beranda</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> Simpan</button>
        </form>
        <br>
    </main>

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
    <?php include 'admin_footer.php'; ?>

</body>

</html>