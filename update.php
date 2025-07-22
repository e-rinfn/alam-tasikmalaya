<?php
require_once 'db.php';

// Ambil ID dari parameter URL
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID tidak valid.");
}

session_start();

// Cek apakah user memiliki hak akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: no_access.php");
    exit;
}

// Query untuk mengambil data yang akan diedit
try {
    $sql = "SELECT * FROM history_daerah WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $history = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$history) {
        die("Data tidak ditemukan.");
    }
} catch (PDOException $e) {
    die("Error loading data: " . $e->getMessage());
}

// Update record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $id,
        'wisata_id' => $_POST['wisata_id'] ?? null,
        'judul' => $_POST['judul'] ?? '',
        'text_peta' => $_POST['teks-peta'] ?? '',
        'deskripsi' => $_POST['deskripsi'] ?? '',
        'longitude' => $_POST['longitude'] ?? null, // Tambahan
        'latitude' => $_POST['latitude'] ?? null    // Tambahan
    ];

    // Validasi dasar
    if (empty($data['wisata_id']) || empty($data['judul'])) {
        die("Wisata dan Judul wajib diisi.");
    }

    // Validasi format longitude dan latitude
    if (!empty($data['longitude']) && !is_numeric($data['longitude'])) {
        die("Longitude harus berupa angka.");
    }

    if (!empty($data['latitude']) && !is_numeric($data['latitude'])) {
        die("Latitude harus berupa angka.");
    }

    try {
        $sql = "UPDATE history_daerah 
                SET wisata_id = :wisata_id, 
                    judul = :judul, 
                    text_peta = :text_peta, 
                    deskripsi = :deskripsi, 
                    longitude = :longitude,
                    latitude = :latitude
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        header("Location: read.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Error updating record: " . $e->getMessage());
    }
}

// Query untuk mengambil data wisata dari database
try {
    $sql = "SELECT id, name FROM wisata ORDER BY name";
    $stmt = $pdo->query($sql);
    $wisataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $wisataList = [];
    $wisataError = "Error loading wisata data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Riwayat Bencana</title>
    <link rel="icon" type="image/png" href="img/Logo-Putih.png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">

    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">
    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

</head>

<body style="font-family: 'Poppins', sans-serif;">
    <?php include 'admin_header.php'; ?>

    <main class="container mt-4">
        <div class="card-body">
            <h1 class="mb-0 fs-3">Ubah Riwayat Bencana</h1>
            <hr>
            <?php if (isset($wisataError)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($wisataError) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="wisata_id" class="form-label">Nama Daerah</label>
                        <select id="wisata_id" name="wisata_id" class="form-select" required>
                            <option value="">-- Pilih Daerah --</option>
                            <?php foreach ($wisataList as $wisata): ?>
                                <option value="<?= htmlspecialchars($wisata['id']) ?>"
                                    <?= $wisata['id'] == $history['wisata_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($wisata['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="judul" class="form-label">Judul</label>
                        <input type="text" id="judul" name="judul" class="form-control" maxlength="150"
                            value="<?= htmlspecialchars($history['judul']) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" id="longitude" name="longitude" class="form-control" maxlength="20"
                            value="<?= htmlspecialchars($history['longitude']) ?>" placeholder="Contoh: 110.123456">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" id="latitude" name="latitude" class="form-control" maxlength="20"
                            value="<?= htmlspecialchars($history['latitude']) ?>" placeholder="Contoh: -7.123456">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="teks-peta" class="form-label">Deskripsi Di Peta</label>
                    <textarea id="teks-peta" name="teks-peta" class="form-control"><?= htmlspecialchars($history['text_peta']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Riwayat Bencana</label>
                    <small class="d-block text-muted mb-2">
                        Tambahkan tahun dalam kurung, contoh: <code>[10 Oktober 2002]</code> diikuti deskripsinya.
                    </small>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" style="height: 200px;"><?= htmlspecialchars($history['deskripsi']) ?></textarea>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-success" id="btnSubmit">Simpan Perubahan</button>
                    <a href="#" class="btn btn-secondary ms-3" id="btnBack">Kembali ke Daftar</a>
                </div>


            </form>
        </div>
    </main>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('btnSubmit').addEventListener('click', function(e) {
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Pastikan data sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim form secara manual
                    e.target.closest('form').submit();
                }
            });
        });

        document.getElementById('btnBack').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Kembali ke Daftar?',
                text: "Perubahan yang belum disimpan akan hilang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'read.php';
                }
            });
        });
    </script>


    <script>
        class MyUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }

            upload() {
                return this.loader.file
                    .then(file => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => {
                            const img = new Image();
                            img.src = reader.result;
                            img.onload = () => {
                                // Resize image
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');

                                const maxWidth = 720;
                                const scale = maxWidth / img.width;
                                canvas.width = maxWidth;
                                canvas.height = img.height * scale;

                                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                canvas.toBlob(blob => {
                                    const formData = new FormData();
                                    formData.append('file', blob, file.name);

                                    fetch('uploads.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(result => {
                                            if (result.url) {
                                                resolve({
                                                    default: result.url
                                                });
                                            } else {
                                                reject(result.error || "Upload failed.");
                                            }
                                        })
                                        .catch(() => reject("Network error."));
                                }, file.type);
                            };
                        };
                    }));
            }

            abort() {}
        }

        function MyCustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new MyUploadAdapter(loader);
            };
        }

        // Inisialisasi CKEditor untuk teks peta
        ClassicEditor
            .create(document.querySelector('#teks-peta'), {
                extraPlugins: [MyCustomUploadAdapterPlugin],
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'link', 'alignment', '|',
                    'imageUpload', 'imageStyle:alignLeft', 'imageStyle:alignCenter', 'imageStyle:alignRight', '|',
                    'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'
                ],
                image: {
                    styles: [
                        'alignLeft', 'alignCenter', 'alignRight'
                    ],
                    toolbar: [
                        'imageStyle:alignLeft', 'imageStyle:alignCenter', 'imageStyle:alignRight',
                        '|', 'resizeImage', '|', 'imageTextAlternative'
                    ]
                }
            })
            .catch(error => console.error(error));

        // Inisialisasi CKEditor untuk deskripsi
        ClassicEditor
            .create(document.querySelector('#deskripsi'), {
                extraPlugins: [MyCustomUploadAdapterPlugin],
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'link', 'alignment', '|',
                    'imageUpload', 'imageStyle:alignLeft', 'imageStyle:alignCenter', 'imageStyle:alignRight', '|',
                    'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'
                ],
                image: {
                    styles: [
                        'alignLeft', 'alignCenter', 'alignRight'
                    ],
                    toolbar: [
                        'imageStyle:alignLeft', 'imageStyle:alignCenter', 'imageStyle:alignRight',
                        '|', 'resizeImage', '|', 'imageTextAlternative'
                    ]
                }
            })
            .catch(error => console.error(error));
    </script>

    <?php include 'pengguna_footer.php'; ?>

</body>

</html>