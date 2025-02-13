<?php
session_start();
include '../config.php';

// Ambil wisata_id dari parameter URL
$wisata_id = $_GET['wisata_id'] ?? null;

if (!$wisata_id) {
    echo "<script>alert('Wisata ID tidak valid!'); window.location.href='scenes.php';</script>";
    exit;
}

// Ambil nama wisata berdasarkan wisata_id
$wisata_query = $conn->prepare("SELECT name FROM wisata WHERE id = ?");
$wisata_query->bind_param("i", $wisata_id);
$wisata_query->execute();
$wisata_query->bind_result($wisata_name);
$wisata_query->fetch();
$wisata_query->close();

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $panorama = ''; // Default gambar kosong

    // Proses Upload Gambar
    if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] == 0) {
        $target_dir = "../img/panorama360/";
        $file_name = time() . "_" . basename($_FILES["panorama"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi Gambar
        if (getimagesize($_FILES["panorama"]["tmp_name"]) && $_FILES["panorama"]["size"] <= 5000000 && in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            if (move_uploaded_file($_FILES["panorama"]["tmp_name"], $target_file)) {
                $panorama = $target_file; // Simpan path gambar yang baru
            }
        }
    }

    // Insert Scene ke Database
    $query = "INSERT INTO scenes (name, wisata_id, panorama) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $name, $wisata_id, $panorama);

    if ($stmt->execute()) {
        header("Location: scenes.php?wisata_id=$wisata_id");
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan scene!');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Scene</title>

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

<?php include 'admin_header.php'; ?>

<div class="container mt-3" style="min-height: 80vh;">
    <h3>Tambah Scene Wisata - <?= htmlspecialchars($wisata_name) ?></h3>
    <hr>
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="d-flex justify-content-around">  
            <div class="mb-3 p-3 w-50">
                <label for="name" class="form-label">Nama Scene</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <hr>
                <label for="wisata_name" class="form-label">Wisata</label>
                <input type="text" class="form-control" id="wisata_name" value="<?= htmlspecialchars($wisata_name) ?>" readonly>
                <input type="hidden" name="wisata_id" value="<?= htmlspecialchars($wisata_id) ?>">
            </div>

            <div class="mb-3 p-3">
                <label for="panorama" class="form-label">Gambar Panorama (Opsional)</label>
                <input type="file" class="form-control" id="panorama" name="panorama">
                <small class="form-text text-muted">Hanya JPG, JPEG, PNG. Maksimal 5MB.</small>
            </div>
        </div>
        <hr>
        <a href="scenes.php?wisata_id=<?= $wisata_id ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> - Simpan Scene</button>
        <br>
    </form>
</div>
</body>
</html>