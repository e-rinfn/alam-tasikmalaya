<?php
include '../config.php';

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
        "panorama" => '../admin/' . $scene['panorama'],
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
        "type" => $hotspot['type'],
        "text" => $hotspot['text'],
        "sceneId" => $hotspot['target_scene_id'],
        "clickHandlerFunc" => "showModal",
        "clickHandlerArgs" => [
            "title" => $hotspot['text'],
            "content" => $hotspot['description']
        ]
    ];

    if ($hotspot['type'] === "scene" && isset($hotspot['target_scene_id'])) {
        $sceneData[$hotspot['target_scene_id']]['yaw'] = floatval($hotspot['targetYaw']); // Atur yaw di scene tujuan
    }
}

// Ambil scene_id dari URL
$scene_id = isset($_GET['scene_id']) ? intval($_GET['scene_id']) : null;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour - <?= htmlspecialchars($wisata['name']) ?></title>
    <link rel="icon" type="image/png" href="../img/Logo-Putih.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Link CDN CSS Pannellum -->
    <link rel="stylesheet" href="https://cdn.pannellum.org/2.5/pannellum.css">

    <!-- Link CDN JS Pannellum -->
    <script src="https://cdn.pannellum.org/2.5/pannellum.js"></script>

    <!-- Link CSS Custom -->
    <link rel="stylesheet" href="../css/tour.css">

    <style>
        #panorama {
            width: 100%;
            height: 100vh;
        }

        .modal-header {
            position: sticky;
            top: 0;
            z-index: 1055;
            /* Pastikan lebih tinggi dari konten */
            background-color: #6c757d;
            /* Sesuaikan warna latar agar tetap konsisten */
        }

        .hidden {
            display: none;
        }

        div.pnlm-tooltip span {
            visibility: visible !important;
            background-color: #0056b3;
            border: 1px solid;
        }

        .modal-body img {
            max-width: 100%;
            height: auto;
            display: block;
            /* Menghindari margin bawaan */
            margin: 0 auto;
            /* Pusatkan gambar */
        }
    </style>

</head>

<body>

    <div id="panorama-container">
        <div id="panorama"></div>
    </div>

    <div class="container mt-4">

        <!-- Tombol Kembali ke Index dan Pilih Lokasi -->
        <div class="d-flex justify-content-between align-items-center">

            <!-- Tambahkan tombol untuk menampilkan modal -->
            <div id="menu-overlay" class="p-2 border" style="background: rgba(0, 0, 0, 0.500);">
                <h5 class="text-center text-white">MENU</h5>
                <a href="../informasi_wisata.php?wisata_id=<?= $wisata_id ?>" class="btn btn-danger btn-sm border"><i class="bi bi-arrow-left"></i></a>
                <button class="btn btn-success btn-sm border" id="openSceneModal">
                    <i class="bi bi-card-image btn-sm"></i>
                </button>
                <button class="btn btn-primary btn-sm border" id="toggleGyro"><i class="bi bi-compass"></i> 360 Off</button>
            </div>

            <!-- Modal Pilih Lokasi -->
            <div class="modal fade" id="sceneModal" tabindex="-1" aria-labelledby="sceneModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <!-- Sticky Header -->
                        <div class="modal-header text-white bg-secondary">
                            <h5 class="modal-title" id="sceneModalLabel"><i class="bi bi-card-image"></i> - Pilih Lokasi Scene</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Scrollable Modal Body -->
                        <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                            <div class="pengujian" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                                <?php if (!empty($sceneList)): ?>
                                    <?php foreach ($sceneList as $scene): ?>
                                        <li class="card">
                                            <a href="#" onclick="selectScene('<?= $scene['id'] ?>')" class="text-decoration-none flex-grow-1 fw-bold text-center p-2">
                                                <div class="d-flex justify-content-center">
                                                    <img src="<?= '../admin/' . htmlspecialchars($scene['panorama']) ?>" width="200" class="rounded shadow text-center">
                                                </div>
                                                <div class="mb-2 text-center text-mute" style="color: black">
                                                    <?= htmlspecialchars($scene['name']) ?>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="card text-center text-muted">
                                        <i class="bi bi-exclamation-circle"></i> Tidak ada scene tersedia.
                                    </li>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <script>
                // Pastikan Bootstrap modal dapat diakses dengan benar
                document.getElementById('openSceneModal').addEventListener('click', function() {
                    var sceneModal = new bootstrap.Modal(document.getElementById('sceneModal'));
                    sceneModal.show();
                });

                document.addEventListener("DOMContentLoaded", function() {
                    var myModal = new bootstrap.Modal(document.getElementById("sceneModal"), {});

                    // Pastikan tombol tutup bekerja
                    document.querySelectorAll("[data-bs-dismiss='modal']").forEach(button => {
                        button.addEventListener("click", function() {
                            myModal.hide();
                        });
                    });
                });


                function selectScene(sceneId) {
                    if (viewer) {
                        viewer.loadScene(sceneId);
                        var sceneModal = bootstrap.Modal.getInstance(document.getElementById('sceneModal'));
                        sceneModal.hide(); // Tutup modal setelah memilih lokasi
                    } else {
                        console.error("Pannellum viewer tidak ditemukan.");
                    }
                }
            </script>

            <div id="judul-overlay" class="border" style="background: rgba(0, 0, 0, 0.500);">
                <h5 class="text-white mb-0"><?= 'Wisata ' . htmlspecialchars($wisata['name']) ?> </h5>
            </div>
        </div>


        <!-- Modal Bootstrap -->
        <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background: linear-gradient(100deg, #001A6E, #16C47F );">
                        <h5 class="modal-title" id="modalTitle">Informasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                        <p id="modalContent">Konten informasi akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let gyroEnabled = true; // Status awal gyroscope hidup

            document.getElementById("toggleGyro").addEventListener("click", function() {
                if (gyroEnabled) {
                    viewer.stopOrientation(); // Nonaktifkan gyroscope
                    this.innerHTML = `<i class="bi bi-compass"></i> 360 Off`;
                    gyroEnabled = false;

                    // Aktifkan kembali kontrol mouse dan sentuh
                    viewer.setConfig({
                        mouseZoom: true, // Aktifkan zoom mouse
                        draggable: true, // Aktifkan pergeseran
                    });
                } else {
                    if (typeof DeviceMotionEvent !== "undefined" && typeof DeviceMotionEvent.requestPermission === "function") {
                        // Untuk iOS, minta izin sensor terlebih dahulu
                        DeviceMotionEvent.requestPermission().then(permissionState => {
                            if (permissionState === "granted") {
                                viewer.startOrientation(); // Aktifkan gyroscope
                                this.innerHTML = `<i class="bi bi-compass"></i> 360 On`;
                                gyroEnabled = true;

                                // Nonaktifkan kontrol mouse dan sentuh
                                viewer.setConfig({
                                    mouseZoom: false, // Nonaktifkan zoom mouse
                                    draggable: false, // Nonaktifkan pergeseran
                                });
                            }
                        }).catch(error => {
                            console.error("Permission denied:", error);
                        });
                    } else {
                        // Untuk perangkat selain iOS
                        viewer.startOrientation();
                        this.innerHTML = `<i class="bi bi-compass"></i> 360 On`;
                        gyroEnabled = true;

                        // Nonaktifkan kontrol mouse dan sentuh
                        viewer.setConfig({
                            mouseZoom: false, // Nonaktifkan zoom mouse
                            draggable: false, // Nonaktifkan pergeseran
                        });
                    }
                }
            });


            // Pastikan sceneConfig sudah di-load
            const sceneConfig = <?= json_encode($sceneData, JSON_PRETTY_PRINT) ?>;

            // Tentukan scene pertama yang akan ditampilkan
            const firstScene = <?= $scene_id ? json_encode($scene_id) : 'Object.keys(sceneConfig)[0]' ?>;

            // Function untuk menentukan apakah perangkat adalah mobile
            function isMobile() {
                return window.innerWidth <= 768; // Anggap perangkat dengan lebar <= 768px adalah mobile
            }



            // Konfigurasi awal berdasarkan ukuran layar
            const initialHfov = isMobile() ? 80 : 100; // hfov lebih kecil di mobile untuk zoom lebih dekat
            const minHfov = isMobile() ? 60 : 80; // Zoom in lebih besar di mobile
            const maxHfov = isMobile() ? 100 : 120; // Zoom out lebih kecil di mobile

            // Inisialisasi Pannellum
            const viewer = pannellum.viewer('panorama', {
                "default": {
                    "firstScene": firstScene,
                    "hfov": initialHfov,
                    "minHfov": minHfov,
                    "maxHfov": maxHfov,
                    "sceneFadeDuration": 1000,
                    "autoLoad": true,
                    "compass": false,
                    "hotSpotDebug": false,
                    "autoRotate": -10,
                    "mouseZoom": true, // Aktifkan zoom mouse
                    "draggable": true, // Aktifkan pergeseran
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

            // Event listener untuk menyesuaikan hfov saat layar berubah ukuran
            window.addEventListener('resize', () => {
                const newHfov = isMobile() ? 80 : 100; // Atur hfov baru berdasarkan ukuran layar
                const newMinHfov = isMobile() ? 60 : 80;
                const newMaxHfov = isMobile() ? 100 : 120;

                // Perbarui properti viewer
                viewer.setHfov(newHfov);
                viewer.setMinHfov(newMinHfov);
                viewer.setMaxHfov(newMaxHfov);
            });
        </script>

        <!-- Link JS Bootstrap -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <!-- Bootstrap 5 JS (Popper.js sudah termasuk) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    </div>

</body>

</html>