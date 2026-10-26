<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Fetch featured menu (top 3 highest rated)
$stmt = $pdo->query("SELECT * FROM menu ORDER BY rating_menu DESC LIMIT 3");
$featured_menu = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Welcome to L'Essence D'Or</h1>
            <p class="lead mb-5 fs-4">Experience the pinnacle of fine dining.</p>
            <a href="reservation.php" class="btn btn-primary btn-lg fw-semibold px-5 rounded-pill shadow">Reserve Now</a>
        </div>
    </div>

    <div class="container mb-5 flex-grow-1">
        <h2 class="text-center fw-bold mb-5" style="color: var(--primary-color);">Featured Menu</h2>
        <div class="row g-4">
            <?php foreach ($featured_menu as $menu): ?>
                <div class="col-md-4">
                    <div class="card h-100">
<?php
$image = !empty($menu['image']) ? $menu['image'] : "images/default.jpg";
?>

<img src="<?= $image ?>"
class="card-img-top menu-img"
alt="<?= htmlspecialchars($menu['nama_menu']) ?>">                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold fs-5"><?= htmlspecialchars($menu['nama_menu']) ?></h5>
                            <p class="card-text text-muted mb-2"><?= htmlspecialchars($menu['kategori_menu']) ?></p>
                            <div class="d-flex justify-content-center align-items-center">
                                <span class="text-warning me-1 fs-5">&#9733;</span>
                                <span class="fw-semibold fs-5"><?= htmlspecialchars($menu['rating_menu']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
