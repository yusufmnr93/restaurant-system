<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_feedback'])) {
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id_feedback = ?");
    if ($stmt->execute([$_POST['id_feedback']])) {
        $success = "Feedback successfully deleted.";
    }
}

try {
    $query = "
        SELECT f.id_feedback, f.rating, f.rating_aroma, f.rating_texture, f.rating_presentation, f.rating_story, f.overall_emotion, f.komentar, p.nama_pelanggan, p.username, m.nama_menu 
        FROM feedback f 
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
        ) p ON f.id_pelanggan = p.id_pelanggan 
        LEFT JOIN menu m ON f.id_menu = m.id_menu 
        ORDER BY f.id_feedback DESC
    ";
    $feedbacks = $pdo->query($query)->fetchAll();
} catch (PDOException $e) {
    // Fallback for older MySQL versions
    $query = "SELECT f.id_feedback, f.id_pelanggan, p.nama_pelanggan, m.nama_menu, f.rating, f.rating_aroma, f.rating_texture, f.rating_presentation, f.rating_story, f.overall_emotion, f.komentar 
              FROM feedback f 
              LEFT JOIN pelanggan p ON f.id_pelanggan = p.id_pelanggan 
              LEFT JOIN menu m ON f.id_menu = m.id_menu 
              ORDER BY f.id_feedback DESC";
    $feedbacks = $pdo->query($query)->fetchAll();
    
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
    
    foreach ($feedbacks as &$fb) {
        $fb['username'] = $pelangganToUsername[$fb['id_pelanggan']] ?? '';
    }
    unset($fb);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - L'Essence D'Or</title>
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
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu Management</a></li>
                <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservation Management</a></li>
                <li class="nav-item"><a class="nav-link active" href="feedback.php">Feedback Management</a></li>
                <li class="nav-item"><a class="nav-link" href="pelanggan.php">Customer Management</a></li>
            </ul>
        </div>
        
        <div class="flex-grow-1 p-4">
            <h2 class="fw-bold mb-4" style="color: var(--primary-color);">Feedback Management</h2>

            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4">Feedback ID</th>
                                <th class="py-3">Customer Name</th>
                                <th class="py-3">Username</th>
                                <th class="py-3">Menu Name</th>
                                <th class="py-3">Rating</th>
                                <th class="py-3">Aroma</th>
                                <th class="py-3">Texture</th>
                                <th class="py-3">Presentation</th>
                                <th class="py-3">Story</th>
                                <th class="py-3">Emotion</th>
                                <th class="py-3">Comment</th>
                                <th class="py-3 px-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $fb): ?>
                                <tr>
                                    <td class="px-4 fw-semibold"><?= htmlspecialchars($fb['id_feedback']) ?></td>
                                    <td><?= htmlspecialchars($fb['nama_pelanggan'] ?? 'Unknown') ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($fb['username'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($fb['nama_menu'] ?? 'Unknown') ?></td>
                                    <td>
                                        <i class="fas fa-star text-warning me-1"></i>
                                        <strong><?= htmlspecialchars($fb['rating']) ?></strong>
                                    </td>
                                    <td><?= $fb['rating_aroma'] !== null ? htmlspecialchars($fb['rating_aroma']) . ' <i class="fas fa-star text-warning small"></i>' : '-' ?></td>
                                    <td><?= $fb['rating_texture'] !== null ? htmlspecialchars($fb['rating_texture']) . ' <i class="fas fa-star text-warning small"></i>' : '-' ?></td>
                                    <td><?= $fb['rating_presentation'] !== null ? htmlspecialchars($fb['rating_presentation']) . ' <i class="fas fa-star text-warning small"></i>' : '-' ?></td>
                                    <td><?= $fb['rating_story'] !== null ? htmlspecialchars($fb['rating_story']) . ' <i class="fas fa-star text-warning small"></i>' : '-' ?></td>
                                    <td>
                                        <?php
                                        $emotion_emoji = '✨';
                                        if ($fb['overall_emotion'] == 'Happy') $emotion_emoji = '😊';
                                        elseif ($fb['overall_emotion'] == 'Relaxed') $emotion_emoji = '😌';
                                        elseif ($fb['overall_emotion'] == 'Excited') $emotion_emoji = '🤩';
                                        elseif ($fb['overall_emotion'] == 'Memorable') $emotion_emoji = '💫';
                                        echo $fb['overall_emotion'] ? $emotion_emoji . ' ' . htmlspecialchars($fb['overall_emotion']) : '-';
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($fb['komentar']) ?></td>
                                    <td class="px-4 text-end">
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this feedback?');" class="d-inline">
                                            <input type="hidden" name="id_feedback" value="<?= htmlspecialchars($fb['id_feedback']) ?>">
                                            <button type="submit" name="delete_feedback" class="btn btn-sm btn-danger rounded-pill px-3">Delete</button>
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
