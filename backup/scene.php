<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $wisata_id = $_POST['wisata_id'];
    $name = $_POST['name'];
    $image = $_POST['image_url'];

    $conn->query("INSERT INTO scenes (wisata_id, name, image_url) VALUES ('$wisata_id', '$name', '$image')");
}

$scenes = $conn->query("SELECT * FROM scenes");
?>

<form method="POST">
    <input type="text" name="name" placeholder="Nama Scene" required>
    <input type="text" name="image_url" placeholder="URL Gambar Panorama" required>
    <button type="submit">Tambah Scene</button>
</form>

<ul>
<?php while ($row = $scenes->fetch_assoc()) { ?>
    <li><?php echo $row['name']; ?> - <img src="<?php echo $row['image_url']; ?>" width="100"></li>
<?php } ?>
</ul>
