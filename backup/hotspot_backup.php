<?php
session_start();
include 'config.php';

// Ambil daftar scene untuk pilihan
$scenes = $conn->query("SELECT * FROM scenes");

// Proses penyimpanan hotspot
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scene_id = $_POST['scene_id'];
    $type = $_POST['type'];
    $text = $_POST['text'];
    $yaw = $_POST['yaw'];
    $pitch = $_POST['pitch'];
    $targetYaw = !empty($_POST['targetYaw']) ? $_POST['targetYaw'] : 'NULL';
    $target_scene_id = !empty($_POST['target_scene_id']) ? $_POST['target_scene_id'] : 'NULL';
    $description = !empty($_POST['description']) ? "'".$_POST['description']."'" : "NULL";

    $sql = "INSERT INTO hotspots (scene_id, type, text, yaw, pitch, targetYaw, target_scene_id, description) 
            VALUES ('$scene_id', '$type', '$text', '$yaw', '$pitch', $targetYaw, $target_scene_id, $description)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Hotspot berhasil ditambahkan!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Ambil semua hotspot yang ada
$hotspots = $conn->query("SELECT * FROM hotspots");
?>

<h2>Tambah Hotspot</h2>
<form method="POST">
    <label>Scene:</label>
    <select name="scene_id" required>
        <?php while ($scene = $scenes->fetch_assoc()) { ?>
            <option value="<?php echo $scene['id']; ?>"><?php echo $scene['name']; ?></option>
        <?php } ?>
    </select>

    <label>Jenis Hotspot:</label>
    <select name="type" id="typeSelect" required>
        <option value="scene">Navigasi ke Scene Lain</option>
        <option value="info">Informasi</option>
    </select>

    <label>Label Hotspot:</label>
    <input type="text" name="text" required>

    <label>Yaw (Posisi Horizontal):</label>
    <input type="number" name="yaw" step="0.01" required>

    <label>Pitch (Posisi Vertikal):</label>
    <input type="number" name="pitch" step="0.01" required>

    <div id="sceneTarget">
        <label>Pindah ke Scene:</label>
        <select name="target_scene_id">
            <option value="">Pilih Scene Tujuan</option>
            <?php
            $scenes->data_seek(0); // Reset pointer query
            while ($scene = $scenes->fetch_assoc()) { ?>
                <option value="<?php echo $scene['id']; ?>"><?php echo $scene['name']; ?></option>
            <?php } ?>
        </select>

        <label>Target Yaw:</label>
        <input type="number" name="targetYaw" step="0.01">
    </div>

    <div id="infoTarget" style="display: none;">
        <label>Deskripsi Informasi:</label>
        <textarea name="description"></textarea>
    </div>

    <button type="submit">Tambah Hotspot</button>
</form>

<h2>Daftar Hotspot</h2>
<ul>
    <?php while ($hotspot = $hotspots->fetch_assoc()) { ?>
        <li><?php echo $hotspot['text']; ?> - <?php echo $hotspot['type']; ?></li>
    <?php } ?>
</ul>

<script>
document.getElementById("typeSelect").addEventListener("change", function() {
    let type = this.value;
    document.getElementById("sceneTarget").style.display = (type === "scene") ? "block" : "none";
    document.getElementById("infoTarget").style.display = (type === "info") ? "block" : "none";
});
</script>
