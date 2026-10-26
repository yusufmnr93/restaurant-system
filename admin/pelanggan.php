<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Customer Deletion
if (isset($_POST['delete_customer'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $username = $_POST['username'];

    try {
        $pdo->beginTransaction();

        // Hapus dari tabel pelanggan (otomatis cascade ke tabel terkait)
        $stmt_pelanggan = $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
        $stmt_pelanggan->execute([$id_pelanggan]);

        // Hapus dari tabel users (hanya customer, tidak menghapus admin)
        if (!empty($username)) {
            $stmt_user = $pdo->prepare("DELETE FROM users WHERE username = ? AND role = 'customer'");
            $stmt_user->execute([$username]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Customer successfully deleted.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Failed to delete customer: " . $e->getMessage();
    }
    
    header("Location: pelanggan.php");
    exit();
}

// Ambil data pelanggan dan relasikan dengan tabel users untuk mendapatkan username.
// Menggunakan ROW_NUMBER agar tidak terjadi duplikasi Cartesian jika ada pelanggan bernama sama.
try {
    $sql = "
        SELECT p.id_pelanggan, p.nama_pelanggan, u.username, u.created_at 
        FROM (
            SELECT id_pelanggan, nama_pelanggan, 
                   ROW_NUMBER() OVER (PARTITION BY nama_pelanggan ORDER BY CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)) as rn 
            FROM pelanggan
        ) p 
        LEFT JOIN (
            SELECT nama, username, created_at, 
                   ROW_NUMBER() OVER (PARTITION BY nama ORDER BY id_user) as rn 
            FROM users 
            WHERE role = 'customer'
        ) u ON p.nama_pelanggan = u.nama AND p.rn = u.rn 
        ORDER BY p.nama_pelanggan
    ";
    $customers = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    // Fallback jika versi MySQL lama tidak mendukung ROW_NUMBER (menghindari error)
    $customersRaw = $pdo->query("SELECT * FROM pelanggan ORDER BY CAST(SUBSTRING(id_pelanggan, 5) AS UNSIGNED)")->fetchAll();
    $usersRaw = $pdo->query("SELECT nama, username, created_at FROM users WHERE role = 'customer' ORDER BY id_user")->fetchAll();
    
    $userMap = [];
    foreach ($usersRaw as $u) {
        $userMap[$u['nama']][] = [
            'username' => $u['username'],
            'created_at' => $u['created_at']
        ];
    }
    
    $customers = [];
    foreach ($customersRaw as $c) {
        $c['username'] = '';
        $c['created_at'] = '';
        if (!empty($userMap[$c['nama_pelanggan']])) {
            $mapped = array_shift($userMap[$c['nama_pelanggan']]);
            $c['username'] = $mapped['username'];
            $c['created_at'] = $mapped['created_at'];
        }
        $customers[] = $c;
    }
    
    usort($customers, function($a, $b) {
        return strcmp($a['nama_pelanggan'], $b['nama_pelanggan']);
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - L'Essence D'Or</title>
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
                <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservation Management</a></li>
                <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback Management</a></li>
                <li class="nav-item"><a class="nav-link active" href="pelanggan.php">Customer Management</a></li>
            </ul>
        </div>
        
        <div class="flex-grow-1 p-4">
            <h2 class="fw-bold mb-4" style="color: var(--primary-color);">Customer Management</h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4">Customer ID</th>
                                <th class="py-3 px-4">Customer Name</th>
                                <th class="py-3 px-4">Username</th>
                                <th class="py-3 px-4">Registration Date</th>
                                <th class="py-3 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $cust): ?>
                                <tr>
                                    <td class="px-4 fw-semibold"><?= htmlspecialchars($cust['id_pelanggan']) ?></td>
                                    <td class="px-4"><?= htmlspecialchars($cust['nama_pelanggan']) ?></td>
                                    <td class="px-4 text-muted"><?= htmlspecialchars($cust['username'] ?? '-') ?></td>
                                    <td class="px-4"><?= htmlspecialchars($cust['created_at'] ?? '-') ?></td>
                                    <td class="px-4 text-center">
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');" class="d-inline">
                                            <input type="hidden" name="id_pelanggan" value="<?= htmlspecialchars($cust['id_pelanggan']) ?>">
                                            <input type="hidden" name="username" value="<?= htmlspecialchars($cust['username'] ?? '') ?>">
                                            <button type="submit" name="delete_customer" class="btn btn-danger btn-sm px-3 shadow-sm rounded-pill">Delete</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
