<?php
include 'config.php';

// Ambil dan decode wisata_id dari parameter URL (base64)
if (isset($_GET['wisata_id'])) {
    $encoded_id = $_GET['wisata_id'];
    $decoded_id = base64_decode($encoded_id);

    if (is_numeric($decoded_id)) {
        $wisata_id = (int)$decoded_id;
    } else {
        echo "ID wisata tidak valid.";
        exit;
    }
} else {
    echo "ID wisata tidak ditemukan.";
    exit;
}

// Ambil data wisata
$wisata = $conn->query("SELECT * FROM wisata WHERE id = $wisata_id");
$row = $wisata->fetch_assoc();

// Ambil data scenes
$scenes = $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id");
$sceneList = [];
while ($scene = $scenes->fetch_assoc()) {
    $sceneList[] = $scene;
}

// Ambil data history
$historyQuery = $conn->query("SELECT id FROM history_daerah WHERE wisata_id = $wisata_id LIMIT 1");
$historyData = $historyQuery->fetch_assoc();
$historyId = $historyData ? $historyData['id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Bencana</title>
    <link rel="icon" type="image/png" href="img/Logo-Putih.png">

    <!-- Fonts & Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/informasi-wisata.css">
</head>

<body style="font-family: 'Poppins', sans-serif;">
    <?php include 'pengguna_header.php'; ?>

    <div class="container mt-3" style="min-height: 100vh;">
        <div class="row">
            <!-- Kiri: Detail Wisata -->
            <div class="col-md-8 mb-2 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Riwayat <?= htmlspecialchars($row['name']) ?></h3>
                    <a href="javascript:history.go(-1)" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <hr>

                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="main-image" alt="<?= htmlspecialchars($row['name']) ?>">

                <div class="description mt-3">
                    <p><?= nl2br(($row['description'])) ?></p>
                </div>
                <hr>

                <h4>Tautan :</h4>
                <div class="d-flex flex-column">
                    <div class="btn-group" role="group" aria-label="Wisata Actions">
                        <a href="view.php?id=<?= $historyId ?>&wisata_id=<?= urlencode($_GET['wisata_id']) ?>" class="btn btn-success">
                            <i class="bi bi-clock-history"></i> History Wisata
                        </a>
                        <a href="https://www.google.com/maps?q=<?= urlencode($row['location']) ?>" target="_blank" class="btn btn-warning">
                            <i class="bi bi-geo-alt"></i> Lihat Google Maps
                        </a>
                        <button id="shareBtn" class="btn btn-primary">
                            Bagikan <i class="bi bi-share"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kanan: Virtual Tour -->
            <div class="col-md-4 vertical-images p-3">
                <h3 class="text-center">Virtual Tour 360</h3>
                <hr>
                <div class="bg-success" style="max-height: 1000px; overflow-y: auto; border: 2px solid #ddd; border-radius: 8px; padding: 10px;">
                    <?php if (!empty($sceneList)): ?>
                        <?php foreach ($sceneList as $scene): ?>
                            <div class="card image-card mb-3" onclick="window.location.href='pengguna/view_tour.php?wisata_id=<?= urlencode($_GET['wisata_id']) ?>&scene_id=<?= $scene['id'] ?>';" style="cursor: pointer; border: 1px solid grey;">
                                <img src="admin/<?= htmlspecialchars($scene['panorama']) ?>" alt="<?= htmlspecialchars($scene['name']) ?>" class="card-img-top">
                                <div class="card-body">
                                    <h6><?= htmlspecialchars($scene['name']) ?></h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card text-center text-muted">
                            <i class="bi bi-exclamation-circle"></i> Tidak ada scene tersedia.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS Share -->
    <script>
        document.getElementById("shareBtn").addEventListener("click", function() {
            const currentURL = window.location.href;
            const shareText = `Lihat lokasi ini di Virtual Tour: ${currentURL}`;

            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: shareText,
                    url: currentURL
                }).catch(err => console.log("Gagal berbagi:", err));
            } else {
                navigator.clipboard.writeText(currentURL);
                alert("Link telah disalin ke clipboard!");
            }
        });
    </script>

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