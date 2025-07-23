<?php
include 'config.php';
ini_set('memory_limit', '256M'); // Tingkatkan memory limit

// Error handling for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tourist data with error handling
$wisata = $conn->query("SELECT * FROM wisata");
if (!$wisata) {
    die("Error fetching wisata data: " . $conn->error);
}

// Konfigurasi paginasi
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 6; // Jumlah item per halaman
$offset = ($page - 1) * $itemsPerPage; // Hitung offset

// Get filter parameters from URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Base query
$baseQuery = "SELECT * FROM wisata";

// Add search filter if provided
if (!empty($search)) {
    $baseQuery .= " WHERE name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Add sorting
switch ($sortOrder) {
    case 'oldest':
        $baseQuery .= " ORDER BY created_at ASC";
        break;
    case 'name_asc':
        $baseQuery .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $baseQuery .= " ORDER BY name DESC";
        break;
    default: // newest
        $baseQuery .= " ORDER BY created_at DESC";
}

// Query dengan paginasi
$query = $baseQuery . " LIMIT $itemsPerPage OFFSET $offset";
$wisata = $conn->query($query);

// Untuk total count (tanpa paginasi)
$totalQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $baseQuery);
$totalItems = $conn->query($totalQuery)->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Ambil data pointer
$pointerQuery = $conn->query("
    SELECT h.*, w.id AS wisata_id 
    FROM history_daerah h 
    JOIN wisata w ON h.wisata_id = w.id
    WHERE h.latitude IS NOT NULL AND h.longitude IS NOT NULL
");


// Fetch pointer data with error handling
$pointerQuery = $conn->query("SELECT h.*, w.id AS wisata_id FROM history_daerah h JOIN wisata w ON h.wisata_id = w.id");
if (!$pointerQuery) {
    die("Error fetching pointer data: " . $conn->error);
}

// Store pointer data for later use in JavaScript
// $pointerData = [];
// if ($pointerQuery->num_rows > 0) {
//     while ($p = $pointerQuery->fetch_assoc()) {
//         $pointerData[] = $p;
//     }
// }

// Store pointer data
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
    <title>Riwayat Bencana</title>
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
    <style>
        .pagination .page-link:hover {
            background-color: #d1e7dd;
            /* Soft green hover */
            color: #198754;
        }
    </style>
</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'pengguna_header.php'; ?>

    <main>
        <!-- Map Section -->
        <div class="container-fluid mt-3">
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
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white" id="modalLabel' . $m['id'] . '">' . htmlspecialchars($m['judul']) . '</h5>
                        <button type="button " class="btn-close text-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-start" style="text-align: justify;">
                        <div class="modal-image-content">
                            ' . htmlspecialchars_decode($text_peta) . '
                        </div>
                        <a href="view.php?id=' . $m['id'] . '&wisata_id=' . $m['wisata_id'] . '" class="btn btn-success mt-3">Lihat</a>
                    </div>
                </div>
            </div>
        </div>';
            }
        }
        ?>


        <!-- Filter Section -->
        <form class="container mt-3" onsubmit="event.preventDefault(); applyFilters();">
            <div class="row">
                <!-- Input Pencarian -->
                <div class="col-md-6 mb-3">
                    <input type="text" id="searchBar" class="form-control" placeholder="Cari riwayat bencana..."
                        value="<?= htmlspecialchars($search ?? '') ?>" onkeyup="filterCards()">
                </div>

                <!-- Urutan -->
                <div class="col-md-2 mb-3">
                    <select id="sortOrder" class="form-select">
                        <option value="newest" <?= ($sortOrder ?? '') === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="oldest" <?= ($sortOrder ?? '') === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                        <option value="name_asc" <?= ($sortOrder ?? '') === 'name_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
                        <option value="name_desc" <?= ($sortOrder ?? '') === 'name_desc' ? 'selected' : '' ?>>Nama (Z-A)</option>
                    </select>
                </div>

                <!-- Tombol Terapkan -->
                <div class="col-md-2 mb-3">
                    <button class="btn btn-success w-100" onclick="applyFilters()">Terapkan</button>
                </div>

                <!-- Tombol Reset -->
                <div class="col-md-2 mb-3">
                    <button class="btn btn-secondary w-100" onclick="resetFilters()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Card Tambah Riwayat Bencana Namun Untuk User Di Hide -->
        <div class="col mt-0 p-2" hidden>
            <a href="admin/add_wisata.php">
                <div class="card h-100 shadow-sm border-0 text-center d-flex align-items-center justify-content-center;">
                    <div class="card-body" style="margin-top: 13rem; margin-bottom: 10rem;">
                        <i class="bi bi-plus-lg text-primary" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted">Tambah Data Riwayat Bencana</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cards Section -->
        <div class="container-fluid bg-success mt-3" style="min-height: 100vh;">
            <div class="row row-cols-1 row-cols-md-3 g-4 p-3">
                <?php while ($row = $wisata->fetch_assoc()) { ?>
                    <div class="col">
                        <div class="card h-100 shadow border border-secondary"
                            data-name="<?= htmlspecialchars($row['name']) ?>"
                            data-kategori="<?= htmlspecialchars($row['kategori']) ?>">

                            <!-- Gambar Wisata -->
                            <img src="<?= htmlspecialchars($row['image_url']) ?>"
                                class="card-img-top"
                                alt="<?= htmlspecialchars($row['name']) ?>"
                                style="height: 250px; object-fit: cover;">

                            <!-- Isi Card -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                                <hr class="my-2">

                                <p class="card-text text-justify" style="flex-grow: 1;">
                                    <?php
                                    $max_length = 200;
                                    $caption = $row['description'];
                                    echo strlen($caption) > $max_length ? substr($caption, 0, $max_length) . '…' : $caption;
                                    ?>
                                </p>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="text-end p-3">

                                <a href="https://www.google.com/maps?q=<?= urlencode($row['location']) ?>" target="_blank" class="btn btn-warning text-dark">
                                    <i class="bi bi-geo-alt"></i> Peta Lokasi
                                </a>


                                <a href="informasi_wisata.php?wisata_id=<?= $row['id'] ?>" class="btn text-white bg-success">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>

                            </div>

                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>


        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Tombol Previous -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link text-success border-success" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sortOrder ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- Nomor Halaman -->
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link <?= ($i == $page) ? 'bg-success border-success text-white' : 'text-success border-success' ?>"
                            href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sortOrder ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Tombol Next -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link text-success border-success" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sortOrder ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.2/purify.min.js"></script>

    <script>
        // Initialize map
        // const map = L.map('leafletMap').setView([-7.3505, 108.2200], 12);
        const map = L.map('leafletMap').setView([-7.4082, 108.3608], 12);


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
                        <button class="btn btn-sm btn-success mt-2" 
                                onclick="document.getElementById('modalTitik${pointer.id}').style.display='block'; 
                                         new bootstrap.Modal(document.getElementById('modalTitik${pointer.id}')).show();">
                            Lihat
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

    <script>
        function applyFilters() {
            const search = document.getElementById('searchBar').value;
            const sort = document.getElementById('sortOrder').value;

            const url = `?search=${encodeURIComponent(search)}&sort=${sort}`;
            window.location.href = url;
        }

        function resetFilters() {
            document.getElementById('searchBar').value = '';
            document.getElementById('sortOrder').value = 'newest';
            applyFilters();
        }
    </script>


    <?php include 'pengguna_footer.php'; ?>
</body>

</html>