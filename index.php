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
$pointerQuery = $conn->query("SELECT h.*, w.id AS wisata_id FROM history_daerah h JOIN wisata w ON h.wisata_id = w.id");
if (!$pointerQuery) {
    die("Error fetching pointer data: " . $conn->error);
}

// Store pointer data for later use in JavaScript
$pointerData = [];
if ($pointerQuery->num_rows > 0) {
    while ($p = $pointerQuery->fetch_assoc()) {
        $pointerData[] = $p;
    }
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #peta-gambar {
            transition: none !important;
            transform: none !important;
        }

        #peta-gambar:hover {
            transform: none !important;
        }

        .modal-image-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        @media (min-width: 768px) {
            .modal-image-content img {
                max-width: 400px;
            }
        }

        .card-img-top {
            transition: transform 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.03);
        }
    </style>
</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'pengguna_header.php'; ?>

    <!-- Map Section -->
    <div class="container-fluid mt-3">
        <h5 class="text-center fw-bold mb-2">Peta Lokasi Daerah</h5>
        <div id="leafletMap" class="border rounded" style="height: 400px;"></div>
    </div>

    <!-- Dynamic Modals from Database -->
    <?php
    if (!empty($pointerData)) {
        foreach ($pointerData as $m) {
            // Tambahkan class img-fluid untuk gambar agar responsive
            $text_peta = preg_replace(
                '/<img(?![^>]*class=["\'][^"\']*img-fluid[^"\']*["\'])/i',
                '<img class="img-fluid"',
                $m['text_peta']
            );

            echo '
        <div class="modal fade" id="modalTitik' . $m['id'] . '" tabindex="-1" aria-labelledby="modalLabel' . $m['id'] . '" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="modalLabel' . $m['id'] . '">' . htmlspecialchars($m['judul']) . '</h5>
                        <button type="button " class="btn-close text-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-start" style="text-align: justify;">
                        <div class="modal-image-content">
                            ' . htmlspecialchars_decode($text_peta) . '
                        </div>
                        <a href="view.php?id=' . $m['id'] . '&wisata_id=' . $m['wisata_id'] . '" class="btn btn-success mt-3">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>';
        }
    }
    ?>


    <!-- Cards Section -->
    <div class="container-fluid mt-3" style="min-height: 100vh;">
        <div class="row row-cols-1 row-cols-md-3 g-4 p-3 border bg-secondary mt-2">
            <?php while ($row = $wisata->fetch_assoc()) { ?>
                <div class="col mt-0 p-2">
                    <div class="card h-100 shadow-sm" style="border: 1px solid grey" data-name="<?= htmlspecialchars($row['name']) ?>" data-kategori="<?= htmlspecialchars($row['kategori']) ?>">
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                            <hr>
                            <p style="text-align: justify;">
                                <?php
                                $max_length = 300;
                                $caption = $row['description'];
                                echo strlen($caption) > $max_length ? substr($caption, 0, $max_length) . '....' : $caption;
                                ?>
                            </p>
                        </div>
                        <hr>
                        <div class="d-flex  me-3 ms-3 text-center">
                            <div class="col-md-6 p-3">
                                <a href="https://www.google.com/maps?q=<?= urlencode($row['location']) ?>" target="_blank" class="btn btn-warning text-dark">
                                    <i class="bi bi-geo-alt"></i> Map
                                </a>
                            </div>
                            <div class="col-md-6 p-3">
                                <a href="informasi_wisata.php?wisata_id=<?= $row['id'] ?>" class="btn text-white bg-success">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.2/purify.min.js"></script>

    <script>
        // Initialize map
        const map = L.map('leafletMap').setView([-7.3505, 108.2200], 12);

        // Add OpenStreetMap layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        // Add markers from PHP data
        <?php if (!empty($pointerData)) { ?>
            const pointerData = <?= json_encode($pointerData) ?>;

            pointerData.forEach(pointer => {
                const marker = L.marker([pointer.latitude, pointer.longitude])
                    .addTo(map)
                    .bindPopup(`
                        <strong>${pointer.judul}</strong>
                        <br>
                        <button class="btn btn-sm btn-primary mt-2" 
                                onclick="document.getElementById('modalTitik${pointer.id}').style.display='block'; 
                                         new bootstrap.Modal(document.getElementById('modalTitik${pointer.id}')).show();">
                            Detail
                        </button>
                    `);

                // Add click event to show modal
                marker.on('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById(`modalTitik${pointer.id}`));
                    modal.show();
                });
            });
        <?php } ?>

        // Filter cards function
        function filterCards() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const selectedKategori = document.getElementById('kategoriFilter').value.toLowerCase();
            const cards = document.querySelectorAll('.card');

            cards.forEach(function(card) {
                const cardTitle = card.getAttribute('data-name').toLowerCase();
                const cardKategori = card.getAttribute('data-kategori').toLowerCase();

                const matchesSearch = cardTitle.includes(searchValue);
                const matchesCategory = selectedKategori === '' || cardKategori === selectedKategori;

                card.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
            });
        }

        // Service Worker registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered:', reg.scope))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }

        // Sanitize descriptions
        document.addEventListener("DOMContentLoaded", function() {
            const descriptions = document.querySelectorAll('.card-text');
            descriptions.forEach(desc => {
                desc.innerHTML = DOMPurify.sanitize(desc.innerHTML);
            });
        });
    </script>

    <?php include 'pengguna_footer.php'; ?>
</body>

</html>