<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "alam-tasikmalaya");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari tabel history_daerah, diurutkan berdasarkan waktu
$query = "
    SELECT id, judul, deskripsi, YEAR(created_at) AS tahun
    FROM history_daerah
    ORDER BY created_at ASC
";

$result = $conn->query($query);

// Susun data berdasarkan tahun
$historyData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tahun = $row['tahun'];
        $judul = $row['judul'];
        $deskripsi = $row['deskripsi'];

        if (!isset($historyData[$tahun])) {
            $historyData[$tahun] = [];
        }

        $historyData[$tahun][] = "<strong>" . htmlspecialchars($judul) . "</strong>: " . htmlspecialchars($deskripsi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sejarah Wisata Tasikmalaya</title>
</head>

<body>
    <h1>Sejarah Wisata Tasikmalaya</h1>

    <?php if (!empty($historyData)): ?>
        <?php foreach ($historyData as $tahun => $deskripsiList): ?>
            <section id="year-<?= $tahun ?>" style="margin-bottom: 30px;">
                <h2><?= $tahun ?></h2>
                <?php foreach ($deskripsiList as $desc): ?>
                    <p><?= $desc ?></p>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>

        <hr>
        <h3>Navigasi Tahun</h3>
        <ul>
            <?php foreach (array_keys($historyData) as $tahun): ?>
                <li><a href="#year-<?= $tahun ?>"><?= $tahun ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Tidak ada data sejarah yang tersedia.</p>
    <?php endif; ?>

</body>

</html>