<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='../login.php';</script>";
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Ambil role user dari session
$role = $_SESSION['role'];

// Cek apakah user memiliki role yang diperbolehkan (admin atau user)
if ($role !== 'admin' && $role !== 'user') {
    echo "<script>alert('Anda tidak memiliki izin untuk mengakses halaman ini!'); window.location.href='no_access.php';</script>";
    exit;
}

// Ambil data wisata, scene, dan hotspot
$wisata = $conn->query("SELECT * FROM wisata");

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
    <title>Alam Tasikmalaya 360</title>
    <link rel="icon" type="image/png" href="../img/Logo-Putih.png">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">

</head>

<body>

    <?php include 'admin_header.php'; ?>

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

    <!-- Map Section -->
    <div class="container">
        <div class="card mt-4 mb-3 mx-auto" style="max-width: 800px;">
            <div id="leafletMap" style="height: 500px; border-radius: 5px;"></div>
        </div>
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
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel' . $m['id'] . '">' . htmlspecialchars($m['judul']) . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-start" style="text-align: justify;">
                        <div class="modal-image-content">
                            ' . htmlspecialchars_decode($text_peta) . '
                        </div>
                        <a href="../view.php?id=' . $m['id'] . '&wisata_id=' . $m['wisata_id'] . '" class="btn btn-primary mt-3">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>';
        }
    }
    ?>


    <!-- Cards Section -->
    <div class="container" style="min-height: 100vh;">

        <br>
        <div class="row row-cols-1 row-cols-md-3 g-4 p-3 border rounded-3" style="background: linear-gradient(100deg, #001A6E, #16C47F );">

            <!-- Card Tambah Wisata -->
            <div class="col mt-0 p-2">
                <a href="add_wisata.php">
                    <div class="card h-100 shadow-sm border-0 text-center d-flex align-items-center justify-content-center;"
                        data-name="<?= htmlspecialchars($row['name']) ?>"
                        style="cursor: pointer;">
                        <div class="card-body" style="margin-top: 13rem; margin-bottom: 10rem;">
                            <i class="bi bi-plus-lg text-primary" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">Tambah Wisata</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Card Daftar Wisata -->
            <?php while ($row = $wisata->fetch_assoc()) { ?>
                <div class="col mt-0 p-2">
                    <div class="card h-100 shadow-sm border-0"
                        data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-kategori="<?= htmlspecialchars($row['kategori']) ?>">

                        <img src="<?= '../' . htmlspecialchars($row['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                            <hr>
                            <!-- Elemen untuk deskripsi -->
                            <p class="card-text">
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
                        <div class="card-footer bg-white border-0 d-flex justify-content-around flex-wrap">
                            <!-- Tombol Aksi -->
                            <a href="scenes.php?wisata_id=<?= $row['id'] ?>" class="btn btn-success btn-sm flex-grow-1 text-center">
                                <i class="bi bi-signpost-2"></i><br> Kelola Scene
                            </a>
                            <a href="edit_wisata.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm flex-grow-1 text-center">
                                <i class="bi bi-pencil-square"></i><br> Edit
                            </a>
                            <a href="#" class="btn btn-danger btn-sm flex-grow-1 text-center delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= htmlspecialchars($row['name']) ?>">
                                <i class="bi bi-trash"></i><br> Hapus
                            </a>
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

            <!-- Modal Konfirmasi Hapus -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin menghapus <strong id="deleteWisataName"></strong>?
                            Tindakan ini tidak bisa dibatalkan!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    confirmDeleteBtn.href = "hapus_wisata.php?id=" + wisataId;

                    deleteModal.show();
                });
            });
        });
    </script>

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
                            Lihat Detail
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
        function filterCards() {
            var searchValue = document.getElementById('searchBar').value.toLowerCase();
            var selectedKategori = document.getElementById('kategoriFilter').value.toLowerCase();
            var cards = document.querySelectorAll('.card');

            cards.forEach(function(card) {
                var cardTitle = card.getAttribute('data-name')?.toLowerCase() || '';
                var cardKategori = card.getAttribute('data-kategori')?.toLowerCase() || '';

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


    <?php include 'admin_footer.php'; ?>
</body>

</html>