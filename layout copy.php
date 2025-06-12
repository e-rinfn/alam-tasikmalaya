<?php

include 'config.php';

// Ambil data wisata, scene, dan hotspot
$wisata = $conn->query("SELECT * FROM wisata");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alam Tasikmalaya 360</title>
    <link rel="icon" type="image/png" href="img/Logo-Putih.png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">

    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">

</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'pengguna_header.php'; ?>

    <!-- Dropdown Kategori Wisata -->
    <div class="search-bar d-flex flex-wrap gap-2 align-items-center justify-content-center">
        <input type="text" class="search-input" id="searchBar" placeholder="Cari Objek Wisata..." oninput="filterCards()">

        <select class="form-select w-auto" id="kategoriFilter" onchange="filterCards()">
            <option value="">Semua Kategori</option>
            <?php
            $kategori = $conn->query("SELECT DISTINCT kategori FROM wisata");
            while ($kat = $kategori->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($kat['kategori']) . '">' . htmlspecialchars($kat['kategori']) . '</option>';
            }
            ?>
        </select>
    </div>

    <!-- Bootstrap 5 CSS & JS (jika belum) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PETA dan POINTER -->
    <div class="card mt-5 mb-3" style="position: relative; max-width: 800px; margin: auto;">
        <img src="img/map.png" alt="Map" class="img-fluid" id="peta-gambar">

        <!-- Pointer Titik A -->
        <div
            class="pointer"
            style="left: 30%; top: 40%;"
            data-bs-toggle="modal"
            data-bs-target="#modalTitikA"
            title="Titik A"></div>

        <!-- Pointer Titik B -->
        <div
            class="pointer"
            style="left: 60%; top: 55%;"
            data-bs-toggle="modal"
            data-bs-target="#modalTitikB"
            title="Titik B"></div>

        <!-- Pointer Titik C -->
        <div
            class="pointer"
            style="left: 45%; top: 70%;"
            data-bs-toggle="modal"
            data-bs-target="#modalTitikC"
            title="Titik C"></div>
    </div>

    <!-- STYLE POINTER -->
    <style>
        .pointer {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: red;
            border: 2px solid white;
            border-radius: 50%;
            cursor: pointer;
            transform: translate(-50%, -50%);
            transition: transform 0.2s ease;
        }

        .pointer:hover {
            transform: translate(-50%, -50%) scale(1.2);
            background-color: darkred;
        }
    </style>

    <!-- Modal Titik A -->
    <!-- Modal Titik A -->
    <div class="modal fade" id="modalTitikA" tabindex="-1" aria-labelledby="modalTitikALabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitikALabel">Titik A</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="img/titik-a.jpg" alt="Titik A" class="img-fluid mb-3">
                    <p>Deskripsi singkat untuk Titik A.</p>
                    <a href="history.php?id=1" class="btn btn-primary">Lihat Detail</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Titik B -->
    <div class="modal fade" id="modalTitikB" tabindex="-1" aria-labelledby="modalTitikBLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitikBLabel">Titik B</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="img/titik-b.jpg" alt="Titik B" class="img-fluid mb-3">
                    <p>Deskripsi singkat untuk Titik B.</p>
                    <a href="history.php?id=2" class="btn btn-primary">Lihat Detail</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Titik C -->
    <div class="modal fade" id="modalTitikC" tabindex="-1" aria-labelledby="modalTitikCLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitikCLabel">Informasi Titik C</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Ini adalah informasi tentang Titik C.
                </div>
            </div>
        </div>
    </div>



    <?php include 'pengguna_footer.php'; ?>
</body>

</html>