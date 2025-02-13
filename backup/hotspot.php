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

// Ambil data scene untuk judul halaman dan URL gambar panorama
$scene = $conn->query("SELECT * FROM scenes WHERE id = $scene_id")->fetch_assoc();
if (!$scene) {
    echo "<script>alert('Scene tidak ditemukan!'); window.location.href='scenes.php';</script>";
    exit;
}

$panorama_url = $scene['panorama']; // Ambil URL panorama dari database

// Ambil data hotspot yang terkait dengan scene
$hotspots = $conn->query("SELECT * FROM hotspots WHERE scene_id = $scene_id");

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
    <title>Editor Panorama - <?= htmlspecialchars($scene['name']) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Pannellum CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.css">

    <style>
        #panorama-container {
            position: relative;
            width: 100%;
            height: 70vh;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        #panorama {
            width: 100%;
            height: 100%;
        }

        /* Crosshair styles */
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

<body class="bg-light">
    <div class="container py-4">
        <h3 class="text-center mb-4">Edit Hotspot - <?= htmlspecialchars($scene['name']) ?></h3>

        <!-- Panorama Viewer -->
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div id="panorama-container" class="position-relative"
                    style="height: 50vh; max-width: 600px; margin: 0 auto;">
                    <div id="panorama"></div>
                    <!-- Crosshair -->
                    <div class="crosshair vertical" style="left: 50%; top: 0;"></div>
                    <div class="crosshair horizontal" style="top: 50%; left: 0;"></div>
                </div>
            </div>

            <!-- Controls -->
            <div class="col-lg-4 text-center">
                <div class="p-3 bg-white border rounded shadow-sm">
                    <button id="setPointer" class="btn btn-primary mb-3 w-100">Set Pointer</button>
                    <div class="text-muted">
                        <p>Pitch: <span id="pitch">0</span></p>
                        <p>Yaw: <span id="yaw">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-4">Tambah Hotspot</h4>
    <form action="" method="POST">
        <div class="mb-3">
            <label for="pitch" class="form-label">Pitch</label>
            <input type="number" step="0.1" class="form-control" id="pitch" name="pitch" required>
        </div>
        <div class="mb-3">
            <label for="yaw" class="form-label">Yaw</label>
            <input type="number" step="0.1" class="form-control" id="yaw" name="yaw" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Tipe</label>
            <select class="form-select" id="type" name="type" required>
                <option value="info">Info</option>
                <option value="scene">Scene</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="text" class="form-label">Teks</label>
            <input type="text" class="form-control" id="text" name="text" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Tambah Hotspot</button>
    </form>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Pannellum JS -->
    <script src="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js"></script>

    <script>
        let viewer;
        let pointerHotspot = null;

        // Ambil URL panorama dari PHP
        const panoramaUrl = "<?= htmlspecialchars($panorama_url) ?>";

        // Fungsi untuk inisialisasi viewer dengan panorama yang dipilih
        function initializeViewer(panorama) {
            // Hancurkan viewer sebelumnya jika ada
            if (viewer) {
                viewer.destroy();
            }

            // Buat viewer baru
            viewer = pannellum.viewer('panorama', {
                type: 'equirectangular',
                panorama: panorama,
                autoLoad: true
            });
        }

        // Tampilkan gambar panorama yang sesuai
        initializeViewer(panoramaUrl);

        // Set pointer pada koordinat saat ini
        document.getElementById('setPointer').addEventListener('click', () => {
            const pitch = viewer.getPitch();
            const yaw = viewer.getYaw();

            // Tampilkan koordinat di UI
            document.getElementById('pitch').innerText = pitch.toFixed(2);
            document.getElementById('yaw').innerText = yaw.toFixed(2);

            // Hapus pointer sebelumnya jika ada
            if (pointerHotspot) {
                viewer.removeHotSpot(pointerHotspot);
            }

            // Tambahkan hotspot baru di koordinat yang dipilih
            pointerHotspot = 'pointer-' + Date.now();
            viewer.addHotSpot({
                id: pointerHotspot,
                pitch: pitch,
                yaw: yaw,
                type: 'info',
                text: 'Pointer'
            });
        });
    </script>
</body>
</html>
