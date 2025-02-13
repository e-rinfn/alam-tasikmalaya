<?php
session_start();
session_destroy(); // Menghapus semua sesi
header("Location: ../login.php"); // Redirect ke halaman login
exit;
?>
