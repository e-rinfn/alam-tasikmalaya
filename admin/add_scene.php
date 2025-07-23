<?php
session_start();
include '../config.php';

// Cek apakah user login dan memiliki role admin ATAU user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: no_access.php");
    exit;
}


// Ambil wisata_id dari parameter URL
$wisata_id = $_GET['wisata_id'] ?? null;
$errors = []; // Array untuk menyimpan error

if (!$wisata_id) {
    $errors[] = "Wisata ID tidak valid!";
} else {
    // Ambil nama wisata berdasarkan wisata_id
    $wisata_query = $conn->prepare("SELECT name FROM wisata WHERE id = ?");
    $wisata_query->bind_param("i", $wisata_id);
    $wisata_query->execute();
    $wisata_query->bind_result($wisata_name);
    $wisata_query->fetch();
    $wisata_query->close();
}

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $panorama = ''; // Default gambar kosong

    // Validasi nama scene
    if (empty($name)) {
        $errors[] = "Nama scene tidak boleh kosong!";
    }

    // Proses Upload Gambar
    //     if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] == 0) {
    //         $target_dir = "../img/panorama360/";
    //         $file_name = time() . "_" . basename($_FILES["panorama"]["name"]);
    //         $target_file = $target_dir . $file_name;
    //         $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    //         // Validasi Gambar
    //         if (!getimagesize($_FILES["panorama"]["tmp_name"])) {
    //             $errors[] = "File yang diunggah bukan gambar!";
    //         } elseif ($_FILES["panorama"]["size"] > 6000000) {
    //             $errors[] = "Ukuran gambar terlalu besar! Maksimal 5MB.";
    //         } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
    //             $errors[] = "Format gambar harus JPG, JPEG, atau PNG!";
    //         } else {
    //             if (move_uploaded_file($_FILES["panorama"]["tmp_name"], $target_file)) {
    //                 $panorama = $file_name; // Simpan nama file saja
    //             } else {
    //                 $errors[] = "Gagal mengunggah gambar!";
    //             }
    //         }
    //     }

    // Proses Upload Gambar
    if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] == 0) {
        $target_dir = "../img/panorama360/";
        $file_name = time() . "_" . basename($_FILES["panorama"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi Gambar
        if (!getimagesize($_FILES["panorama"]["tmp_name"])) {
            $errors[] = "File yang diunggah bukan gambar!";
        } elseif ($_FILES["panorama"]["size"] > 6000000) {
            $errors[] = "Ukuran gambar terlalu besar! Maksimal 5MB.";
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            $errors[] = "Format gambar harus JPG, JPEG, atau PNG!";
        } else {
            if (move_uploaded_file($_FILES["panorama"]["tmp_name"], $target_file)) {
                $panorama = $target_file; // Simpan path lengkap
            } else {
                $errors[] = "Gagal mengunggah gambar!";
            }
        }
    }


    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $query = "INSERT INTO scenes (name, wisata_id, panorama) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sis", $name, $wisata_id, $panorama);

        if ($stmt->execute()) {
            header("Location: scenes.php?wisata_id=$wisata_id");
            exit;
        } else {
            $errors[] = "Gagal menambahkan scene!";
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

    <main class="container mt-4 p-3 mb-3 rounded">
        <h3>Tambah Scene Wisata - <?= htmlspecialchars($wisata_name) ?></h3>
        <hr>
        <form action="" method="POST" enctype="multipart/form-data">

            <div class="d-flex justify-content-around">
                <div class="mb-3 p-3 w-50">
                    <label for="name" class="form-label">Nama Scene</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    <hr>
                    <label for="wisata_name" class="form-label">Wisata</label>
                    <input type="text" class="form-control" id="wisata_name" value="<?= htmlspecialchars($wisata_name) ?>" readonly>
                    <input type="hidden" name="wisata_id" value="<?= htmlspecialchars($wisata_id) ?>">
                </div>

                <div class="mb-3 p-3">
                    <label for="panorama" class="form-label">Gambar Panorama 360</label>
                    <input type="file" class="form-control" id="panorama" name="panorama">
                    <small class="form-text text-muted">Hanya JPG, JPEG, PNG. Maksimal 5MB.</small>
                </div>
            </div>
            <hr>
            <a href="scenes.php?wisata_id=<?= $wisata_id ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> - Simpan Scene</button>
            <br>
        </form>
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
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Tampilkan Modal Error jika Ada -->
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