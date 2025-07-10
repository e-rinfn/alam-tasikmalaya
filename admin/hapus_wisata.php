<?php
session_start();
include '../config.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Pastikan ID wisata dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID wisata tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

$id = $_GET['id'];

// Gunakan prepared statement untuk keamanan
$query = "DELETE FROM wisata WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../index_admin.php");
} else {
    echo "<script>alert('Gagal menghapus data!'); window.location.href='index.php';</script>";
}

$stmt->close();
$conn->close();
