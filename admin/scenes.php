<?php
session_start();
include '../config.php';

// Cek apakah user login dan memiliki role admin ATAU user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: no_access.php");
    exit;
}

// Ambil wisata_id dari parameter URL
$wisata_id = isset($_GET['wisata_id']) ? intval($_GET['wisata_id']) : null;

// Jika wisata_id tidak ada, redirect atau tampilkan pesan
if (!$wisata_id) {
    echo "<script>alert('Wisata tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

// Ambil nama wisata berdasarkan wisata_id
$stmt = $conn->prepare("SELECT name FROM wisata WHERE id = ?");
$stmt->bind_param("i", $wisata_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Wisata tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

$row = $result->fetch_assoc();
$wisata_name = $row['name'];
$stmt->close();

// Ambil daftar scene berdasarkan wisata_id
$stmt = $conn->prepare("SELECT * FROM scenes WHERE wisata_id = ?");
$stmt->bind_param("i", $wisata_id);
$stmt->execute();
$scenes = $stmt->get_result();
$stmt->close();
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

    <div class="container mt-3 p-3 mb-3 rounded" style="min-height: 80vh;">

        <!-- Tampilkan nama wisata yang dipilih -->
        <h3>Kelola Scene Riwayat Bencana - <?= htmlspecialchars($wisata_name) ?></h3>
        <hr>
        <!-- Container untuk tombol Tambah Scene dan Kembali -->
        <div class="button-container">
            <a href="../index_admin.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="add_scene.php?wisata_id=<?= $wisata_id ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Tambah Scene
            </a>
        </div>

        <?php
        // Pastikan koneksi database sudah ada di $conn
        $wisata_id = $_GET['wisata_id']; // Ambil ID wisata dari parameter URL

        // Perbaiki query SQL
        $query = "SELECT scenes.id, scenes.name, scenes.panorama, 
                    COUNT(hotspots.id) AS total_hotspot, 
                    GROUP_CONCAT(hotspots.text SEPARATOR ', ') AS hotspot_names
            FROM scenes
            LEFT JOIN hotspots ON scenes.id = hotspots.scene_id
            WHERE scenes.wisata_id = ?  -- Filter berdasarkan wisata_id
            GROUP BY scenes.id";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $wisata_id);
        $stmt->execute();
        $scenes = $stmt->get_result();
        ?>



        <!-- Tampilkan daftar scene -->
        <div class="table-responsive mt-3 bg-white">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center w-25">Nama Scene</th>
                        <th class="text-center w-25">Gambar Panorama</th>
                        <th class="text-center w-25">Hotspot Scene</th>
                        <th class="text-center w-25">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($scenes->num_rows > 0): ?>
                        <?php $no = 1;
                        while ($scene = $scenes->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++ ?></td>
                                <td class="align-middle"><?= htmlspecialchars($scene['name']) ?></td>
                                <td class="text-center align-middle">
                                    <img src="<?= htmlspecialchars($scene['panorama']) ?>" class="img-thumbnail" width="500" alt="Panorama">
                                </td>
                                <td class="text-center">
                                    <hr>
                                    <b>Total Hotspot: </b>
                                    <?= $scene['total_hotspot'] ?>
                                    <br>
                                    <hr>
                                    <div class="text-start">
                                        <?php if ($scene['hotspot_names']): ?>
                                            <ul class="list-styled"><b>Nama Hotspot: </b>
                                                <?php foreach (explode(',', $scene['hotspot_names']) as $hotspot_name): ?>
                                                    <li><?= htmlspecialchars(trim($hotspot_name)) ?></li>
                                                <?php endforeach; ?>
                                                </u>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <a href="view_tour.php?wisata_id=<?= $wisata_id ?>&scene_id=<?= $scene['id'] ?>" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-eye"></i> Lihat Scene
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="edit_scene.php?id=<?= $scene['id'] ?>" class="btn btn-warning btn-sm w-100">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="hotspots.php?scene_id=<?= $scene['id'] ?>" class="btn btn-info btn-sm w-100">
                                                <i class="bi bi-gear"></i> Hotspot
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#" class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-href="hapus_scene.php?id=<?= $scene['id'] ?>">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </div>


                                    <!-- Modal Konfirmasi -->
                                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="confirmDeleteLabel">Konfirmasi Hapus</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">Apakah Anda yakin ingin menghapus scene ini?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Ya, Hapus</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Script untuk Menyesuaikan URL -->
                                    <script>
                                        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
                                        confirmDeleteModal.addEventListener('show.bs.modal', function(event) {
                                            var button = event.relatedTarget; // Tombol yang diklik
                                            var href = button.getAttribute('data-href'); // Ambil URL dari data-href
                                            var confirmButton = document.getElementById('confirmDeleteBtn');
                                            confirmButton.setAttribute('href', href); // Set URL di tombol hapus
                                        });
                                    </script>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="bi bi-exclamation-circle"></i> Belum ada scene.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-success text-white py-4 mt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; 2025 Riwayat Bencana</p>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>