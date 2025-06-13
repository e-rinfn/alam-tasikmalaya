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
<html>

<head>
    <title>View History Daerah</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .detail-row {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 120px;
        }

        .value {
            display: inline-block;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-link:hover {
            background: #2980b9;
        }

        .toggle-header {
            background-color: #f0f0f0;
            padding: 10px;
            cursor: pointer;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }

        .toggle-content {
            padding: 10px;
            border: 1px solid #ddd;
            border-top: none;
            background-color: #fafafa;
        }

        .deskripsi-section {
            margin-bottom: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .toggle-header {
            background-color: #f0f0f0;
            padding: 10px;
            cursor: pointer;
        }

        .toggle-content {
            padding: 10px;
        }

        .deskripsi-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <!-- <div class="container"> -->
    <h1>View History Daerah</h1>

    <div class="detail-row">
        <span class="label">Judul:</span>
        <span class="value"><?= htmlspecialchars($record['judul']) ?></span>
    </div>
    <div class="detail-row">
        <span class="label">Nama Daerah:</span>
        <span class="value"><?= htmlspecialchars($record['name']) ?></span>
    </div>

    <style>
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

        /* Clear float after image section (optional) */
        .deskripsi-section::after {
            content: "";
            display: block;
            clear: both;
        }
    </style>



    <?php
    function formatDeskripsiToggle($deskripsi)
    {
        // Jangan ubah HTML menjadi teks biasa
        // Jika sebelumnya sudah disimpan dalam bentuk HTML asli dari CKEditor, maka cukup langsung proses
        preg_match_all('/\[(\d{4})\](.*?)(?=(\[\d{4}\])|$)/s', $deskripsi, $matches, PREG_SET_ORDER);

        $output = '<div class="deskripsi-container">';
        foreach ($matches as $index => $match) {
            $tahun = $match[1];
            $konten = trim($match[2]); // Tidak pakai nl2br agar HTML <img> tetap utuh
            $output .= "
        <div class='deskripsi-section'>
            <div class='toggle-header' onclick='toggleDeskripsi($index)'>
                <strong>Tahun $tahun</strong>
            </div>
            <div class='toggle-content' id='content-$index' style='display: none;'>
                $konten
            </div>
        </div>
        ";
        }
        $output .= '</div>';
        return $output;
    }
    ?>

    <script>
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


    <div class="deskripsi">
        <?= formatDeskripsiToggle($record['deskripsi']) ?>
    </div>


    <!-- <div class="detail-row">
        <span class="label">Created At:</span>
        <span class="value"><?= htmlspecialchars($record['created_at']) ?></span>
    </div>

    <div class="detail-row">
        <span class="label">Left Position:</span>
        <span class="value"><?= htmlspecialchars($record['left_position'] ?? 'N/A') ?></span>
    </div>

    <div class="detail-row">
        <span class="label">Top Position:</span>
        <span class="value"><?= htmlspecialchars($record['top_position'] ?? 'N/A') ?></span>
    </div> -->

    <a href="read.php" class="back-link">Kembali Ke List</a>
    <!-- </div> -->

    <script>
        function toggleDeskripsi(index) {
            const content = document.getElementById('content-' + index);
            if (content.style.display === 'none') {
                content.style.display = 'block';
            } else {
                content.style.display = 'none';
            }
        }
    </script>

</body>

</html>