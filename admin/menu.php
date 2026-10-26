<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';

// Handle CRUD
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_menu'])) {
        $id = uniqid('MNU-');
        $stmt = $pdo->prepare("INSERT INTO menu (id_menu, nama_menu, kategori_menu, rating_menu, image, story_behind, recommended_atmosphere, mood_fit, aroma_notes, texture_profile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $_POST['nama_menu'],
            $_POST['kategori_menu'],
            $_POST['rating_menu'],
            $_POST['image'] ?: null,
            $_POST['story_behind'] ?: null,
            $_POST['recommended_atmosphere'] ?: null,
            $_POST['mood_fit'] ?: null,
            $_POST['aroma_notes'] ?: null,
            $_POST['texture_notes'] ?: null
        ]);
        $success = "Menu added successfully.";
    } elseif (isset($_POST['edit_menu'])) {
        $stmt = $pdo->prepare("UPDATE menu SET nama_menu = ?, kategori_menu = ?, rating_menu = ?, image = ?, story_behind = ?, recommended_atmosphere = ?, mood_fit = ?, aroma_notes = ?, texture_profile = ? WHERE id_menu = ?");
        $stmt->execute([
            $_POST['nama_menu'],
            $_POST['kategori_menu'],
            $_POST['rating_menu'],
            $_POST['image'] ?: null,
            $_POST['story_behind'] ?: null,
            $_POST['recommended_atmosphere'] ?: null,
            $_POST['mood_fit'] ?: null,
            $_POST['aroma_notes'] ?: null,
            $_POST['texture_notes'] ?: null,
            $_POST['id_menu']
        ]);
        $success = "Menu updated successfully.";
    } elseif (isset($_POST['delete_menu'])) {
        $stmt = $pdo->prepare("DELETE FROM menu WHERE id_menu = ?");
        $stmt->execute([$_POST['id_menu']]);
        $success = "Menu deleted successfully.";
    }
}

$menus = $pdo->query("SELECT * FROM menu ORDER BY kategori_menu, nama_menu")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .admin-sidebar { min-height: calc(100vh - 76px); background-color: #ffffff; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .admin-sidebar .nav-link { color: var(--text-dark) !important; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 0.5rem; font-weight: 500; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background-color: var(--bg-color); color: var(--primary-color) !important; }
        .bg-admin { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-admin">
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid d-flex p-0">
        <div class="admin-sidebar p-3 d-none d-md-block" style="width: 280px;">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="menu.php">Menu Management</a></li>
                <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservation Management</a></li>
                <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback Management</a></li>
                <li class="nav-item"><a class="nav-link" href="pelanggan.php">Customer Management</a></li>
            </ul>
        </div>
        
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0" style="color: var(--primary-color);">Menu Management</h2>
                <button class="btn btn-primary rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#addModal">+ Add New Menu</button>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4">Menu Name</th>
                                <th class="py-3">Category</th>
                                <th class="py-3">Rating</th>
                                <th class="py-3">Story Preview</th>
                                <th class="py-3 px-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menus as $menu): ?>
                                <tr>
                                    <td class="px-4 fw-semibold"><?= htmlspecialchars($menu['nama_menu']) ?></td>
                                    <td><?= htmlspecialchars($menu['kategori_menu']) ?></td>
                                    <td><?= htmlspecialchars($menu['rating_menu']) ?></td>
                                    <td>
                                        <?= $menu['story_behind'] ? htmlspecialchars(mb_substr($menu['story_behind'], 0, 50) . (mb_strlen($menu['story_behind']) > 50 ? '...' : '')) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td class="px-4 text-end">
                                        <button class="btn btn-sm btn-warning fw-semibold rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $menu['id_menu'] ?>">Edit</button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu?');">
                                            <input type="hidden" name="id_menu" value="<?= htmlspecialchars($menu['id_menu']) ?>">
                                            <button type="submit" name="delete_menu" class="btn btn-sm btn-danger fw-semibold rounded-pill px-3">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($menus as $menu): ?>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?= $menu['id_menu'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body text-start">
                            <input type="hidden" name="id_menu" value="<?= htmlspecialchars($menu['id_menu']) ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Menu Name</label>
                                <input type="text" name="nama_menu" class="form-control" value="<?= htmlspecialchars($menu['nama_menu']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="kategori_menu" class="form-select" required>
                                    <option value="Main Course" <?= $menu['kategori_menu'] == 'Main Course' ? 'selected' : '' ?>>Main Course</option>
                                    <option value="Appetizer" <?= $menu['kategori_menu'] == 'Appetizer' ? 'selected' : '' ?>>Appetizer</option>
                                    <option value="Dessert" <?= $menu['kategori_menu'] == 'Dessert' ? 'selected' : '' ?>>Dessert</option>
                                    <option value="Beverage" <?= $menu['kategori_menu'] == 'Beverage' ? 'selected' : '' ?>>Beverage</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Rating</label>
                                <input type="number" step="0.1" min="0" max="5" name="rating_menu" class="form-control" value="<?= htmlspecialchars($menu['rating_menu']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Image Path</label>
                                <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($menu['image'] ?? '') ?>" placeholder="e.g. images/default.jpg">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Story Behind</label>
                                <textarea name="story_behind" class="form-control" rows="3"><?= htmlspecialchars($menu['story_behind'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Recommended Atmosphere</label>
                                <input type="text" name="recommended_atmosphere" class="form-control" value="<?= htmlspecialchars($menu['recommended_atmosphere'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mood Fit</label>
                                <textarea name="mood_fit" class="form-control" rows="2"><?= htmlspecialchars($menu['mood_fit'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Aroma Notes</label>
                                <input type="text" name="aroma_notes" class="form-control" value="<?= htmlspecialchars($menu['aroma_notes'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Texture Notes</label>
                                <input type="text" name="texture_notes" class="form-control" value="<?= htmlspecialchars($menu['texture_profile'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="edit_menu" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Menu Name</label>
                            <input type="text" name="nama_menu" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="kategori_menu" class="form-select" required>
                                <option value="Main Course">Main Course</option>
                                <option value="Appetizer">Appetizer</option>
                                <option value="Dessert">Dessert</option>
                                <option value="Beverage">Beverage</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rating</label>
                            <input type="number" step="0.1" min="0" max="5" name="rating_menu" class="form-control" value="0.0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Image Path</label>
                            <input type="text" name="image" class="form-control" placeholder="e.g. images/default.jpg">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Story Behind</label>
                            <textarea name="story_behind" class="form-control" rows="3" placeholder="The story behind this dish..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recommended Atmosphere</label>
                            <input type="text" name="recommended_atmosphere" class="form-control" placeholder="e.g. Candlelit intimacy">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mood Fit</label>
                            <textarea name="mood_fit" class="form-control" rows="2" placeholder="Why this menu fits specific moods..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Aroma Notes</label>
                            <input type="text" name="aroma_notes" class="form-control" placeholder="e.g. Earthy black truffle, toasted garlic">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Texture Notes</label>
                            <input type="text" name="texture_notes" class="form-control" placeholder="e.g. Creamy and velvety">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_menu" class="btn btn-primary rounded-pill px-4">Add Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
