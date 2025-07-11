<?php
require_once '../../db.php';

// Insert new record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'wisata_id' => $_POST['wisata_id'] ?? null,
        'judul' => $_POST['judul'] ?? '',
        'text_peta' => $_POST['teks-peta'] ?? '', // Perbaikan nama field
        'deskripsi' => $_POST['deskripsi'] ?? '',


        'longitude' => $_POST['longitude'] ?? null,
        'latitude' => $_POST['latitude'] ?? null
    ];

    // Validasi dasar
    if (empty($data['wisata_id']) || empty($data['judul'])) {
        die("Wisata dan Judul wajib diisi.");
    }

    try {
        $sql = "INSERT INTO history_daerah 
                (wisata_id, judul, text_peta, deskripsi, longitude, latitude, created_at) 
                VALUES 
                (:wisata_id, :judul, :text_peta, :deskripsi, :longitude, :latitude, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        header("Location: read.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Error creating record: " . $e->getMessage());
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
    <title>Tambah History Daerah</title>

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .ck-editor__editable {
            min-height: 200px;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 5px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Tambah History Daerah</h1>

        <?php if (isset($wisataError)): ?>
            <div class="error-message"><?= htmlspecialchars($wisataError) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="wisata_id">Wisata:</label>
                <select id="wisata_id" name="wisata_id" required>
                    <option value="">-- Pilih Wisata --</option>
                    <?php foreach ($wisataList as $wisata): ?>
                        <option value="<?= htmlspecialchars($wisata['id']) ?>">
                            <?= htmlspecialchars($wisata['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="judul">Judul:</label>
                <input type="text" id="judul" name="judul" maxlength="150" required>
            </div>

            <div class="form-group">
                <label for="teks-peta">Teks di peta:</label>
                <textarea id="teks-peta" name="teks-peta"></textarea>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi History: (tambahkan tahun didalam kurung ini []. contoh [2025] kemudian diikuti text deskripsi tahun tersebut)</label>
                <textarea id="deskripsi" name="deskripsi"></textarea>
            </div>

            <div class="form-group">
                <label for="longitude">Longitude:</label>
                <input type="text" id="longitude" name="longitude" maxlength="20"
                    placeholder="Contoh: 110.123456">
            </div>

            <div class="form-group">
                <label for="latitude">Latitude:</label>
                <input type="text" id="latitude" name="latitude" maxlength="20"
                    placeholder="Contoh: -7.123456">
            </div>

            <button type="submit" class="btn">Simpan</button>
        </form>

        <a href="read.php" class="back-link">Kembali ke Daftar</a>
    </div>

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

                                    fetch('aplod.php', {
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
</body>

</html>