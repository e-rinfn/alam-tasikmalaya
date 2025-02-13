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
    $description = $_POST['description'];

    // Masukkan data hotspot baru ke database
    $query = "INSERT INTO hotspots (scene_id, pitch, yaw, type, text, description) 
              VALUES ('$scene_id', '$pitch', '$yaw', '$type', '$text', '$description')";

    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Hotspot berhasil ditambahkan!'); window.location.href='manage_hotspots.php?scene_id=$scene_id';</script>";
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
    <h2>Kelola Hotspot untuk Scene: <?= htmlspecialchars($scene['name']) ?></h2>

    <div class="row">
        <!-- Form Tambah Hotspot -->
        <div class="col-md-4">
            <h4 class="mt-4">Tambah Hotspot</h4>
            <form method="POST">
                <div class="mb-3">
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

                <div class="mb-3">
                    <label class="form-label">Label Hotspot</label>
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

                <!-- Dropdown untuk navigasi ke scene lain -->
                <div id="sceneTarget" class="mt-3">
                    <label class="form-label">Pindah ke Scene</label>
                    <select name="target_scene_id" class="form-select">
                        <option value="">Pilih Scene Tujuan</option>
                        <?php
                        // Ambil data scene dari database
                        $scenes = $conn->query("SELECT * FROM scenes");
                        while ($scene = $scenes->fetch_assoc()) { ?>
                            <option value="<?= $scene['id']; ?>"><?= $scene['name']; ?></option>
                        <?php } ?>
                    </select>

                    <label class="form-label mt-2">Target Yaw</label>
                    <input type="number" name="targetYaw" step="0.01" class="form-control">
                </div>

                <!-- Deskripsi informasi -->
                <div id="infoTarget" class="mt-3" style="display: none;">
                    <label class="form-label">Deskripsi Informasi</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3">Tambah Hotspot</button>
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

        <!-- Panorama Viewer dan Kontrol -->
        <div class="col-md-8">
            <h3 class="text-center mb-4">Cari Titik Koordinat</h3>
            <div id="panorama-container">
                <div id="panorama"></div>
                <div class="crosshair vertical" style="left: 50%; top: 0;"></div>
                <div class="crosshair horizontal" style="top: 50%; left: 0;"></div>
            </div>
            <div class="text-center mt-3">
                <button id="setPointer" class="btn btn-primary mb-3">Set Pointer</button>
                <div class="text-muted">
                    <p>Pitch: <span id="display-pitch">0</span></p>
                    <p>Yaw: <span id="display-yaw">0</span></p>
                </div>
            </div>
        </div>
    </div>

<!-- Daftar Hotspot -->
<h4 class="mt-4">Daftar Hotspot</h4>
<?php if ($hotspots->num_rows > 0): ?>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Text</th>
                <th>Pitch</th>
                <th>Yaw</th>
                <th>Deskripsi</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($hotspot = $hotspots->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($hotspot['text']) ?></td>
                    <td><?= $hotspot['pitch'] ?></td>
                    <td><?= $hotspot['yaw'] ?></td>
                    <td><?= nl2br(htmlspecialchars($hotspot['description'])) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                                data-id="<?= $hotspot['id'] ?>" 
                                data-pitch="<?= $hotspot['pitch'] ?>" 
                                data-yaw="<?= $hotspot['yaw'] ?>" 
                                data-type="<?= $hotspot['type'] ?>" 
                                data-text="<?= htmlspecialchars($hotspot['text']) ?>" 
                                data-description="<?= htmlspecialchars($hotspot['description']) ?>"
                                data-target-scene-id="<?= $hotspot['target_scene_id'] ?>"
                                data-target-yaw="<?= $hotspot['target_yaw'] ?>">
                            Edit
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $hotspot['id'] ?>">Hapus</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-center">Belum ada hotspot untuk scene ini.</p>
<?php endif; ?>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus hotspot ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="deleteButton" href="#" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Hotspot -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Hotspot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="edit_hotspot.php" method="POST">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label for="editPitch" class="form-label">Pitch</label>
                        <input type="number" step="0.1" class="form-control" id="editPitch" name="pitch" required>
                    </div>
                    <div class="mb-3">
                        <label for="editYaw" class="form-label">Yaw</label>
                        <input type="number" step="0.1" class="form-control" id="editYaw" name="yaw" required>
                    </div>
                    <div class="mb-3">
                        <label for="editType" class="form-label">Tipe</label>
                        <select class="form-select" id="editType" name="type" required>
                            <option value="info">Info</option>
                            <option value="scene">Scene</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editText" class="form-label">Teks</label>
                        <input type="text" class="form-control" id="editText" name="text" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="4" required></textarea>
                    </div>
                    <!-- Input untuk Target Yaw dan Scene Tujuan -->
                    <div id="editSceneTarget" style="display: none;">
                        <div class="mb-3">
                            <label for="editTargetSceneId" class="form-label">Scene Tujuan</label>
                            <select class="form-select" id="editTargetSceneId" name="target_scene_id">
                                <option value="">Pilih Scene Tujuan</option>
                                <?php
                                $scenes = $conn->query("SELECT * FROM scenes");
                                while ($scene = $scenes->fetch_assoc()) { ?>
                                    <option value="<?= $scene['id']; ?>"><?= $scene['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editTargetYaw" class="form-label">Target Yaw</label>
                            <input type="number" step="0.1" class="form-control" id="editTargetYaw" name="target_yaw">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk Modal Edit -->
<script>
    // Handle modal edit show event
    document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Tombol yang memicu modal
        const id = button.getAttribute('data-id');
        const pitch = button.getAttribute('data-pitch');
        const yaw = button.getAttribute('data-yaw');
        const type = button.getAttribute('data-type');
        const text = button.getAttribute('data-text');
        const description = button.getAttribute('data-description');
        const targetSceneId = button.getAttribute('data-target-scene-id');
        const targetYaw = button.getAttribute('data-target-yaw');

        // Isi form dengan data dari tombol
        document.getElementById('editId').value = id;
        document.getElementById('editPitch').value = pitch;
        document.getElementById('editYaw').value = yaw;
        document.getElementById('editType').value = type;
        document.getElementById('editText').value = text;
        document.getElementById('editDescription').value = description;
        document.getElementById('editTargetSceneId').value = targetSceneId;
        document.getElementById('editTargetYaw').value = targetYaw;

        // Tampilkan atau sembunyikan input target yaw dan scene tujuan berdasarkan tipe
        if (type === 'scene') {
            document.getElementById('editSceneTarget').style.display = 'block';
        } else {
            document.getElementById('editSceneTarget').style.display = 'none';
        }
    });

    // Handle perubahan tipe hotspot
    document.getElementById('editType').addEventListener('change', function () {
        const type = this.value;
        if (type === 'scene') {
            document.getElementById('editSceneTarget').style.display = 'block';
        } else {
            document.getElementById('editSceneTarget').style.display = 'none';
        }
    });
</script>

<!-- JavaScript untuk Modal Hapus -->
<script>
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Tombol yang memicu modal
        const id = button.getAttribute('data-id');
        const deleteButton = document.getElementById('deleteButton');
        deleteButton.href = `delete_hotspot.php?id=${id}`;
    });
</script>


<!-- Bootstrap JS & Pannellum JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js"></script>




<script>
    let viewer;

    // Initialize viewer with default panorama
    function initializeViewer(panorama) {
        if (viewer) {
            viewer.destroy();
        }
        viewer = pannellum.viewer('panorama', {
            type: 'equirectangular',
            panorama: panorama,
            autoLoad: true,
        });

        // Tambahkan event listener untuk klik pada panorama
        viewer.on('mousedown', function (event) {
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
    });
</script>

<!-- Footer -->
<footer style="flex-shrink: 0;">
    <p>&copy; 2025 Erin Fajrin Nugraha - Alam Tasikmalaya 360.</p>
</footer>
</body>
</html>