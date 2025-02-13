<?php
session_start();
include 'config.php';

// Ambil data wisata, scene, dan hotspot
$wisata = $conn->query("SELECT * FROM wisata");
$scenes = $conn->query("SELECT * FROM scenes");
$hotspots = $conn->query("SELECT * FROM hotspots");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h2>Dashboard Admin</h2>
    
    <!-- Section: Kelola Wisata -->
    <div class="card mt-3">
        <div class="card-header">Kelola Wisata</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Wisata</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $wisata->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td>
                                <a href="scenes.php?wisata_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Kelola Scene</a>
                                <a href="delete_wisata.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                                <a href="tour.php?wisata_id=<?= $row['id'] ?>" class="btn btn-sm btn-info" target="_blank">Lihat Virtual Tour</a>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="add_wisata.php" class="btn btn-success">Tambah Wisata</a>
        </div>
    </div>

    <!-- Section: Kelola Scene -->
    <div class="card mt-3">
        <div class="card-header">Kelola Scene</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Scene</th>
                        <th>Gambar Panorama</th>
                        <th>Wisata</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($scene = $scenes->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $scene['id'] ?></td>
                            <td><?= $scene['name'] ?></td>
                            <td><?= $scene['panorama'] ?></td>
                            <td><?= $scene['wisata_id'] ?></td>
                            <td>
                                <a href="hotspot.php?scene_id=<?= $scene['id'] ?>" class="btn btn-sm btn-primary">Kelola Hotspot</a>
                                <a href="delete_scene.php?id=<?= $scene['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="add_scene.php" class="btn btn-success">Tambah Scene</a>
        </div>
    </div>

    <!-- Section: Kelola Hotspot -->
    <div class="card mt-3">
        <div class="card-header">Kelola Hotspot</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Scene</th>
                        <th>Jenis</th>
                        <th>Teks</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hotspot = $hotspots->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $hotspot['id'] ?></td>
                            <td><?= $hotspot['scene_id'] ?></td>
                            <td><?= $hotspot['type'] ?></td>
                            <td><?= $hotspot['text'] ?></td>
                            <td>
                                <a href="delete_hotspot.php?id=<?= $hotspot['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="hotspot.php" class="btn btn-success">Tambah Hotspot</a>
        </div>
    </div>

    <!-- Section: Preview Virtual Tour -->
    <div class="card mt-3">
        <div class="card-header">Preview Virtual Tour</div>
        <div class="card-body">
            <a href="index.php" class="btn btn-primary">Lihat Virtual Tour</a>
        </div>
    </div>

</div>

</body>
</html>
