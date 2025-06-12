<?php


include 'config.php';

// Error handling for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tourist data with error handling
$wisata = $conn->query("SELECT * FROM wisata");
if (!$wisata) {
    die("Error fetching wisata data: " . $conn->error);
}

// Fetch pointer data with error handling
$pointerQuery = $conn->query("SELECT * FROM history_daerah");
if (!$pointerQuery) {
    die("Error fetching pointer data: " . $conn->error);
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

    <style>
        #peta-gambar {
            transition: none !important;
            transform: none !important;
        }

        #peta-gambar:hover {
            transform: none !important;
        }
    </style>



    <!-- Map and Pointers Section -->
    <div class="card mt-4 mb-3 mx-auto" style="max-width: 800px; position: relative;">
        <img src="img/map.png" alt="Peta Tasikmalaya" class="img-fluid p-2" id="peta-gambar">

        <!-- Dynamic Pointers from Database -->
        <?php
        if ($pointerQuery->num_rows > 0) {
            while ($p = $pointerQuery->fetch_assoc()) {
                echo '<div class="pointer" 
                      style="left: ' . htmlspecialchars($p['left_position']) . '; 
                             top: ' . htmlspecialchars($p['top_position']) . ';"
                      data-bs-toggle="modal" 
                      data-bs-target="#modalTitik' . $p['id'] . '"
                      title="' . htmlspecialchars($p['judul']) . '"></div>';
            }
        }
        ?>
    </div>

    <style>
        .modal-image-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        @media (min-width: 768px) {

            /* Untuk layar desktop dan di atas tablet */
            .modal-image-content img {
                max-width: 400px;
            }
        }
    </style>


    <!-- Dynamic Modals from Database -->
    <?php
    $modalQuery = $conn->query("SELECT * FROM history_daerah");
    if ($modalQuery && $modalQuery->num_rows > 0) {
        while ($m = $modalQuery->fetch_assoc()) {
            echo '
        <div class="modal fade" id="modalTitik' . $m['id'] . '" tabindex="-1" aria-labelledby="modalLabel' . $m['id'] . '" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- gunakan modal-lg -->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel' . $m['id'] . '">' . htmlspecialchars($m['judul']) . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="modal-image-content">
                            ' . htmlspecialchars_decode($m['text_peta']) . '
                        </div>
                        <a href="view.php?id=' . $m['id'] . '" class="btn btn-primary mt-3">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>';
        }
    }
    ?>


    <!-- Pointer Style -->
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
            z-index: 100;
        }

        .pointer:hover {
            transform: translate(-50%, -50%) scale(1.2);
            background-color: darkred;
        }
    </style>

    <!-- Cards Section -->
    <div class="container" style="min-height: 100vh;">


        <br>
        <div class="row row-cols-1 row-cols-md-3 g-4 p-3 border rounded-3" style="background: linear-gradient(135deg, #16C47F , #001A6E);">




            <!-- Card Daftar Wisata -->
            <?php while ($row = $wisata->fetch_assoc()) { ?>
                <div class="col mt-0 p-2">
                    <div class="card h-100 shadow-sm" style="border: 1px solid grey" data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-kategori="<?= htmlspecialchars($row['kategori']) ?>">

                        <img src="<?= '' . htmlspecialchars($row['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                            <hr>
                            <!-- Elemen untuk deskripsi -->
                            <p class="card-text" style="text-align: justify;">
                                <?php
                                $max_length = 300; // Batas karakter
                                $caption = $row['description'];
                                echo strlen($caption) > $max_length ? substr($caption, 0, $max_length) . '....' : $caption;
                                ?>
                            </p>
                        </div>
                        <div class="text-center m-2">
                            Kategori : <span class="badge text-bg-primary"><?= htmlspecialchars($row['kategori']) ?></span>
                        </div>
                        <hr>
                        <div class="row m-3 text-end">
                            <div class="col-md-2 p-3">
                                <a href="https://www.google.com/maps?q=<?= urlencode($row['location']) ?>"
                                    target="_blank" class="btn btn-warning text-dark">
                                    <i class="bi bi-geo-alt"></i>
                                </a>
                            </div>
                            <div class="col-md-10 p-3">
                                <a href="informasi_wisata.php?wisata_id=<?= $row['id'] ?>" class="btn text-white bg-success">
                                    <i class="bi bi-eye"></i> Lihat Selengkapnya
                                </a>
                            </div>
                        </div>
                    </div>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.2/purify.min.js"></script>
                    <script>
                        // Tampilkan deskripsi wisata
                        document.addEventListener("DOMContentLoaded", function() {
                            let descriptionElement = document.getElementById("description-<?= $row['id'] ?>");
                            let descriptionText = `<?= nl2br($row['description']) ?>`;

                            // Gunakan DOMPurify untuk mengamankan HTML
                            descriptionElement.innerHTML = DOMPurify.sanitize(descriptionText);
                        });
                    </script>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Bootstrap JS (for the hamburger menu) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function filterCards() {
            var searchValue = document.getElementById('searchBar').value.toLowerCase();
            var cards = document.querySelectorAll('.card');

            cards.forEach(function(card) {
                var cardTitle = card.getAttribute('data-name').toLowerCase();
                if (cardTitle.includes(searchValue)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker terdaftar:', reg.scope))
                    .catch(err => console.log('Service Worker gagal:', err));
            });
        }
    </script>

    <script>
        function filterCards() {
            var searchValue = document.getElementById('searchBar').value.toLowerCase();
            var selectedKategori = document.getElementById('kategoriFilter').value.toLowerCase();
            var cards = document.querySelectorAll('.card');

            cards.forEach(function(card) {
                var cardTitle = card.getAttribute('data-name').toLowerCase();
                var cardKategori = card.getAttribute('data-kategori').toLowerCase();

                var cocokJudul = cardTitle.includes(searchValue);
                var cocokKategori = selectedKategori === '' || cardKategori === selectedKategori;

                if (cocokJudul && cocokKategori) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

    <?php include 'pengguna_footer.php'; ?>
</body>

</html>