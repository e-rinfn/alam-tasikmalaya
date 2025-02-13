<?php
include 'config.php';

$wisata_id = isset($_GET['wisata_id']) ? intval($_GET['wisata_id']) : 0;
if ($wisata_id === 0) {
    die("Wisata tidak ditemukan.");
}

// Ambil data wisata
$wisata = $conn->query("SELECT * FROM wisata WHERE id = $wisata_id")->fetch_assoc();
if (!$wisata) {
    die("Wisata tidak valid.");
}

// Ambil daftar scene untuk wisata ini
$scenes = $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id");
$sceneList = [];
while ($scene = $scenes->fetch_assoc()) {
    $sceneList[] = $scene;
}

// Ambil daftar scene untuk wisata ini
$scenes = $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id");

// Siapkan data JSON untuk Pannellum
$sceneData = [];
while ($scene = $scenes->fetch_assoc()) {
    $sceneData[$scene['id']] = [
        "panorama" => 'admin/' . $scene['panorama'],
        "autoLoad" => true,
        "yaw" => 0,
        "hotSpots" => [] // Akan diisi nanti
    ];
}

// Ambil hotspot yang terkait dengan scene
$hotspots = $conn->query("SELECT * FROM hotspots WHERE scene_id IN (SELECT id FROM scenes WHERE wisata_id = $wisata_id)");
while ($hotspot = $hotspots->fetch_assoc()) {
    $sceneId = $hotspot['scene_id'];
    $sceneData[$sceneId]['hotSpots'][] = [
        "pitch" => floatval($hotspot['pitch']),
        "yaw" => floatval($hotspot['yaw']),
        "type" => $hotspot['type'], // Bisa "info" atau "scene"
        "text" => $hotspot['text'],
        "sceneId" => $hotspot['target_scene_id'],
        "clickHandlerFunc" => "showModal",
        "clickHandlerArgs" => [
            "title" => $hotspot['text'],
            "content" => $hotspot['description']
        ]
    ];
}

// Ambil scene_id dari URL
$scene_id = isset($_GET['scene_id']) ? intval($_GET['scene_id']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Virtual Tour Wisata Tasikmalaya</title>
    
    <!-- Link CDN CSS Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Link CDN CSS Pannellum -->
    <link rel="stylesheet" href="https://cdn.pannellum.org/2.5/pannellum.css">

    <!-- Link CDN JS Pannellum -->
    <script src="https://cdn.pannellum.org/2.5/pannellum.js"></script>

    <!-- Link CDN Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Link CSS Custom -->
    <link rel="stylesheet" href="css/tour.css">

    
</head>
<body>
    <div id="panorama-container">
        <div id="panorama"></div>
    </div>

    <div class="container mt-4">

        <!-- Tombol Kembali ke Index dan Pilih Lokasi -->
<div class="d-flex justify-content-between align-items-center p-3">
    <!-- Tombol Kembali -->
    <div id="menu-overlay" class="p-2">

    <a href="../../informasi-bukit-panyangrayan.php" class="btn btn-warning">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

    <!-- Tombol Pilih Lokasi -->
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#sceneModal">
        ☰ Pilih Lokasi
    </button>
    </div>

    <!-- Judul -->
    <div id="judul-overlay">
    <h5 class="m-0">Bukit Panyangrayan</h5>
    </div>  
</div>

<!-- Modal Pilih Lokasi -->
<div class="modal fade" id="sceneModal" tabindex="-1" aria-labelledby="sceneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sceneModalLabel">Pilih Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <?php foreach ($sceneList as $scene): ?>
                        <li class="list-group-item d-flex align-items-center">
                            <img src="../../thumbnail/360/bukit-panyangrayan/<?= htmlspecialchars($scene['thumbnail']) ?>" width="50" class="me-2">
                            <a href="#" onclick="selectScene('<?= $scene['id'] ?>')" class="text-decoration-none flex-grow-1">
                                <?= htmlspecialchars($scene['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function selectScene(sceneId) {
        if (viewer) {
            viewer.loadScene(sceneId);
            var sceneModal = new bootstrap.Modal(document.getElementById('sceneModal'));
            sceneModal.hide(); // Tutup modal setelah memilih lokasi
        } else {
            console.error("Pannellum viewer tidak ditemukan.");
        }
    }
</script>



        <!-- Modal -->
        <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Informasi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="modalContent">Konten informasi akan ditampilkan di sini.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <script>

// Pastikan sceneConfig sudah di-load
const sceneConfig = <?= json_encode($sceneData, JSON_PRETTY_PRINT) ?>;

// Tentukan scene pertama yang akan ditampilkan
const firstScene = <?= $scene_id ? json_encode($scene_id) : 'Object.keys(sceneConfig)[0]' ?>;

// Inisialisasi Pannellum
const viewer = pannellum.viewer('panorama', {
    "default": {
        "firstScene": firstScene,
        "sceneFadeDuration": 1000,
        "autoLoad": true
    },
    "scenes": sceneConfig
});

// Konversi clickHandlerFunc dari string ke function
Object.values(sceneConfig).forEach(scene => {
    if (scene.hotSpots) {
        scene.hotSpots.forEach(hotspot => {
            if (hotspot.clickHandlerFunc === "showModal") {
                hotspot.clickHandlerFunc = function(event, args) {
                    window.showModal(event, args);
                };
            }
        });
    }
});

console.log("Scene Config setelah perubahan:", sceneConfig);

window.showModal = function(event, args) {
    console.log("showModal dipanggil dengan args:", args); // Debugging
    if (!args || !args.title || !args.content) {
        console.error("Data args tidak lengkap:", args);
        return;
    }

    document.getElementById("modalTitle").innerText = args.title;
    document.getElementById("modalContent").innerHTML = args.content;
    $('#infoModal').modal('show'); // Tampilkan modal
};

</script>

        <!-- Link JS Bootstrap -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </div>
</body>
</html>
