<?php
session_start();
include 'config.php';

// Ambil daftar scene untuk pilihan
$scenes = $conn->query("SELECT * FROM scenes");

// Proses penyimpanan hotspot
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scene_id = $_POST['scene_id'];
    $type = $_POST['type'];
    $text = $_POST['text'];
    $yaw = $_POST['yaw'];
    $pitch = $_POST['pitch'];
    $targetYaw = !empty($_POST['targetYaw']) ? $_POST['targetYaw'] : 'NULL';
    $target_scene_id = !empty($_POST['target_scene_id']) ? $_POST['target_scene_id'] : 'NULL';
    $description = !empty($_POST['description']) ? "'" . $conn->real_escape_string($_POST['description']) . "'" : "NULL";

    $sql = "INSERT INTO hotspots (scene_id, type, text, yaw, pitch, targetYaw, target_scene_id, description) 
            VALUES ('$scene_id', '$type', '$text', '$yaw', '$pitch', $targetYaw, $target_scene_id, $description)";
    
    if ($conn->query($sql) === TRUE) {
        $message = '<div class="alert alert-success">Hotspot berhasil ditambahkan!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
    }
}

// Ambil semua hotspot yang ada
$hotspots = $conn->query("SELECT * FROM hotspots");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Hotspot</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tambah Hotspot</h4>
                    </div>
                    <div class="card-body">
                        <?= $message ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Scene</label>
                                <select name="scene_id" class="form-select" required>
                                    <option value="">Pilih Scene</option>
                                    <?php while ($scene = $scenes->fetch_assoc()) { ?>
                                        <option value="<?= $scene['id']; ?>"><?= $scene['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jenis Hotspot</label>
                                <select name="type" id="typeSelect" class="form-select" required>
                                    <option value="scene">Navigasi ke Scene Lain</option>
                                    <option value="info">Informasi</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Label Hotspot</label>
                                <input type="text" name="text" class="form-control" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Yaw (Posisi Horizontal)</label>
                                    <input type="number" name="yaw" step="0.01" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pitch (Posisi Vertikal)</label>
                                    <input type="number" name="pitch" step="0.01" class="form-control" required>
                                </div>
                            </div>

                            <div id="sceneTarget" class="mt-3">
                                <label class="form-label">Pindah ke Scene</label>
                                <select name="target_scene_id" class="form-select">
                                    <option value="">Pilih Scene Tujuan</option>
                                    <?php
                                    $scenes->data_seek(0);
                                    while ($scene = $scenes->fetch_assoc()) { ?>
                                        <option value="<?= $scene['id']; ?>"><?= $scene['name']; ?></option>
                                    <?php } ?>
                                </select>

                                <label class="form-label mt-2">Target Yaw</label>
                                <input type="number" name="targetYaw" step="0.01" class="form-control">
                            </div>

                            <div id="infoTarget" class="mt-3" style="display: none;">
                                <label class="form-label">Deskripsi Informasi</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100 mt-3">Tambah Hotspot</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0">Daftar Hotspot</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($hotspots->num_rows > 0) { ?>
                            <ul class="list-group">
                                <?php while ($hotspot = $hotspots->fetch_assoc()) { ?>
                                    <li class="list-group-item">
                                        <strong><?= $hotspot['text']; ?></strong> - <span class="badge bg-info"><?= ucfirst($hotspot['type']); ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <p class="text-muted">Belum ada hotspot.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.getElementById("typeSelect").addEventListener("change", function () {
        let type = this.value;
        document.getElementById("sceneTarget").style.display = (type === "scene") ? "block" : "none";
        document.getElementById("infoTarget").style.display = (type === "info") ? "block" : "none";
    });
    </script>
</body>
</html>
