<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
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

<?php include 'admin_header.php'; ?>

<div class="container mt-3" style="min-height: 80vh;">
    
    <!-- Tampilkan nama wisata yang dipilih -->
    <h3>Kelola Scene Untuk Wisata - <?= htmlspecialchars($wisata_name) ?></h3>
    <hr>
    <!-- Container untuk tombol Tambah Scene dan Kembali -->
    <div class="button-container">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="add_scene.php?wisata_id=<?= $wisata_id ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Scene
        </a>
    </div>

    <!-- Tampilkan daftar scene -->
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-secondary">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nama Scene</th>
                    <th class="text-center">Gambar Panorama</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($scenes->num_rows > 0): ?>
                    <?php $no = 1; while ($scene = $scenes->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($scene['name']) ?></td>
                            <td class="text-center" >
                                <img src="<?= htmlspecialchars($scene['panorama']) ?>" class="img-thumbnail" width="400" alt="Panorama">
                            </td>
                            <td class="text-center" >

                                <!-- Tombol Lihat Scene -->
                                <a href="view_tour.php?wisata_id=<?= $wisata_id ?>&scene_id=<?= $scene['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> Lihat Scene
                                </a>

                                <!-- Tombol Edit -->
                                <a href="edit_scene.php?id=<?= $scene['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <br>
                                <br>
                                <!-- Tombol Kelola Hotspot -->
                                <a href="hotspots.php?scene_id=<?= $scene['id'] ?>" class="btn btn-info btn-sm">
                                    <i class="bi bi-gear"></i> Kelola Hotspot
                                </a>

                                <!-- Tombol Hapus -->
                                <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-href="hapus_scene.php?id=<?= $scene['id'] ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>


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
                                    confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
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
                        <td colspan="4" class="text-center text-muted">
                            <i class="bi bi-exclamation-circle"></i> Belum ada scene.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
</body>
</html>