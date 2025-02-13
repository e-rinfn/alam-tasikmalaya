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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour - <?= htmlspecialchars($wisata['name']) ?></title>
    
    <!-- Link CDN CSS Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Link CDN CSS Pannellum -->
    <link rel="stylesheet" href="https://cdn.pannellum.org/2.5/pannellum.css">

    <!-- Link CDN JS Pannellum -->
    <script src="https://cdn.pannellum.org/2.5/pannellum.js"></script>
    
    <!-- Link CSS Custom -->
    <link rel="stylesheet" href="../../css/tour.css">

    <style>
        #panorama {
            width: 100%;
            height: 100vh;
        }
    </style>
</head>
<body>

<!-- Modal Bootstrap -->
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

<h2 class="text-center my-3"><?= htmlspecialchars($wisata['name']) ?></h2>
<div id="panorama"></div>

<script>

    // Pastikan sceneConfig sudah di-load
    const sceneConfig = <?= json_encode($sceneData, JSON_PRETTY_PRINT) ?>;

    // Inisialisasi Pannellum
    const viewer = pannellum.viewer('panorama', {
        "default": {
            "firstScene": "<?= array_key_first($sceneData) ?>",
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

</body>
</html>