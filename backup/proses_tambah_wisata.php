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

// Inisialisasi error dan success
$errors = [];
$success = "";

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $image_url = null; // Default gambar kosong

    // Validasi input kosong
    if (empty($name)) $errors[] = "Nama wisata tidak boleh kosong!";
    if (empty($description)) $errors[] = "Deskripsi wisata tidak boleh kosong!";
    if (empty($location)) $errors[] = "Lokasi wisata tidak boleh kosong!";

    // Proses upload gambar jika ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = $_FILES['image']['name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $image_size = $_FILES['image']['size'];

        // Validasi ukuran file (maks 2MB)
        if ($image_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran gambar terlalu besar! Maksimal 2MB.";
        }

        // Validasi ekstensi file (hanya gambar)
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_ext, $valid_ext)) {
            $errors[] = "Format gambar tidak valid! Hanya JPG, JPEG, PNG, atau GIF.";
        }

        // Jika validasi berhasil, simpan gambar
        if (empty($errors)) {
            $upload_dir = '../img/thumbnail/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_image_name = uniqid() . '.' . $image_ext;
            if (move_uploaded_file($image_tmp, $upload_dir . $new_image_name)) {
                $image_url = 'img/thumbnail/' . $new_image_name; // Simpan path gambar relatif
            } else {
                $errors[] = "Gagal mengunggah gambar!";
            }
        }
    }

    // Jika tidak ada error, masukkan ke database
    if (empty($errors)) {
        $query = "INSERT INTO wisata (name, description, location, image_url, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $name, $description, $location, $image_url, $user_id);

        if ($stmt->execute()) {
            $success = "Wisata berhasil ditambahkan!";
        } else {
            $errors[] = "Gagal menambahkan wisata!";
        }

        $stmt->close();
    }
}
?>