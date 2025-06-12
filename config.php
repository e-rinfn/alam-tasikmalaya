<?php
$host = "localhost";
$user = "root"; // Sesuaikan dengan database kamu
$pass = "";
$db   = "alam-tasikmalaya";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
