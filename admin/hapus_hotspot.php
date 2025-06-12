<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID hotspot dari parameter URL
$hotspot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scene_id = isset($_GET['scene_id']) ? intval($_GET['scene_id']) : 0;

if ($hotspot_id === 0 || $scene_id === 0) {
    header("Location: scenes.php");
    exit;
}

// Hapus hotspot dari database
$query = "DELETE FROM hotspots WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hotspot_id);

if ($stmt->execute()) {
    header("Location: hotspots.php?scene_id=$scene_id&status=success");
} else {
    header("Location: hotspots.php?scene_id=$scene_id&status=error");
}

$stmt->close();
?>
