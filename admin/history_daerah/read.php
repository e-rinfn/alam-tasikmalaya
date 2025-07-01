<?php
require_once '../../db.php';

try {
    $sql = "SELECT h.*, w.id AS wisata_id FROM history_daerah h JOIN wisata w ON h.wisata_id = w.id";
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
    <title>History Daerah List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light py-4">

    <div class="container">
        <h1 class="mb-4">History Daerah Records</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Record <?= $_GET['success'] == 1 ? 'created' : ($_GET['success'] == 2 ? 'updated' : 'deleted'); ?> successfully!
            </div>
        <?php endif; ?>

        <a href="create.php" class="btn btn-success mb-3">Create New Record</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <!-- <th>ID</th>
                        <th>Wisata ID</th> -->
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <!-- <th>Created At</th> -->
                        <th>Longitude</th>
                        <th>Latitude</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <!-- <td><?= htmlspecialchars($record['id']) ?></td>
                            <td><?= htmlspecialchars($record['wisata_id']) ?></td> -->
                            <td><?= htmlspecialchars($record['judul']) ?></td>
                            <td><?= htmlspecialchars(substr($record['deskripsi'], 0, 50)) ?>...</td>
                            <!-- <td><?= htmlspecialchars($record['created_at']) ?></td> -->
                            <td><?= htmlspecialchars($record['longitude']) ?></td>
                            <td><?= htmlspecialchars($record['latitude']) ?></td>
                            <td>
                                <a href="view-admin.php?id=<?= $record['id'] . '&wisata_id=' . $record['wisata_id'] ?>" class="btn btn-sm btn-primary mb-1">View</a>
                                <a href="update.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                                <a href="delete.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
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