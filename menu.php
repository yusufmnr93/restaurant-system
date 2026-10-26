<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM menu WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND nama_menu LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND kategori_menu = ?";
    $params[] = $category;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$menus = $stmt->fetchAll();

// Get unique categories for filter
$cat_stmt = $pdo->query("SELECT DISTINCT kategori_menu FROM menu");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5 flex-grow-1">
        <h2 class="text-center fw-bold mb-4" style="color: var(--primary-color);">Our Menu</h2>
        
        <div class="card p-4 mb-5 shadow-sm border-0">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Search Menu</label>
                    <input type="text" name="search" class="form-control rounded-pill" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select rounded-pill">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                        <!-- Ensure requested categories exist as filter options if DB lacks them -->
                        <?php 
                            $req_cats = ['Main Course', 'Dessert', 'Beverage'];
                            foreach($req_cats as $rc): 
                                if(!in_array($rc, $categories)):
                        ?>
                            <option value="<?= $rc ?>" <?= $category === $rc ? 'selected' : '' ?>><?= $rc ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter</button>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if (count($menus) > 0): ?>
                <?php foreach ($menus as $menu): ?>
                    <?php
                    $image = !empty($menu['image']) ? $menu['image'] : "images/default.jpg";
                    ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100">
                            <img src="<?= $image ?>" class="card-img-top menu-img" alt="Menu Image">
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold fs-6"><?= htmlspecialchars($menu['nama_menu']) ?></h5>
                                <p class="card-text text-muted mb-2 small"><?= htmlspecialchars($menu['kategori_menu']) ?></p>
                                <div class="d-flex justify-content-center align-items-center">
                                    <i class="fas fa-star text-warning me-1"></i>
                                    <span class="fw-semibold"><?= htmlspecialchars($menu['rating_menu']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <h5>No menu items found matching your criteria.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
