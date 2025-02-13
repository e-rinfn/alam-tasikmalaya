<?php

include 'config.php';

// Ambil data wisata, scene, dan hotspot
$wisata = $conn->query("SELECT * FROM wisata");


$row = $wisata->fetch_assoc();
$scene = $conn->query("SELECT * FROM scenes");
$scene = $scene->fetch_assoc();

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
    <title>Alam Tasikmalaya 360</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/informasi-wisata.css">

</head>
<body>


<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-secondary">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand text-white" href="index.php">Alam Tasikmalaya 360</a>      

        <!-- Menu Navbar -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Dropdown -->
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

<div class="container my-3">
    <div class="row">
        <!-- Bagian kiri: Gambar utama dan deskripsi -->
        <div class="col-md-8 mb-5">
            <h3>Wisata <?= htmlspecialchars($row['name']) ?></h3>
            <hr>
            <div class="d-flex justify-content-center">
            <img src="<?= '' . htmlspecialchars($row['image_url']) ?>" class="text-center" alt="<?= htmlspecialchars($row['name']) ?>">
        </div>

            <div class="description">
                <p><?= $row['description'] ?></p>

            </div>
            <h4>Link Tautan :</h4>
            <!-- Link ke Google Maps -->
            <div class="d-flex justify-content-center">
                <!-- Tombol Google Maps -->
                <a href="https://www.google.com/maps/search/?q=<?= urlencode($row['location']) ?>" 
                class="btn btn-warning m-3 mb-3" 
                target="_blank">
                <i class="bi bi-map"></i> - Lihat di Google Maps 
                </a>

                <!-- Tombol Bagikan -->
                <button id="shareBtn" class="btn btn-primary m-3 mb-3">Bagikan <i class="bi bi-share"></i></button>
            </div>
        </div>

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

        <!-- Bagian Virtual Tour 360 Derajat -->
        <div class="col-md-4 vertical-images">
            <h3>Virtual Tour 360</h3>

            <!-- Card 1 -->
            <?php if (!empty($sceneList)): ?>
                        <?php foreach ($sceneList as $scene): ?>
                            <li class="card">
                                
                                <div class="d-flex justify-content-center">
                                <img src="admin/<?= htmlspecialchars($scene['panorama']) ?>" width="200" class=" rounded shadow text-center">
                                </div>
                                <a href="#" onclick="selectScene('<?= $scene['id'] ?>')" class="text-decoration-none flex-grow-1 fw-bold text-center">
                                    <?= htmlspecialchars($scene['name']) ?>
                                </a>
                          
                                <!-- <button class="btn btn-sm btn-outline-primary" onclick="selectScene('<?= $scene['id'] ?>')">
                                    <i class="bi bi-box-arrow-in-right"></i> Pilih
                                </button> -->
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="cardtext-center text-muted">
                            <i class="bi bi-exclamation-circle"></i> Tidak ada scene tersedia.
                        </li>
                    <?php endif; ?>
            <div class="card image-card" onclick="window.location.href='view_tour.php?wisata_id=<?= $wisata_id ?>&scene_id=<?= $scene['id'] ?>" style="cursor: pointer;">
            <img src="<?= 'admin/' . htmlspecialchars($scene['panorama']) ?>" alt="Gambar 1">
                <div class="card-body">
                    <h6><?= htmlspecialchars($scene['name']) ?></h6>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="card image-card" onclick="window.location.href='tour/situ-gede/situgede-kiri.php';" style="cursor: pointer;">
                <img src="thumbnail/360/situ-gede/Situgede Kekiri.png" alt="Gambar 2">
                <div class="card-body">
                    <h6>Kiri Dari Gerbang</h6>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="card image-card" onclick="window.location.href='tour/situ-gede/situgede-kanan.php';" style="cursor: pointer;">
                <img src="thumbnail/360/situ-gede/Situgede Kekanan.png" alt="Gambar 2">
                <div class="card-body">
                    <h6>Kanan Dari Gerbang</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer style="flex-shrink: 0;">
    <p>&copy; 2025 Erin Fajrin Nugraha - Alam Tasikmalaya 360.</p>
</footer>

<!-- Bootstrap JS (for the hamburger menu) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
