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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">

    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">

</head>
<body>

<?php include 'pengguna_header.php'; ?>

<!-- Form Pencarian -->
<div class="search-bar">
    <input type="text" class="search-input" id="searchBar" placeholder="Cari Objek Wisata..." oninput="filterCards()">
</div>

<!-- Cards Section -->
<div class="container" style="min-height: 100vh;">


<br>
<div class="row row-cols-1 row-cols-md-3 g-4 bg-secondary p-3 border rounded-3">



<!-- Card Daftar Wisata -->
<?php while ($row = $wisata->fetch_assoc()) { ?>
        <div class="col mt-0 p-2">
            <div class="card h-100 shadow-sm border-0" data-name="<?= htmlspecialchars($row['name']) ?>">
                <img src="<?= '' . htmlspecialchars($row['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height: 200px; object-fit: cover;">
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
                <hr>
                <div class="d-flex justify-content-center mb-3">
                    <a href="informasi_wisata.php?wisata_id=<?= $row['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-eye"></i> - Lihat Selengkapnya
                    </a>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.2/purify.min.js"></script>
            <script>
                // Tampilkan deskripsi wisata
                document.addEventListener("DOMContentLoaded", function () {
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

            cards.forEach(function (card) {
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

<?php include 'pengguna_footer.php'; ?>
</body>
</html>
