<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base = $is_admin ? '../' : '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="<?= $base ?>index.php">L'Essence D'Or</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(!isset($_SESSION['id_user'])): ?>
                    <li class="nav-item"><a class="nav-link <?= $current_page == 'login.php' ? 'active' : '' ?>" href="<?= $base ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current_page == 'register.php' ? 'active' : '' ?>" href="<?= $base ?>register.php">Register</a></li>
                <?php else: ?>
                    <?php if($_SESSION['role'] === 'customer'): ?>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="<?= $base ?>index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'menu.php' ? 'active' : '' ?>" href="<?= $base ?>menu.php">Menu</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'reservation.php' ? 'active' : '' ?>" href="<?= $base ?>reservation.php">Reservation</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'recommendation.php' ? 'active' : '' ?>" href="<?= $base ?>recommendation.php">Recommendation</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'feedback.php' ? 'active' : '' ?>" href="<?= $base ?>feedback.php">Feedback</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'history.php' ? 'active' : '' ?>" href="<?= $base ?>history.php">Reservation History</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'memory_journey.php' ? 'active' : '' ?>" href="<?= $base ?>memory_journey.php">Memory Journey</a></li>
                        <li class="nav-item ms-lg-3"><a class="btn btn-outline-primary btn-sm rounded-pill mt-1 px-4" href="<?= $base ?>logout.php">Logout</a></li>
                    <?php elseif($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="<?= $base ?>admin/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'menu.php' && $is_admin ? 'active' : '' ?>" href="<?= $base ?>admin/menu.php">Menu Management</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'reservasi.php' ? 'active' : '' ?>" href="<?= $base ?>admin/reservasi.php">Reservation Management</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'feedback.php' && $is_admin ? 'active' : '' ?>" href="<?= $base ?>admin/feedback.php">Feedback Management</a></li>
                        <li class="nav-item"><a class="nav-link <?= $current_page == 'pelanggan.php' ? 'active' : '' ?>" href="<?= $base ?>admin/pelanggan.php">Customer Management</a></li>
                        <li class="nav-item ms-lg-3"><a class="btn btn-outline-primary btn-sm rounded-pill mt-1 px-4" href="<?= $base ?>logout.php">Logout</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
