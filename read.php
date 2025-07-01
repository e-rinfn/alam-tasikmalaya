<?php
require_once 'db.php';

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
    <title>Alam Tasikmalaya 360</title>
    <link rel="icon" type="image/png" href="../img/Logo-Putih.png">


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

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background: linear-gradient(100deg, #001A6E, #16C47F );">
        <div class="container">
            <!-- Logo -->

            <a class="navbar-brand text-white" href="admin/index.php"><img src="img/Logo-Putih.png" style="width: 50px;" alt=""> Alam Tasikmalaya 360</a>

            <!-- Menu Navbar -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Link Kelola History -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            <i class="bi bi-house"></i> Kelola History
                        </a>
                    </li>

                    <!-- Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white text-decoration-none" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> - ADMINISTRATOR
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mt-3" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="akun.php"><i class="bi bi-person"></i> Akun</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-book"></i> Petunjuk Penggunaan</a></li>
                            <li><a class="dropdown-item" href="tentang.php"><i class="bi bi-info-circle"></i> Tentang</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>


        </div>
    </nav>

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
                        <th>No</th>
                        <th>Wisata</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Longitude</th>
                        <th>Latitude</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($record['wisata_nama']) ?></td>
                            <td><?= htmlspecialchars($record['judul']) ?></td>
                            <td><?= htmlspecialchars(substr($record['deskripsi'], 0, 50)) ?>...</td>
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