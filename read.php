<?php
require_once 'db.php';
session_start();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';


// Cek apakah user login dan memiliki role admin ATAU user
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
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

$sql = "SELECT h.*, w.id AS wisata_id, w.name AS wisata_nama 
        FROM history_daerah h 
        JOIN wisata w ON h.wisata_id = w.id";

if (!empty($search)) {
    $sql .= " WHERE h.judul LIKE :search OR h.deskripsi LIKE :search OR w.name LIKE :search";
}

$sql .= " ORDER BY h.id DESC"; // Urutkan yang terbaru di atas

$stmt = $pdo->prepare($sql);

if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
}

$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Bencana</title>
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</head>

<body style="font-family: 'Poppins', sans-serif;">

    <!-- Header -->
    <?php include 'admin_header.php'; ?>

    <main class="container">

        <div class="row align-items-center mt-5 mb-4">
            <div class="col-md-6">
                <h1 class="mb-0 fs-3">Daftar Riwayat Bencana</h1>
            </div>
        </div>

        <hr>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?=
                $_GET['success'] == 1 ? 'Data berhasil disimpan.' : ($_GET['success'] == 2 ? 'Data berhasil diperbarui.' : 'Data berhasil dihapus.')
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>



        <form method="GET" class="mb-4">
            <div class="row g-2 align-items-center">
                <!-- Kolom kiri: tombol Tambah & Kembali -->
                <div class="col-md-4">
                    <a href="javascript:history.go(-1)" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <a href="create.php" class="btn btn-success"> <i class="bi bi-plus-circle"></i> Tambah Riwayat</a>
                </div>

                <!-- Kolom kanan: input + tombol Terapkan & Reset -->
                <div class="col-md-8 d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan judul, deskripsi, atau wisata..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-success">Terapkan</button>
                    <a href="read.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>




        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 250px;">Nama</th>
                        <th style="width: 250px;">Judul</th>
                        <th>Deskripsi</th>
                        <th>Longitude</th>
                        <th>Latitude</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($record['wisata_nama']) ?></td>
                            <td><?= htmlspecialchars($record['judul']) ?></td>
                            <td>
                                <span title="<?= htmlspecialchars($record['deskripsi']) ?>">
                                    <?= (mb_strimwidth($record['deskripsi'], 0, 50, '...')) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($record['longitude']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($record['latitude']) ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="view-admin.php?id=<?= $record['id'] ?>&wisata_id=<?= $record['wisata_id'] ?>" class="btn btn-sm btn-primary" title="Lihat"><i class="bi bi-eye"></i></a>
                                    <a href="update.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus"
                                        data-id="<?= $record['id'] ?>"
                                        data-judul="<?= htmlspecialchars($record['judul']) ?>"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hapusButtons = document.querySelectorAll('.btn-hapus');

            hapusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const judul = this.getAttribute('data-judul');

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: `Data dengan judul "${judul}" akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `delete.php?id=${id}`;
                        }
                    });
                });
            });
        });
    </script>


    <?php include 'pengguna_footer.php'; ?>
</body>



</html>