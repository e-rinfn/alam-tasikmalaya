<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Validasi password baru
if ($new_password !== $confirm_password) {
    $_SESSION['message'] = "Password baru dan konfirmasi tidak cocok.";
    $_SESSION['message_type'] = "error";
    header("Location: akun.php");
    exit;
}

// Ambil password lama dari database
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verifikasi password lama
if (!password_verify($old_password, $user['password'])) {
    $_SESSION['message'] = "Password lama salah.";
    $_SESSION['message_type'] = "error";
    header("Location: akun.php");
    exit;
}

// Hash password baru
$new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

// Update password di database
$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_password_hashed, $user_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Password berhasil diubah.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Terjadi kesalahan saat memperbarui password.";
    $_SESSION['message_type'] = "error";
}

header("Location: akun.php");
exit;
?>
