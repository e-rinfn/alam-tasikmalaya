<?php
require_once 'db.php';
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: read.php");
    exit();
}

// Ambil wisata_id dari parameter URL atau sesuaikan dengan kebutuhan
$wisata_id = isset($_GET['wisata_id']) ? intval($_GET['wisata_id']) : 0;

// Ambil data wisata berdasarkan wisata_id
$wisata = $conn->query("SELECT * FROM wisata WHERE id = $wisata_id");
$row = $wisata->fetch_assoc();

// Ambil data scene berdasarkan wisata_id
$scenes = $conn->query("SELECT * FROM scenes WHERE wisata_id = $wisata_id");
$sceneList = [];
while ($scene = $scenes->fetch_assoc()) {
    $sceneList[] = $scene;
}

$id = $_GET['id'];

try {
    // Query untuk mengambil data history daerah beserta data wisata terkait
    $sql = "SELECT h.*, w.name 
            FROM history_daerah h
            LEFT JOIN wisata w ON h.wisata_id = w.id
            WHERE h.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        die("Data History Daerah tidak ditemukan.");
    }
} catch (PDOException $e) {
    die("Error fetching record: " . $e->getMessage());
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
    <link rel="stylesheet" href="css/informasi-wisata.css">
    <style>
        /* Style untuk gambar dari CKEditor */
        .deskripsi-container img {
            max-width: 100%;
            height: auto;
            cursor: pointer;
            margin: 10px 0;
        }

        /* Alignments dari CKEditor */
        .image-style-align-left {
            float: left;
            margin-right: 1em;
        }

        .image-style-align-center {
            float: center;
            margin-right: 1em;
        }

        .image-style-align-right {
            float: right;
            margin-left: 1em;
        }

        /* Clear float after image section */
        .deskripsi-section::after {
            content: "";
            display: block;
            clear: both;
        }

        /* Style untuk toggle section */
        .toggle-header {
            cursor: pointer;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            background-color: rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .toggle-content {
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-top: none;
        }
    </style>
</head>

<body style="font-family: 'Poppins', sans-serif;">

    <?php include 'pengguna_header.php'; ?>
    <main class="container mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 fs-3">Riwayat <?= htmlspecialchars($row['name']) ?></h1>
                <a href="javascript:history.go(-1)" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Judul:</div>
                <div class="col-md-6"><?= htmlspecialchars($record['judul']) ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Nama Daerah:</div>
                <div class="col-md-6"><?= htmlspecialchars($record['name']) ?></div>
            </div>
            <div class="card">
                <div class="card-body">
                    <hr>
                    <h2 class="text-center h5 mb-3">Deskripsi Daerah</h2>
                    <hr>

                    <?php
                    // Tambahkan class "img-fluid" ke semua tag <img> jika belum ada
                    $text_peta = preg_replace(
                        '/<img(?![^>]*class=["\'][^"\']*img-fluid[^"\']*["\'])/i',
                        '<img class="img-fluid"',
                        $record['text_peta']
                    );
                    ?>

                    <div class="row mb-3">
                        <div class="col-md-12" style="text-align: justify;"><?= htmlspecialchars_decode($text_peta) ?></div>
                    </div>

                    <?php
                    function formatDeskripsiToggle($deskripsi)
                    {
                        // Gunakan regex untuk memecah deskripsi berdasarkan tahun
                        // preg_match_all('/\[(\d{4})\](.*?)(?=(\[\d{4}\])|$)/s', $deskripsi, $matches, PREG_SET_ORDER);

                        // Atau jika formatnya adalah [1 Januari 2020] atau [31 Desember 2020]
                        preg_match_all('/\[(\d{1,2}\s+\p{L}+\s+\d{4})\](.*?)(?=\[\d{1,2}\s+\p{L}+\s+\d{4}\]|\z)/su', $deskripsi, $matches, PREG_SET_ORDER);


                        $output = '<div class="deskripsi-container">';
                        foreach ($matches as $index => $match) {
                            $tahun = $match[1];
                            $konten = trim($match[2]);
                            $output .= "
                        <div class='deskripsi-section mb-3'>
                            <div class='toggle-header rounded' onclick='toggleDeskripsi($index)'>
                                <strong>$tahun</strong>
                            </div>
                            <div class='toggle-content rounded-bottom' id='content-$index' style='display: none; text-align: justify;'>
                                $konten
                            </div>
                        </div>
                        ";
                        }
                        $output .= '</div>';
                        return $output;
                    }
                    ?>
                    <hr>
                    <div class="mb-3">
                        <h2 class="text-center h5 mb-3">Sejarah Daerah</h2>
                        <hr>

                        <?= formatDeskripsiToggle($record['deskripsi']) ?>
                    </div>

                    <!-- <a href="index.php" class="btn btn-primary mt-3">Kembali</a> -->
                </div>
            </div>
        </div>
        <!-- Bagian Virtual Tour 360 Derajat -->
        <div class="col-md-12 vertical-images p-3">
            <h3 class="text-center">Virtual Tour 360</h3>
            <hr>
            <div class="bg-success" style="max-height: 1000px; overflow-y: auto; border: 2px solid #ddd; border-radius: 8px; padding: 10px;">
                <?php if (!empty($sceneList)): ?>
                    <?php foreach ($sceneList as $scene): ?>
                        <div class="card image-card mb-3" onclick="window.location.href='pengguna/view_tour.php?wisata_id=<?= $wisata_id ?>&scene_id=<?= $scene['id'] ?>';" style="cursor: pointer; border: 1px solid grey">
                            <img src="admin/<?= htmlspecialchars($scene['panorama']) ?>" alt="<?= htmlspecialchars($scene['name']) ?>" class="card-img-top">
                            <div class="card-body">
                                <h6 class="text-center"><?= htmlspecialchars($scene['name']) ?></h6>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card text-center text-muted">
                        <i class="bi bi-exclamation-circle"></i> Tidak ada scene tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleDeskripsi(index) {
            const content = document.getElementById('content-' + index);
            if (content.style.display === 'none') {
                content.style.display = 'block';
            } else {
                content.style.display = 'none';
            }
        }

        // Make images clickable to open in new tab
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".deskripsi-container img").forEach(img => {
                const src = img.getAttribute("src");
                if (src && !img.closest("a")) {
                    const link = document.createElement("a");
                    link.href = src;
                    link.target = "_blank";
                    link.appendChild(img.cloneNode(true));
                    img.replaceWith(link);
                }
            });
        });
    </script>

    <?php include 'pengguna_footer.php'; ?>

</body>

</html>