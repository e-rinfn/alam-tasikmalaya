<?php
session_start();
include '../config.php';

// Periksa apakah parameter id tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID scene tidak valid!'); window.location.href='scenes.php';</script>";
    exit;
}

$id = $_GET['id'];

// Ambil data scene berdasarkan ID untuk mendapatkan wisata_id dan path gambar panorama
$query = "SELECT wisata_id, panorama FROM scenes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Scene tidak ditemukan!'); window.location.href='scenes.php';</script>";
    exit;
}

$scene = $result->fetch_assoc();
$wisata_id = $scene['wisata_id']; // Simpan wisata_id untuk redirect
$stmt->close();

// Hapus file gambar panorama jika ada
if (!empty($scene['panorama']) && file_exists($scene['panorama'])) {
    unlink($scene['panorama']); // Hapus file dari direktori
}

// Hapus scene dari database
$query = "DELETE FROM scenes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirect ke halaman scenes.php dengan wisata_id yang sesuai
    header("Location: scenes.php?wisata_id=$wisata_id");
    exit;
} else {
    echo "<script>alert('Gagal menghapus scene!'); window.location.href='scenes.php?wisata_id=$wisata_id';</script>";
}

$stmt->close();
?>