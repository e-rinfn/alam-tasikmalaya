<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light" style="background: linear-gradient(100deg, #001A6E, #16C47F );">
    <div class="container">
        <!-- Logo -->

        <a class="navbar-brand text-white" href="index.php"><img src="img/Logo-Putih.png" style="width: 50px;" alt=""> Alam Tasikmalaya 360</a>

        <!-- Menu Navbar -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Link Kelola History -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="read.php">
                        <i class="bi bi-house"></i> Kelola History
                    </a>
                </li>

                <!-- Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white text-decoration-none" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> - ADMINISTRATOR
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-3" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="akun.php"><i class="bi bi-person"></i> Akun</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-book"></i> Petunjuk Penggunaan</a></li>
                        <li><a class="dropdown-item" href="tentang.php"><i class="bi bi-info-circle"></i> Tentang</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>


    </div>
</nav>