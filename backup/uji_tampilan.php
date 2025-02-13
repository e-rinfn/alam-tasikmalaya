<?php
session_start();
include 'config.php';

// Ambil data wisata, scene, dan hotspot
$wisata = $conn->query("SELECT * FROM wisata");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Flexbox untuk sticky footer */
        html, body {
            height: 100%;
            margin: 0;
        }
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content {
            flex: 1;
        }
        .footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Navigasi -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Logo</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Tentang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Layanan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Kontak</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Konten -->
        <div class="content container mt-5">
        <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Card 1 -->
        <?php while ($row = $wisata->fetch_assoc()) { ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0" data-name="<?= htmlspecialchars($row['name']) ?>">
                    <img src="<?php echo 'admin/' .$row['image_url']; ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="card-text text-muted"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex justify-content-between flex-wrap">
                        <a href="tour.php?wisata_id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Virtual Tour</a>
                        <a href="scenes.php?wisata_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Lihat Scene</a>
                        <a href="edit_wisata.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    </div>
                </div>
            </div>
            <?php } ?>
            </div>
        </div>

        </div>

        <!-- Footer -->
        <footer class="footer bg-dark text-white text-center py-3">
            <p>&copy; 2023 Website Sederhana. Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> oleh Anda.</p>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>