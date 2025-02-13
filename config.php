<?php
$host = "localhost";
$user = "root"; // Sesuaikan dengan database kamu
$pass = "";
$db   = "virtual_tour";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
