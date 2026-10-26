<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE reservasi SET status_reservasi = ?, tanggal_reservasi = ?, jam_reservasi = ?, jumlah_orang = ?, preferensi_menu = ?, mood_preference = ?, favorite_flavor = ?, special_occasion = ? WHERE id_reservasi = ?");
    $stmt->execute([
        $_POST['status_reservasi'],
        $_POST['tanggal_reservasi'],
        $_POST['jam_reservasi'],
        $_POST['jumlah_orang'],
        $_POST['preferensi_menu'] ?: null,
        $_POST['mood_preference'] ?: null,
        $_POST['favorite_flavor'] ?: null,
        $_POST['special_occasion'] ?: 'None',
        $_POST['id_reservasi']
    ]);
    $success = "Reservation updated successfully.";
}

try {
    $query = "
        SELECT r.id_reservasi, r.tanggal_reservasi, r.jam_reservasi, r.jumlah_orang, r.status_reservasi, r.preferensi_menu, r.mood_preference, r.favorite_flavor, r.special_occasion, p.nama_pelanggan, p.username 
        FROM reservasi r 
        LEFT JOIN (
            SELECT p.id_pelanggan, p.nama_pelanggan, u.username 
            FROM (
                SELECT id_pelanggan, nama_pelanggan, 
                       ROW_NUMBER() OVER (PARTITION BY nama_pelanggan ORDER BY CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)) as rn 
                FROM pelanggan
            ) p 
            LEFT JOIN (
                SELECT nama, username, 
                       ROW_NUMBER() OVER (PARTITION BY nama ORDER BY id_user) as rn 
                FROM users 
                WHERE role = 'customer'
            ) u ON p.nama_pelanggan = u.nama AND p.rn = u.rn 
        ) p ON r.id_pelanggan = p.id_pelanggan 
        ORDER BY r.tanggal_reservasi DESC
    ";
    $reservations = $pdo->query($query)->fetchAll();
} catch (PDOException $e) {
    // Fallback for older MySQL versions
    $query = "SELECT r.id_reservasi, r.tanggal_reservasi, r.jam_reservasi, r.jumlah_orang, r.status_reservasi, r.preferensi_menu, r.mood_preference, r.favorite_flavor, r.special_occasion, r.id_pelanggan, p.nama_pelanggan 
              FROM reservasi r 
              LEFT JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan 
              ORDER BY r.tanggal_reservasi DESC";
    $reservations = $pdo->query($query)->fetchAll();
    
    // Fallback mapping
    $customersRaw = $pdo->query("SELECT * FROM pelanggan ORDER BY CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)")->fetchAll();
    $usersRaw = $pdo->query("SELECT nama, username FROM users WHERE role = 'customer' ORDER BY id_user")->fetchAll();
    
    $userMap = [];
    foreach ($usersRaw as $u) {
        $userMap[$u['nama']][] = $u['username'];
    }
    
    $pelangganToUsername = [];
    foreach ($customersRaw as $c) {
        $username = '';
        if (!empty($userMap[$c['nama_pelanggan']])) {
            $username = array_shift($userMap[$c['nama_pelanggan']]);
        }
        $pelangganToUsername[$c['id_pelanggan']] = $username;
    }
    
    foreach ($reservations as &$res) {
        $res['username'] = $pelangganToUsername[$res['id_pelanggan']] ?? '';
    }
    unset($res);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu Management</a></li>
                <li class="nav-item"><a class="nav-link active" href="reservasi.php">Reservation Management</a></li>
                <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback Management</a></li>
                <li class="nav-item"><a class="nav-link" href="pelanggan.php">Customer Management</a></li>
            </ul>
        </div>
        
        <div class="flex-grow-1 p-4">
            <h2 class="fw-bold mb-4" style="color: var(--primary-color);">Reservation Management</h2>

            <?php if($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4">Reservation ID</th>
                                <th class="py-3">Customer Name</th>
                                <th class="py-3">Username</th>
                                <th class="py-3">Reservation Date</th>
                                <th class="py-3">Reservation Time</th>
                                <th class="py-3">Guests</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Menu Pref.</th>
                                <th class="py-3">Mood</th>
                                <th class="py-3">Flavor</th>
                                <th class="py-3">Occasion</th>
                                <th class="py-3 px-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td class="px-4 fw-semibold"><?= htmlspecialchars($res['id_reservasi']) ?></td>
                                    <td><?= htmlspecialchars($res['nama_pelanggan']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($res['username'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($res['tanggal_reservasi']) ?></td>
                                    <td><?= htmlspecialchars($res['jam_reservasi']) ?></td>
                                    <td><?= htmlspecialchars($res['jumlah_orang']) ?></td>
                                    <td>
                                        <?php if ($res['status_reservasi'] == 'Confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                        <?php elseif ($res['status_reservasi'] == 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($res['preferensi_menu'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($res['mood_preference'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($res['favorite_flavor'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($res['special_occasion'] ?? '-') ?></td>
                                    <td class="px-4 text-end">
                                        <button class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#statusModal<?= $res['id_reservasi'] ?>">Update</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($reservations as $res): ?>
        <!-- Status Modal -->
        <div class="modal fade" id="statusModal<?= $res['id_reservasi'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Update Reservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body text-start">
                            <input type="hidden" name="id_reservasi" value="<?= htmlspecialchars($res['id_reservasi']) ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status_reservasi" class="form-select" required>
                                    <option value="Confirmed" <?= $res['status_reservasi'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Pending" <?= $res['status_reservasi'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Cancelled" <?= $res['status_reservasi'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reservation Date</label>
                                <input type="date" name="tanggal_reservasi" class="form-control" value="<?= htmlspecialchars($res['tanggal_reservasi']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reservation Time</label>
                                <input type="time" name="jam_reservasi" class="form-control" value="<?= htmlspecialchars($res['jam_reservasi']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Number of Guests</label>
                                <input type="number" name="jumlah_orang" class="form-control" min="1" max="50" value="<?= htmlspecialchars($res['jumlah_orang']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Menu Preference</label>
                                <textarea name="preferensi_menu" class="form-control" rows="2"><?= htmlspecialchars($res['preferensi_menu'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mood Preference</label>
                                <select name="mood_preference" class="form-select">
                                    <option value="" <?= empty($res['mood_preference']) ? 'selected' : '' ?>>None</option>
                                    <option value="Romantic Dinner" <?= $res['mood_preference'] == 'Romantic Dinner' ? 'selected' : '' ?>>Romantic Dinner</option>
                                    <option value="Family Gathering" <?= $res['mood_preference'] == 'Family Gathering' ? 'selected' : '' ?>>Family Gathering</option>
                                    <option value="Celebration" <?= $res['mood_preference'] == 'Celebration' ? 'selected' : '' ?>>Celebration</option>
                                    <option value="Business Meeting" <?= $res['mood_preference'] == 'Business Meeting' ? 'selected' : '' ?>>Business Meeting</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Favorite Flavor</label>
                                <select name="favorite_flavor" class="form-select">
                                    <option value="" <?= empty($res['favorite_flavor']) ? 'selected' : '' ?>>None</option>
                                    <option value="Sweet" <?= $res['favorite_flavor'] == 'Sweet' ? 'selected' : '' ?>>Sweet</option>
                                    <option value="Savory" <?= $res['favorite_flavor'] == 'Savory' ? 'selected' : '' ?>>Savory</option>
                                    <option value="Spicy" <?= $res['favorite_flavor'] == 'Spicy' ? 'selected' : '' ?>>Spicy</option>
                                    <option value="Umami" <?= $res['favorite_flavor'] == 'Umami' ? 'selected' : '' ?>>Umami</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Special Occasion</label>
                                <select name="special_occasion" class="form-select">
                                    <option value="None" <?= $res['special_occasion'] == 'None' ? 'selected' : '' ?>>None</option>
                                    <option value="Birthday" <?= $res['special_occasion'] == 'Birthday' ? 'selected' : '' ?>>Birthday</option>
                                    <option value="Anniversary" <?= $res['special_occasion'] == 'Anniversary' ? 'selected' : '' ?>>Anniversary</option>
                                    <option value="Graduation" <?= $res['special_occasion'] == 'Graduation' ? 'selected' : '' ?>>Graduation</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_status" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
