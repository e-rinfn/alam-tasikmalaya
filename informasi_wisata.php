<?php
include 'config.php';


// Ambil wisata_id dari parameter URL atau sesuaikan dengan kebutuhan
$wisata_id = isset($_GET['wisata_id']) ? intval($_GET['wisata_id']) : 0;

// Ambil data wisata berdasarkan wisata_id
$wisata = $conn->query("SELECT * FROM wisata WHERE id = $wisata_id");
$row = $wisata->fetch_assoc();

// Ambil data scene berdasarkan wisata_id
$scenes = $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id");
$sceneList = [];
while ($scene = $scenes->fetch_assoc()) {
    $sceneList[] = $scene;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Daerah</title>
    <link rel="icon" type="image/png" href="img/Logo-Putih.png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/informasi-wisata.css">
</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'pengguna_header.php'; ?>

    <div class="container mt-3" style="min-height: 100vh; ">
        <div class="row">
            <!-- Bagian kiri: Gambar utama dan deskripsi -->
            <div class="col-md-8 mb-2 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Wisata <?= htmlspecialchars($row['name']) ?></h3>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <span class="small"></span><i class="bi bi-arrow-left me-1"></i> Kembali</span>
                    </a>
                </div>
                <hr>
                <!-- <div class="d-flex justify-content-center"> -->
                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="main-image" alt="<?= htmlspecialchars($row['name']) ?>">
                <!-- </div> -->

                <div class="description">
                    <p><?= $row['description'] ?></p>
                </div>

                <h4>Link Tautan :</h4>
                <div class="d-flex flex-column align-items-center">
                    <!-- History Wisata Button - Now Centered -->


                    <?php
                    // First, get the history_daerah id that matches this wisata_id
                    $historyQuery = $conn->query("SELECT id FROM history_daerah WHERE wisata_id = $wisata_id LIMIT 1");
                    $historyData = $historyQuery->fetch_assoc();
                    $historyId = $historyData ? $historyData['id'] : 0;
                    ?>

                    <a class="btn btn-info m-3" href="view.php?id=<?= $historyId ?>&wisata_id=<?= $wisata_id ?>"><i class="bi bi-clock-history"></i> History Wisata</a>

                    <div class="d-flex justify-content-center">
                        <a href="https://www.google.com/maps?q=<?= urlencode($row['location']) ?>"
                            target="_blank" class="btn btn-success mx-2">
                            <i class="bi bi-geo-alt"></i> Lihat Google Maps
                        </a>
                        <button id="shareBtn" class="btn btn-primary mx-2">
                            Bagikan <i class="bi bi-share"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bagian Virtual Tour 360 Derajat -->
            <div class="col-md-4 vertical-images p-3">
                <h3 class="text-center">Virtual Tour 360</h3>
                <hr>
                <div class="bg-secondary" style="max-height: 1000px; overflow-y: auto; border: 2px solid #ddd; border-radius: 8px; padding: 10px;">
                    <?php if (!empty($sceneList)): ?>
                        <?php foreach ($sceneList as $scene): ?>
                            <div class="card image-card mb-3" onclick="window.location.href='pengguna/view_tour.php?wisata_id=<?= $wisata_id ?>&scene_id=<?= $scene['id'] ?>';" style="cursor: pointer; border: 1px solid grey">
                                <img src="admin/<?= htmlspecialchars($scene['panorama']) ?>" alt="<?= htmlspecialchars($scene['name']) ?>" class="card-img-top">
                                <div class="card-body">
                                    <h6 style=""><?= htmlspecialchars($scene['name']) ?></h6>
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

    <!-- Bootstrap JS (for the hamburger menu) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript untuk berbagi link -->
    <script>
        document.getElementById("shareBtn").addEventListener("click", function() {
            const currentURL = window.location.href; // Mendapatkan URL halaman saat ini
            const shareText = `Lihat lokasi ini di Virtual Tour: ${currentURL}`;

            if (navigator.share) {
                // Gunakan Web Share API jika didukung
                navigator.share({
                    title: document.title,
                    text: shareText,
                    url: currentURL
                }).catch(err => console.log("Gagal berbagi:", err));
            } else {
                // Fallback: Salin URL ke clipboard
                navigator.clipboard.writeText(currentURL);
                alert("Link telah disalin ke clipboard!");
            }
        });
    </script>

    <?php include 'pengguna_footer.php'; ?>
</body>

</html>