<?php
session_start();
include '../config.php';

// Cek apakah user login dan memiliki role admin ATAU user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: no_access.php");
    exit;
}

$id = $_GET['id'];
$errors = []; // Array untuk menyimpan pesan kesalahan

// Ambil data scene berdasarkan ID
$query = "SELECT * FROM scenes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: scenes.php');
    exit;
}

$scene = $result->fetch_assoc();
$stmt->close();
$wisata_id = $scene['wisata_id'];
$name = $scene['name']; // Inisialisasi default

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']) ?? '';
    $wisata_id = $_POST['wisata_id'];
    $panorama = $scene['panorama']; // Gunakan gambar lama jika tidak diubah

    // Validasi input nama scene
    if (empty($name)) {
        $errors[] = "Nama Scene wajib diisi!";
    }

    // Proses Upload Gambar jika ada
    if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] == 0) {
        $target_dir = "../img/panorama360/";
        $file_name = time() . "_" . basename($_FILES["panorama"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!getimagesize($_FILES["panorama"]["tmp_name"])) {
            $errors[] = "File yang diunggah bukan gambar!";
        } elseif ($_FILES["panorama"]["size"] > 6000000) {
            $errors[] = "Ukuran gambar maksimal 5MB!";
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            $errors[] = "Format gambar hanya JPG, JPEG, dan PNG!";
        } else {
            if (move_uploaded_file($_FILES["panorama"]["tmp_name"], $target_file)) {
                $panorama = $target_file;
            } else {
                $errors[] = "Gagal mengunggah gambar!";
            }
        }
    }

    // Validasi wisata_id
    $check_wisata = $conn->prepare("SELECT id FROM wisata WHERE id = ?");
    $check_wisata->bind_param("i", $wisata_id);
    $check_wisata->execute();
    $check_wisata->store_result();

    if ($check_wisata->num_rows == 0) {
        $errors[] = "Wisata ID tidak valid!";
    }

    $check_wisata->close();

    // Jika tidak ada error, update data di database
    if (empty($errors)) {
        $query = "UPDATE scenes SET name = ?, wisata_id = ?, panorama = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisi", $name, $wisata_id, $panorama, $id);

        if ($stmt->execute()) {
            header("Location: scenes.php?wisata_id=$wisata_id");
            exit;
        } else {
            $errors[] = "Gagal memperbarui scene!";
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

    <?php
    // Ambil nama wisata berdasarkan wisata_id
    $wisata_query = $conn->prepare("SELECT name FROM wisata WHERE id = ?");
    $wisata_query->bind_param("i", $scene['wisata_id']);
    $wisata_query->execute();
    $wisata_query->bind_result($wisata_name);
    $wisata_query->fetch();
    $wisata_query->close();
    ?>

    <!-- Modal Error -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Terjadi Kesalahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <main class="container mt-4 p-3 mb-3 rounded">
        <h3>Edit Scene Wisata - <?= htmlspecialchars($wisata_name) ?></h3>
        <hr>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($scene['id']) ?>">
            <div class="d-flex justify-content-around">
                <div class="mb-3 p-3 w-50">
                    <label for="name" class="form-label">Nama Scene</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                    <hr>
                    <label for="wisata_name" class="form-label">Wisata</label>
                    <input type="text" class="form-control" id="wisata_name" value="<?= htmlspecialchars($wisata_name) ?>" readonly>
                    <input type="hidden" name="wisata_id" value="<?= htmlspecialchars($scene['wisata_id']) ?>">
                </div>
                <div class="mb-3 p-3">
                    <label for="panorama" class="form-label">Gambar Panorama 360</label>
                    <input type="file" class="form-control" id="panorama" name="panorama">
                    <small class="form-text text-muted">Hanya JPG, JPEG, PNG. Maksimal 5MB.</small>
                    <?php if (!empty($scene['panorama'])): ?>
                        <div class="mt-2 text-center">
                            <img src="<?= htmlspecialchars($scene['panorama']) ?>" alt="Panorama Scene" class="img-thumbnail" width="300">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <a href="scenes.php?wisata_id=<?= $wisata_id ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> Simpan Perubahan</button>
        </form>
    </main>

    <?php if (!empty($errors)) : ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        </script>
    <?php endif; ?>

    <?php include 'admin_footer.php'; ?>

</body>

</html>