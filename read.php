<?php
require_once 'db.php';
session_start();

// Cek apakah user memiliki hak akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: no_access.php");
    exit;
}

try {
    $sql = "SELECT h.*, w.id AS wisata_id, w.name AS wisata_nama FROM history_daerah h JOIN wisata w ON h.wisata_id = w.id";
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching records: " . $e->getMessage());
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

</head>

<body style="font-family: 'Poppins', sans-serif;">

    <!-- Header -->
    <?php include 'admin_header.php'; ?>

    <div class="container">
        <h1 class="mb-4 mt-3">Daftar History Daerah</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Record <?= $_GET['success'] == 1 ? 'created' : ($_GET['success'] == 2 ? 'updated' : 'deleted'); ?> successfully!
            </div>
        <?php endif; ?>

        <a href="create.php" class="btn btn-success mb-3">Tambah History Baru</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Wisata</th>
                        <th>Judul</th>
                        <th style="width: 200px;">Deskripsi</th>
                        <th>Longitude</th>
                        <th>Latitude</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($record['wisata_nama']) ?></td>
                            <td><?= htmlspecialchars($record['judul']) ?></td>
                            <td>
                                <span title="<?= htmlspecialchars($record['deskripsi']) ?>">
                                    <?= htmlspecialchars(mb_strimwidth($record['deskripsi'], 0, 50, '...')) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($record['longitude']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($record['latitude']) ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="view-admin.php?id=<?= $record['id'] ?>&wisata_id=<?= $record['wisata_id'] ?>" class="btn btn-sm btn-primary" title="Lihat"><i class="bi bi-eye"></i></a>
                                    <a href="update.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <a href="delete.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Hapus data ini?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>