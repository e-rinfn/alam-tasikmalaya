<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: read.php");
    exit();
}

$id = $_GET['id'];

try {
    $sql = "DELETE FROM history_daerah WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header("Location: read.php?success=3");
    exit();
} catch (PDOException $e) {
    die("Error deleting record: " . $e->getMessage());
}
