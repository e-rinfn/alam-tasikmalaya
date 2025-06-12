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

    <!-- Search and Filter Section -->
    <div class="search-bar d-flex flex-wrap gap-2 align-items-center justify-content-center my-3">
        <input type="text" class="search-input form-control" id="searchBar" placeholder="Cari Objek Wisata..." oninput="filterCards()">

        <select class="form-select w-auto" id="kategoriFilter" onchange="filterCards()">
            <option value="">Semua Kategori</option>
            <?php
            $kategori = $conn->query("SELECT DISTINCT kategori FROM wisata");
            if ($kategori) {
                while ($kat = $kategori->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($kat['kategori']) . '">' . htmlspecialchars($kat['kategori']) . '</option>';
                }
            } else {
                echo '<option value="">Error loading categories</option>';
            }
            ?>
        </select>
    </div>

    <!-- Map and Pointers Section -->
    <div class="card mt-5 mb-3 mx-auto" style="max-width: 800px; position: relative;">
        <img src="img/map.png" alt="Peta Tasikmalaya" class="img-fluid" id="peta-gambar">

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

    <!-- Dynamic Modals from Database -->
    <?php
    $modalQuery = $conn->query("SELECT * FROM history_daerah");
    if ($modalQuery && $modalQuery->num_rows > 0) {
        while ($m = $modalQuery->fetch_assoc()) {
            echo '<div class="modal fade" id="modalTitik' . $m['id'] . '" tabindex="-1" aria-labelledby="modalLabel' . $m['id'] . '" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title" id="modalLabel' . $m['id'] . '">' . htmlspecialchars($m['judul']) . '</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                          </div>
                          <div class="modal-body text-center">
                              <p>' . nl2br(htmlspecialchars($m['deskripsi'])) . '</p>
                              <a href="view.php?id=' . $m['id'] . '" class="btn btn-primary">Lihat Detail</a>
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

    <?php include 'pengguna_footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        function filterCards() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const kategoriValue = document.getElementById('kategoriFilter').value.toLowerCase();

            // You'll need to implement the actual filtering logic here
            // This would depend on how your cards are structured in the HTML
            console.log("Filtering:", searchValue, kategoriValue);
        }
    </script>
</body>

</html>