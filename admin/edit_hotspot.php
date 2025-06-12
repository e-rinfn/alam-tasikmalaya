<?php
session_start();
include '../config.php';

// Pastikan admin sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

// Ambil data dari parameter URL
$hotspot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scene_id = isset($_GET['scene_id']) ? intval($_GET['scene_id']) : 0;

// Ambil data hotspot
$hotspot = [];
if ($hotspot_id > 0) {
    $query = "SELECT * FROM hotspots WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hotspot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotspot = $result->fetch_assoc();
    $stmt->close();
}

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pitch = $_POST['pitch'];
    $yaw = $_POST['yaw'];
    $type = $_POST['type'];
    $text = $_POST['text'];
    $description = $_POST['description'];

    $query = "UPDATE hotspots SET 
                pitch = ?,
                yaw = ?,
                type = ?,
                text = ?,
                description = ?
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ddsssi", $pitch, $yaw, $type, $text, $description, $hotspot_id);

    if ($stmt->execute()) {
        echo "<script>alert('Hotspot berhasil diperbarui!'); window.location.href='hotspots.php?scene_id=$scene_id';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui hotspot!');</script>";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hotspot</title>
	<link rel="icon" type="image/png" href="../img/Logo-Putih.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Bootstrap JS & Popper.js (Wajib untuk Dropdown) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>

<?php include 'admin_header.php'; ?>


<div class="container mt-4 p-3 mb-3 rounded">
    <h2>Edit Hotspot</h2>
    
    <form method="POST">
        <div class="mb-3" hidden>
            <label class="form-label">Pitch</label>
            <input type="number" step="0.1" class="form-control" name="pitch" 
                   value="<?= htmlspecialchars($hotspot['pitch'] ?? '') ?>" required>
        </div>
        
        <div class="mb-3" hidden>
            <label class="form-label">Yaw</label>
            <input type="number" step="0.1" class="form-control" name="yaw" 
                   value="<?= htmlspecialchars($hotspot['yaw'] ?? '') ?>" required>
        </div>
        
        <div class="mb-3" hidden>
            <label class="form-label">Tipe</label>
            <select class="form-select" name="type" required>
                <option value="info" <?= ($hotspot['type'] ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                <option value="scene" <?= ($hotspot['type'] ?? '') === 'scene' ? 'selected' : '' ?>>Scene</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Label Hotspot</label>
            <input type="text" class="form-control" name="text" 
                   value="<?= htmlspecialchars($hotspot['text'] ?? '') ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea id="description" class="form-control" name="description" rows="4" required>
                <?= htmlspecialchars($hotspot['description'] ?? '') ?>
            </textarea>
        </div>

        <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
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

                                    const maxWidth = 350; // Atur ukuran maksimum
                                    const scale = maxWidth / img.width;
                                    canvas.width = maxWidth;
                                    canvas.height = img.height * scale;

                                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                    canvas.toBlob(blob => {
                                        const formData = new FormData();
                                        formData.append('file', blob, file.name);

                                        fetch('upload.php', { method: 'POST', body: formData })
                                            .then(response => response.json())
                                            .then(result => {
                                                if (result.url) {
                                                    resolve({ default: result.url });
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

            ClassicEditor
                .create(document.querySelector('#description'), {
                    extraPlugins: [MyCustomUploadAdapterPlugin],
                    toolbar: [
                        'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 
                        'blockQuote', 'insertImage', 'undo', 'redo'
                    ]
                })
                .catch(error => console.error(error));
        </script>
        <hr>
        <a href="hotspots.php?scene_id=<?= $scene_id ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> - Simpan Perubahan Hotspot</button>
    </form>
    <br>
</div>

</body>
</html>