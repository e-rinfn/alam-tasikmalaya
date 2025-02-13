<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $loc  = $_POST['location'];

    $conn->query("INSERT INTO wisata (user_id, name, description, location) VALUES ('1', '$name', '$desc', '$loc')");
}

$wisata = $conn->query("SELECT * FROM wisata");
?>

<form method="POST">
    <input type="text" name="name" placeholder="Nama Wisata" required>
    <textarea name="description" placeholder="Deskripsi"></textarea>
    <input type="text" name="location" placeholder="Lokasi">
    <button type="submit">Tambah Wisata</button>
</form>

<ul>
<?php while ($row = $wisata->fetch_assoc()) { ?>
    <li><?php echo $row['name']; ?> - <?php echo $row['location']; ?></li>
<?php } ?>
</ul>
