<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: read.php");
    exit();
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
        die("Record not found");
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
    <title>View History Daerah</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            display: block;
            margin: 0 auto;
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

<body>
    <div class="container mt-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">View History Daerah</h1>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-2 fw-bold">Judul:</div>
                    <div class="col-md-10"><?= htmlspecialchars($record['judul']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2 fw-bold">Nama Daerah:</div>
                    <div class="col-md-10"><?= htmlspecialchars($record['name']) ?></div>
                </div>

                <?php
                function formatDeskripsiToggle($deskripsi)
                {
                    preg_match_all('/\[(\d{4})\](.*?)(?=(\[\d{4}\])|$)/s', $deskripsi, $matches, PREG_SET_ORDER);

                    $output = '<div class="deskripsi-container">';
                    foreach ($matches as $index => $match) {
                        $tahun = $match[1];
                        $konten = trim($match[2]);
                        $output .= "
                        <div class='deskripsi-section mb-3'>
                            <div class='toggle-header rounded' onclick='toggleDeskripsi($index)'>
                                <strong>Tahun $tahun</strong>
                            </div>
                            <div class='toggle-content rounded-bottom' id='content-$index' style='display: none;'>
                                $konten
                            </div>
                        </div>
                        ";
                    }
                    $output .= '</div>';
                    return $output;
                }
                ?>

                <div class="mb-3">
                    <h2 class="h5 mb-3">Deskripsi</h2>
                    <?= formatDeskripsiToggle($record['deskripsi']) ?>
                </div>

                <a href="index.php" class="btn btn-primary mt-3">Kembali</a>
            </div>
        </div>
    </div>

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
</body>

</html>