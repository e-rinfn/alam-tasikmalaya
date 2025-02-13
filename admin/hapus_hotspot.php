<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil ID hotspot dari parameter URL
$hotspot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scene_id = isset($_GET['scene_id']) ? intval($_GET['scene_id']) : 0;

if ($hotspot_id === 0 || $scene_id === 0) {
    echo "<script>alert('Parameter tidak valid!'); window.location.href='scenes.php';</script>";
    exit;
}

// Hapus hotspot dari database
$query = "DELETE FROM hotspots WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hotspot_id);

if ($stmt->execute()) {
    echo "<script>alert('Hotspot berhasil dihapus!'); window.location.href='hotspots.php?scene_id=$scene_id';</script>";
} else {
    echo "<script>alert('Gagal menghapus hotspot!'); window.location.href='hotspots.php?scene_id=$scene_id';</script>";
}

$stmt->close();
?>