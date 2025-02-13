<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil ID scene dari parameter URL
$scene_id = isset($_GET['scene_id']) ? intval($_GET['scene_id']) : 0;
if ($scene_id === 0) {
    echo "<script>alert('Scene tidak ditemukan!'); window.location.href='scenes.php';</script>";
    exit;
}

// Ambil data wisata_id dari scene yang sedang dikelola
$scene = $conn->query("SELECT * FROM scenes WHERE id = $scene_id")->fetch_assoc();
$wisata_id = $scene['wisata_id']; // Ambil wisata_id dari scene yang sedang dikelola

// Ambil data hotspot yang terkait dengan scene
$hotspots = $conn->query("SELECT * FROM hotspots WHERE scene_id = $scene_id");

// Ambil data scene untuk judul halaman
$scene = $conn->query("SELECT * FROM scenes WHERE id = $scene_id")->fetch_assoc();
if (!$scene) {
    echo "<script>alert('Scene tidak ditemukan!'); window.location.href='scenes.php';</script>";
    exit;
}

// Pastikan path gambar panorama valid
$panorama_path = !empty($scene['panorama']) ? $scene['panorama'] : '';

// Proses tambah hotspot baru
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pitch = $_POST['pitch'];
    $yaw = $_POST['yaw'];
    $type = $_POST['type'];
    $text = $_POST['text'];
    $target_scene_id = $_POST['target_scene_id'];
    $description = $_POST['description'];

    // Debugging: Cetak data yang diterima
    // echo "<pre>";
    // print_r($_POST);
    // echo "</pre>";

    // Masukkan data hotspot baru ke database
    $query = "INSERT INTO hotspots (scene_id, pitch, yaw, type, text, target_scene_id, description) 
              VALUES ('$scene_id', '$pitch', '$yaw', '$type', '$text', '$target_scene_id', '$description')";

    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Hotspot berhasil ditambahkan!'); window.location.href='hotspots.php?scene_id=$scene_id';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan hotspot!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hotspot - <?= htmlspecialchars($scene['name']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- Pannellum CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.css">

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <!-- Bootstrap JS & Pannellum JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js"></script>



</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-secondary">
    <div class="container">
        <a class="navbar-brand text-white" href="index.php">Alam Tasikmalaya 360</a>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
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

<!-- Main Content -->
<div class="container mt-4">
    <h3>Kelola Hotspot untuk Scene: <?= htmlspecialchars($scene['name']) ?></h3>
<hr>
    <div class="row">
        <!-- Form Tambah Hotspot -->
        <div class="col-md-5">
            <h4 class="mt-4">Tambah Hotspot</h4>

            <form method="POST">
    <div class="mb-3" hidden>
        <label class="form-label">Scene</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($scene['name']) ?>" readonly>
        <input type="hidden" name="scene_id" value="<?= $scene_id ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Jenis Hotspot</label>
        <select name="type" id="typeSelect" class="form-select" required>
            <option value="scene">Navigasi ke Scene Lain</option>
            <option value="info">Informasi</option>
        </select>
    </div>

    <!-- Dropdown untuk navigasi ke scene lain -->
    <div id="sceneTarget" class="mt-3">
        <label class="form-label">Pindah ke Scene</label>
        <select class="form-select" id="editTargetSceneId" name="target_scene_id" required>
            <option value="">Pilih Scene Tujuan</option>
            <?php
            // Ambil scene hanya dari wisata tertentu
            $scenes = $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id");
            while ($scene = $scenes->fetch_assoc()) { ?>
                <option value="<?= $scene['id']; ?>"><?= $scene['name']; ?></option>
            <?php } ?>
        </select>

        <label class="form-label mt-3">Target Yaw</label>
        <input type="number" name="targetYaw" step="0.01" class="form-control">
    </div>

    <div class="mt-3">
        <label class="form-label">Label Hotspot (Maju, Informasi, dll)</label>
        <input type="text" name="text" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label class="form-label">Yaw (Posisi Horizontal)</label>
            <input type="number" name="yaw" step="0.01" class="form-control" id="inputYaw" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Pitch (Posisi Vertikal)</label>
            <input type="number" name="pitch" step="0.01" class="form-control" id="inputPitch" required>
        </div>
    </div>

    <!-- Deskripsi informasi -->
    <div id="infoTarget" class="mt-3 text-center" style="display: none;">
        <label for="description" class="form-label">Deskripsi Wisata</label>
        <textarea class="form-control" id="description" name="description" style="height: 200px;"></textarea>
    </div>

    

    <button type="submit" class="btn btn-success w-100 mt-3"><i class="bi bi-floppy"></i> - Tambah Hotspot</button>
</form>


            <!-- JavaScript untuk menampilkan/menyembunyikan elemen berdasarkan jenis hotspot -->
            <script>
                document.getElementById("typeSelect").addEventListener("change", function () {
                    const type = this.value;
                    document.getElementById("sceneTarget").style.display = (type === "scene") ? "block" : "none";
                    document.getElementById("infoTarget").style.display = (type === "info") ? "block" : "none";
                });
            </script>
        </div>

<style>
    #panorama-container {
        position: relative;
        width: 100%;
        height: 50vh;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }

    #panorama {
        width: 100%;
        height: 100%;
    }

    .crosshair {
        position: absolute;
        z-index: 999;
        width: 2px;
        height: 100%;
        background-color: rgba(255, 0, 0, 0.5);
    }

    .crosshair.horizontal {
        width: 100%;
        height: 2px;
    }
</style>

<!-- Panorama Viewer dan Kontrol -->
<div class="col-md-7">
    <h4 class="text-center mb-4">Cari Titik Koordinat</h4>
    <div id="panorama-container">
        <div id="panorama"></div>
        <div class="crosshair vertical" style="left: 50%; top: 0;"></div>
        <div class="crosshair horizontal" style="top: 50%; left: 0;"></div>
    </div>
    <div class="text-center mt-3">
        <button id="setPointer" class="btn btn-primary mb-3"><i class="bi bi-crosshair"></i> - Set Pointer</button>
        <div class="text-muted">
            <p>Pitch: <span id="display-pitch">0</span></p>
            <p>Yaw: <span id="display-yaw">0</span></p>
        </div>
    </div>
</div>


<hr>

<!-- Daftar Hotspot -->
<h4 class="mt-4">Daftar Hotspot</h4>
<?php if ($hotspots->num_rows > 0): ?>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Text</th>
                <th class="text-center">Pitch</th>
                <th class="text-center">Yaw</th>
                <th class="text-center">Tipe</th>
                <th class="text-center">Deskripsi</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($hotspot = $hotspots->fetch_assoc()): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($hotspot['text']) ?></td>
                    <td><?= $hotspot['pitch'] ?></td>
                    <td><?= $hotspot['yaw'] ?></td>
                    <td><?= $hotspot['type'] ?></td>
                    <td><?= strip_tags($hotspot['description'], '<p><br><b><i><u><strong><em>') ?></td>
                    <td class="text-end">
                        <a href="edit_hotspot.php?id=<?= $hotspot['id'] ?>&scene_id=<?= $scene_id ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> - Edit Hotspot</a>

                        <!-- Tombol Hapus yang membuka modal -->
                        <a href="#" class="btn btn-danger btn-sm" 
                        data-bs-toggle="modal" 
                        data-bs-target="#confirmDeleteModal"
                        data-id="<?= $hotspot['id'] ?>" 
                        data-scene="<?= $scene_id ?>">
                        <i class="bi bi-trash"></i> - 
                        Hapus
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-center">Belum ada hotspot untuk scene ini.</p>
<?php endif; ?>


<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus hotspot ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="confirmDeleteButton" href="#" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var confirmDeleteModal = document.getElementById("confirmDeleteModal");
    confirmDeleteModal.addEventListener("show.bs.modal", function(event) {
        var button = event.relatedTarget; // Tombol yang diklik
        var hotspotId = button.getAttribute("data-id");
        var sceneId = button.getAttribute("data-scene");

        // Update link hapus di dalam modal
        var deleteLink = document.getElementById("confirmDeleteButton");
        deleteLink.href = "hapus_hotspot.php?id=" + hotspotId + "&scene_id=" + sceneId;
    });
});
</script>




<script>
    let viewer;
    let currentMarker = null;

    // Initialize viewer with default panorama
    function initializeViewer(panorama) {
        if (viewer) {
            viewer.destroy();
        }
        viewer = pannellum.viewer('panorama', {
            type: 'equirectangular',
            panorama: panorama,
            autoLoad: true,
            compass: false,
            showFullscreenCtrl: false,
        });

        // Tambahkan event listener untuk klik pada panorama
        viewer.on('#', function (event) {
            const pitch = viewer.mouseEventToCoords(event)[0]; // Mendapatkan nilai pitch
            const yaw = viewer.mouseEventToCoords(event)[1];   // Mendapatkan nilai yaw

            // Isi nilai pitch dan yaw ke dalam form
            document.getElementById('inputPitch').value = pitch.toFixed(2);
            document.getElementById('inputYaw').value = yaw.toFixed(2);

            // Tampilkan nilai pitch dan yaw di display
            document.getElementById('display-pitch').innerText = pitch.toFixed(2);
            document.getElementById('display-yaw').innerText = yaw.toFixed(2);
        });
    }

    // Initialize with default image
    <?php if (!empty($panorama_path)): ?>
        initializeViewer('<?= $panorama_path ?>');
    <?php else: ?>
        console.error('Path panorama tidak valid.');
    <?php endif; ?>

    // Handle set pointer button click
    document.getElementById('setPointer').addEventListener('click', function () {
        const pitch = viewer.getPitch();
        const yaw = viewer.getYaw();

        // Isi nilai pitch dan yaw ke dalam form
        document.getElementById('inputPitch').value = pitch.toFixed(2);
        document.getElementById('inputYaw').value = yaw.toFixed(2);

        // Tampilkan nilai pitch dan yaw di display
        document.getElementById('display-pitch').innerText = pitch.toFixed(2);
        document.getElementById('display-yaw').innerText = yaw.toFixed(2);

        // Hapus marker sebelumnya jika ada
        if (currentMarker) {
            viewer.removeMarker(currentMarker);
        }

        // Tambahkan marker baru
        currentMarker = viewer.addMarker({
            id: 'pointerMarker',
            pitch: pitch,
            yaw: yaw,
            cssClass: 'custom-marker',
            createTooltipFunc: markerTooltip,
            anchor: 'center',
        });
    });

    // Fungsi untuk membuat tooltip pada marker
    function markerTooltip(marker) {
        return `Pitch: ${marker.getPitch().toFixed(2)}, Yaw: ${marker.getYaw().toFixed(2)}`;
    }
</script>




</body>
</html>