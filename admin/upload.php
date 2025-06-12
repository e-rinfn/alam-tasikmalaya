<?php
if ($_FILES['file']['name']) {
    $filename = time() . '_' . $_FILES['file']['name'];
    $destination = '../uploads/' . $filename; // Folder penyimpanan

    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        echo json_encode(['url' => $destination]);
    } else {
        echo json_encode(['error' => 'Upload gagal.']);
    }
}
