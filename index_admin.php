<?php
session_start();
ini_set('memory_limit', '256M');
include 'config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil user_id dan role dari session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek role user
if ($role !== 'admin' && $role !== 'user') {
    echo "<script>alert('Anda tidak memiliki izin untuk mengakses halaman ini!'); window.location.href='no_access.php';</script>";
    exit;
}

// Konfigurasi paginasi
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 5; // Jumlah item per halaman
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

// Store pointer data
$pointerData = [];
if ($pointerQuery->num_rows > 0) {
    while ($p = $pointerQuery->fetch_assoc()) {
        $pointerData[] = $p;
    }
}
?>

<style>
    .pagination .page-link:hover {
        background-color: #d1e7dd;
        /* Soft green hover */
        color: #198754;
    }
</style>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Bencana</title>
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

</head>

<style>
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
</style>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'admin_header.php'; ?>

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
                        <h5 class="modal-title" id="modalLabel' . $m['id'] . '">' . htmlspecialchars($m['judul']) . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-start" style="text-align: justify;">
                        <div class="modal-image-content">
                            ' . htmlspecialchars_decode($text_peta) . '
                        </div>
                        <a href="view-admin.php?id=' . $m['id'] . '&wisata_id=' . $m['wisata_id'] . '" class="btn btn-success mt-3">Lihat</a>
                    </div>
                </div>
            </div>
        </div>';
        }
    }
    ?>



    <main>
        <!-- Cards Section -->
        <div class="container-fluid mt-3" style="min-height: 100vh;">

            <!-- Filter Section -->
            <div class="container mt-3" onsubmit="event.preventDefault(); applyFilters();">
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
            </div>

            <div class="container-fluid mt-3">
                <div class="row row-cols-1 row-cols-md-3 g-4 p-3 bg-success">

                    <!-- Card Tambah Riwayat Bencana -->
                    <div class="col">
                        <a href="admin/add_wisata.php" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm border-0 text-center" style="cursor: pointer;">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center" style="height: 250px;">
                                    <i class="bi bi-plus-lg text-primary" style="font-size: 3rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Tambah Data Riwayat Bencana</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Card Daftar Wisata -->
                    <?php while ($row = $wisata->fetch_assoc()) { ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0" data-name="<?= htmlspecialchars($row['name']) ?>">

                                <!-- Gambar -->
                                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($row['name']) ?>"
                                    style="height: 250px; object-fit: cover;">

                                <!-- Konten -->
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                                    <hr class="my-2">
                                    <p class="card-text" style="text-align: justify;">
                                        <?= strlen($row['description']) > 300
                                            ? htmlspecialchars(substr($row['description'], 0, 300)) . '...'
                                            : htmlspecialchars($row['description']); ?>
                                    </p>
                                </div>

                                <!-- Footer Tombol Aksi -->
                                <div class="card-footer bg-white border-0 d-flex justify-content-center">
                                    <div class="btn-group w-100" role="group" aria-label="Aksi Wisata">

                                        <!-- Kelola Scene -->
                                        <a href="admin/scenes.php?wisata_id=<?= $row['id'] ?>"
                                            class="btn btn-success btn-sm text-center">
                                            <i class="bi bi-signpost-2"></i><br>Scene
                                        </a>

                                        <!-- Edit -->
                                        <a href="admin/edit_wisata.php?id=<?= $row['id'] ?>"
                                            class="btn btn-warning btn-sm text-center">
                                            <i class="bi bi-pencil-square"></i><br>Edit
                                        </a>

                                        <!-- Hapus -->
                                        <a href="#"
                                            class="btn btn-danger btn-sm text-center delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-id="<?= $row['id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name']) ?>">
                                            <i class="bi bi-trash"></i><br>Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"> <!-- Tambahan: membuat modal di tengah -->
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus <strong id="deleteWisataName">data ini</strong>?
                        <br><span class="text-danger">Tindakan ini tidak bisa dibatalkan!</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Hapus</a>
                    </div>
                </div>
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

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi</h5>
                </div>
                <div class="modal-body">
                    <?= $delete_message; ?>
                </div>
                <div class="modal-footer">
                    <a href="index.php" class="btn btn-primary">OK</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal Hapus
        document.addEventListener("DOMContentLoaded", function() {
            const deleteButtons = document.querySelectorAll(".delete-btn");
            const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
            const deleteWisataName = document.getElementById("deleteWisataName");
            const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function() {
                    const wisataId = this.getAttribute("data-id");
                    const wisataName = this.getAttribute("data-name");

                    deleteWisataName.textContent = wisataName;
                    confirmDeleteBtn.href = "admin/hapus_wisata.php?id=" + wisataId;

                    deleteModal.show();
                });
            });
        });
    </script>

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


        // Tampilkan modal saat halaman dimuat
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    </script>

    <script>
        // Function to apply all filters
        function applyFilters() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const sortOrder = document.getElementById('sortOrder').value;

            // Send request to server with filters
            window.location.href = `?page=1&search=${encodeURIComponent(searchValue)}&sort=${sortOrder}`;
        }

        // Function to filter cards on client side (for instant search)
        function filterCards() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const cards = document.querySelectorAll('.card');

            cards.forEach(function(card) {
                const cardTitle = card.getAttribute('data-name').toLowerCase();
                if (cardTitle.includes(searchValue)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Initialize filters from URL parameters
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);

            // Set search bar value
            if (urlParams.has('search')) {
                document.getElementById('searchBar').value = urlParams.get('search');
                filterCards();
            }

            // Set sort order
            if (urlParams.has('sort')) {
                document.getElementById('sortOrder').value = urlParams.get('sort');
            }
        });
    </script>

    <!-- Bootstrap JS (for the hamburger menu) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fungsi untuk filter cards
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
        // Register service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker terdaftar:', reg.scope))
                    .catch(err => console.log('Service Worker gagal:', err));
            });
        }
    </script>

    <script>
        function applyFilters() {
            const search = document.getElementById('searchBar').value;
            const sort = document.getElementById('sortOrder').value;
            const url = new URL(window.location.href);
            url.searchParams.set('search', search);
            url.searchParams.set('sort', sort);
            url.searchParams.set('page', 1); // Reset ke halaman 1
            window.location.href = url.toString();
        }

        function resetFilters() {
            document.getElementById('searchBar').value = '';
            document.getElementById('sortOrder').value = 'newest';
            applyFilters();
        }

        function filterCards() {
            // Optional: jika kamu ingin filter langsung saat mengetik
            // applyFilters(); // Atau bisa gunakan debounce
        }

        // Menjalankan filter saat tombol Enter ditekan di search input
        document.getElementById("searchBar").addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault(); // Hindari reload default
                applyFilters(); // Jalankan fungsi filter
            }
        });
    </script>



    <?php include 'pengguna_footer.php'; ?>
</body>

</html>