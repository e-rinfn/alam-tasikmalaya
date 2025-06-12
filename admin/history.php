<?php
include '../config.php';

$message = '';

// Ambil data wisata untuk dropdown
$wisataResult = $conn->query("SELECT id, name FROM wisata ORDER BY name ASC");

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wisata_id = intval($_POST['wisata_id']);
    $judul = $conn->real_escape_string($_POST['judul']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $map_url = $conn->real_escape_string($_POST['map_url'] ?? '');
    $left_position = $conn->real_escape_string($_POST['left_position'] ?? '');
    $top_position = $conn->real_escape_string($_POST['top_position'] ?? '');

    if ($wisata_id && $judul) {
        $sql = "INSERT INTO history_daerah (wisata_id, judul, deskripsi, map_url, left_position, top_position)
                VALUES ($wisata_id, '$judul', '$deskripsi', '$map_url', '$left_position', '$top_position')";
        if ($conn->query($sql)) {
            $message = "Data history daerah berhasil disimpan.";
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Wisata dan judul harus diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Input History Daerah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        h1,
        h2 {
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="url"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #aaa;
            border-radius: 4px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #2e8b57;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #246f47;
        }

        .message {
            background: #e0ffe0;
            border: 1px solid #2e8b57;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background: #eee;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Input History Daerah</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="wisata_id">Pilih Wisata</label>
            <select name="wisata_id" id="wisata_id" required>
                <option value="">-- Pilih Wisata --</option>
                <?php while ($row = $wisataResult->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="judul">Judul History</label>
            <input type="text" name="judul" id="judul" maxlength="150" required>

            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="5"></textarea>

            <label for="map_url">URL Peta (Opsional)</label>
            <input type="url" name="map_url" id="map_url" placeholder="https://">

            <label for="left_position">Posisi Kiri Pointer (contoh: 30%)</label>
            <input type="text" name="left_position" id="left_position" placeholder="Contoh: 30%">

            <label for="top_position">Posisi Atas Pointer (contoh: 40%)</label>
            <input type="text" name="top_position" id="top_position" placeholder="Contoh: 40%">

            <button type="submit">Simpan History Daerah</button>
        </form>

        <h2>Data History Daerah Saat Ini</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Wisata</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>URL Peta</th>
                    <th>Left</th>
                    <th>Top</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT h.id, w.name AS wisata_nama, h.judul, h.deskripsi, h.map_url, h.left_position, h.top_position, h.created_at
                          FROM history_daerah h
                          JOIN wisata w ON h.wisata_id = w.id
                          ORDER BY h.created_at DESC";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['wisata_nama']) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                        <td>
                            <?php if ($row['map_url']): ?>
                                <a href="<?= htmlspecialchars($row['map_url']) ?>" target="_blank">Lihat</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['left_position']) ?></td>
                        <td><?= htmlspecialchars($row['top_position']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>