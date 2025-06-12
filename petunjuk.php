<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alam Tasikmalaya 360</title>
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

</head>
<body style="font-family: 'Poppins', sans-serif;">


<?php include 'pengguna_header.php'; ?>

<!-- Cards Section -->
<div class="container" style="min-height: 85vh;">
<br>
    <div class="row p-3 border" style="min-height: 75vh;">
        <h1 class="text-center" >PETUNJUK PENGGUNAAN</h1>
        <hr>
        <br>
        <h3>A. Akses Website</h3>
        <p style="padding-left: 10%">
            Buka browser dan kunjungi website virtual tour 
            <a href="index.php">Alam Tasikmalaya 360</a>.
        	<br>
			<br>
            Setelah masuk ke website, pengguna dapat melihat daftar objek wisata yang tersedia.  
            Pilih objek wisata yang ingin dijelajahi dengan menekan tombol <strong>"Lihat Selengkapnya"</strong>.
        	<br>
			<br>
            Untuk memulai eksplorasi, klik gambar thumbnail dari lokasi yang ingin dijelajahi dalam tampilan virtual 360 derajat.
        </p>

        <h3>B. Navigasi dalam Virtual Tour</h3>
        <ol style="padding-left: 10%">
            <li>Gunakan mouse atau layar sentuh untuk menggeser tampilan dan melihat ke segala arah.</li>
            <li>Klik ikon <strong>hotspot</strong> berbentuk panah untuk berpindah ke lokasi lain dalam tur.</li>
            <li>Gunakan fitur zoom dengan mencubit layar (pinch) atau menggulir (scroll) menggunakan mouse untuk memperbesar atau memperkecil tampilan.</li>
        </ol>

        <h3>C. Informasi Objek Wisata</h3>
        <p style="padding-left: 10%">
            Klik ikon informasi (<strong>"i"</strong>) yang muncul di layar untuk melihat deskripsi dan detail tentang objek wisata yang sedang dijelajahi.
        </p>    
    </div>
</div>

<!-- Bootstrap JS (for the hamburger menu) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'pengguna_footer.php'; ?>
</body>
</html>
