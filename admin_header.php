<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-success" style="font-family: 'Poppins', sans-serif;">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand text-white" href="index_admin.php">Riwayat Bencana</a>
        <!-- Menu Navbar -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-2">
                <!-- Tombol Kelola Riwayat Bencana -->
                <li class="nav-item">
                    <a href="read.php" class="btn btn-sm btn-outline-light d-flex align-items-center">
                        <i class="bi bi-house me-2"></i> Kelola Riwayat
                    </a>
                </li>

                <!-- Dropdown Admin -->
                <li class="nav-item dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i> Administrator
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="admin/manage_account.php">
                                <i class="bi bi-person me-2"></i> Pengguna
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="admin/akun.php">
                                <i class="bi bi-person me-2"></i> Password
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center text-danger" href="admin/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>