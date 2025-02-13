<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Ambil ID wisata dari URL
$id = $_GET['id'];

// Hapus wisata dari database
$query = "DELETE FROM wisata WHERE id = '$id' AND user_id = '$user_id'";

if ($conn->query($query) === TRUE) {
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}

?>
