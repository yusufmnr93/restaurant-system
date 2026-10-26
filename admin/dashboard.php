<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$customers = $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
$reservations = $pdo->query("SELECT COUNT(*) FROM reservasi")->fetchColumn();
$menus = $pdo->query("SELECT COUNT(*) FROM menu")->fetchColumn();
$feedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

// Fetch latest 5 reservations
$latest_reservations_stmt = $pdo->query("
    SELECT r.tanggal_reservasi, r.jumlah_orang, r.status_reservasi, p.nama_pelanggan 
    FROM reservasi r 
    LEFT JOIN pelanggan p ON r.id_pelanggan = p.id_pelanggan 
    ORDER BY r.tanggal_reservasi DESC 
    LIMIT 5
");
$latest_reservations = $latest_reservations_stmt->fetchAll();

// Fetch latest 5 feedbacks
$latest_feedback_stmt = $pdo->query("
    SELECT f.rating, f.komentar, p.nama_pelanggan, m.nama_menu 
    FROM feedback f 
    LEFT JOIN pelanggan p ON f.id_pelanggan = p.id_pelanggan 
    LEFT JOIN menu m ON f.id_menu = m.id_menu 
    ORDER BY f.id_feedback DESC 
    LIMIT 5
");
$latest_feedbacks = $latest_feedback_stmt->fetchAll();

// Emotional Journey Analytics
$mood_stats = $pdo->query("SELECT mood_preference, COUNT(*) as cnt FROM reservasi WHERE mood_preference IS NOT NULL AND mood_preference != '' GROUP BY mood_preference ORDER BY cnt DESC LIMIT 3")->fetchAll();
$emotion_stats = $pdo->query("SELECT overall_emotion, COUNT(*) as cnt FROM feedback WHERE overall_emotion IS NOT NULL AND overall_emotion != '' GROUP BY overall_emotion ORDER BY cnt DESC LIMIT 3")->fetchAll();
$sensory_stats = $pdo->query("SELECT AVG(rating_aroma) as avg_aroma, AVG(rating_texture) as avg_texture, AVG(rating_presentation) as avg_presentation, AVG(rating_story) as avg_story FROM feedback")->fetch();
$flavor_stats = $pdo->query("SELECT favorite_flavor, COUNT(*) as cnt FROM reservasi WHERE favorite_flavor IS NOT NULL AND favorite_flavor != '' GROUP BY favorite_flavor ORDER BY cnt DESC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .admin-sidebar { min-height: calc(100vh - 76px); background-color: #ffffff; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .admin-sidebar .nav-link { color: var(--text-dark) !important; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 0.5rem; font-weight: 500; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background-color: var(--bg-color); color: var(--primary-color) !important; }
        .bg-admin { background-color: #f8f9fa; }
        .dashboard-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            border-radius: 16px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08) !important;
            border-left: 4px solid var(--primary-color) !important;
        }
        .card-icon-wrapper {
            background-color: var(--secondary-color);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .dashboard-card:hover .card-icon-wrapper {
            background-color: var(--primary-color);
            color: #ffffff !important;
        }
        .dashboard-card:hover .card-icon-wrapper i {
            color: #ffffff !important;
        }
        .table {
            margin-top: 0px !important;
        }
    </style>
</head>
<body class="bg-admin">
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid d-flex p-0">
        <!-- Sidebar -->
        <div class="admin-sidebar p-3 d-none d-md-block" style="width: 280px;">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu Management</a></li>
                <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservation Management</a></li>
                <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback Management</a></li>
                <li class="nav-item"><a class="nav-link" href="pelanggan.php">Customer Management</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <h2 class="fw-bold mb-4" style="color: var(--primary-color);">Admin Dashboard</h2>
            
            <!-- Dashboard Stats Cards -->
            <div class="row g-4 mb-5">
                <!-- Customers Card -->
                <div class="col-md-3 col-sm-6">
                    <a href="pelanggan.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm border-0 text-center p-3 h-100 dashboard-card">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <div class="card-icon-wrapper mb-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h5 class="text-muted fs-6 mb-2">Total Customers</h5>
                                <h2 class="fw-bold mb-0 text-primary"><?= $customers ?></h2>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Reservations Card -->
                <div class="col-md-3 col-sm-6">
                    <a href="reservasi.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm border-0 text-center p-3 h-100 dashboard-card">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <div class="card-icon-wrapper mb-3">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                                <h5 class="text-muted fs-6 mb-2">Total Reservations</h5>
                                <h2 class="fw-bold mb-0 text-primary"><?= $reservations ?></h2>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Menus Card -->
                <div class="col-md-3 col-sm-6">
                    <a href="menu.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm border-0 text-center p-3 h-100 dashboard-card">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <div class="card-icon-wrapper mb-3">
                                    <i class="fas fa-utensils fa-2x"></i>
                                </div>
                                <h5 class="text-muted fs-6 mb-2">Total Menus</h5>
                                <h2 class="fw-bold mb-0 text-primary"><?= $menus ?></h2>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Feedback Card -->
                <div class="col-md-3 col-sm-6">
                    <a href="feedback.php" class="text-decoration-none text-dark">
                        <div class="card shadow-sm border-0 text-center p-3 h-100 dashboard-card">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <div class="card-icon-wrapper mb-3">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                                <h5 class="text-muted fs-6 mb-2">Total Feedback</h5>
                                <h2 class="fw-bold mb-0 text-primary"><?= $feedback ?></h2>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Emotional Journey Analytics Cards -->
            <h5 class="fw-bold mb-3 mt-5" style="color: var(--primary-color);">Guest Emotional Journey Analytics</h5>
            <div class="row g-4 mb-5">
                <!-- Customer Mood Analytics -->
                <div class="col-md-3">
                    <div class="card shadow-sm analytics-card h-100">
                        <div class="card-header fw-bold">
                            <i class="fas fa-heart text-danger me-2"></i> Customer Mood
                        </div>
                        <div class="card-body p-3">
                            <?php foreach ($mood_stats as $stat): ?>
                                <div class="analytics-stat">
                                    <span class="stat-label"><?= htmlspecialchars($stat['mood_preference']) ?></span>
                                    <span class="stat-value"><?= $stat['cnt'] ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($mood_stats)): ?>
                                <div class="text-center text-muted small py-3">No data yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Emotional Experience Summary -->
                <div class="col-md-3">
                    <div class="card shadow-sm analytics-card h-100">
                        <div class="card-header fw-bold">
                            <i class="fas fa-face-smile text-warning me-2"></i> Emotions
                        </div>
                        <div class="card-body p-3">
                            <?php foreach ($emotion_stats as $stat): ?>
                                <div class="analytics-stat">
                                    <span class="stat-label"><?= htmlspecialchars($stat['overall_emotion']) ?></span>
                                    <span class="stat-value"><?= $stat['cnt'] ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($emotion_stats)): ?>
                                <div class="text-center text-muted small py-3\">No data yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Multi-Sensory Stats -->
                <div class="col-md-3">
                    <div class="card shadow-sm analytics-card h-100">
                        <div class="card-header fw-bold">
                            <i class="fas fa-hand-sparkles text-info me-2"></i> Sensory Avg
                        </div>
                        <div class="card-body p-3">
                            <div class="analytics-stat">
                                <span class="stat-label">Aroma</span>
                                <span class="stat-value"><?= number_format($sensory_stats['avg_aroma'] ?? 0, 1) ?> <i class="fas fa-star text-warning small"></i></span>
                            </div>
                            <div class="analytics-stat">
                                <span class="stat-label">Texture</span>
                                <span class="stat-value"><?= number_format($sensory_stats['avg_texture'] ?? 0, 1) ?> <i class="fas fa-star text-warning small"></i></span>
                            </div>
                            <div class="analytics-stat">
                                <span class="stat-label">Presentation</span>
                                <span class="stat-value"><?= number_format($sensory_stats['avg_presentation'] ?? 0, 1) ?> <i class="fas fa-star text-warning small"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Taste Evolution -->
                <div class="col-md-3">
                    <div class="card shadow-sm analytics-card h-100">
                        <div class="card-header fw-bold">
                            <i class="fas fa-chart-line text-success me-2"></i> Favorite Flavor
                        </div>
                        <div class="card-body p-3">
                            <?php foreach ($flavor_stats as $stat): ?>
                                <div class="analytics-stat">
                                    <span class="stat-label"><?= htmlspecialchars($stat['favorite_flavor']) ?></span>
                                    <span class="stat-value"><?= $stat['cnt'] ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($flavor_stats)): ?>
                                <div class="text-center text-muted small py-3">No data yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities & Latest Feedback Sections -->
            <div class="row g-4 mb-4">
                <!-- Recent Reservations (last 5) -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-calendar-check text-primary me-2"></i>Latest Reservations</h5>
                            <a href="reservasi.php" class="btn btn-sm btn-outline-primary py-1 px-3 rounded-pill fw-semibold">View All</a>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Customer</th>
                                        <th>Date</th>
                                        <th>Guests</th>
                                        <th class="pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($latest_reservations) > 0): ?>
                                        <?php foreach ($latest_reservations as $res): ?>
                                            <tr>
                                                <td class="ps-4 fw-semibold"><?= htmlspecialchars($res['nama_pelanggan']) ?></td>
                                                <td><?= htmlspecialchars($res['tanggal_reservasi']) ?></td>
                                                <td><?= htmlspecialchars($res['jumlah_orang']) ?></td>
                                                <td class="pe-4">
                                                    <?php if ($res['status_reservasi'] == 'Confirmed'): ?>
                                                        <span class="badge bg-success">Confirmed</span>
                                                    <?php elseif ($res['status_reservasi'] == 'Pending'): ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No reservations found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Latest Feedback (last 5) -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-comments text-primary me-2"></i>Latest Feedbacks</h5>
                            <a href="feedback.php" class="btn btn-sm btn-outline-primary py-1 px-3 rounded-pill fw-semibold">View All</a>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Customer</th>
                                        <th>Menu</th>
                                        <th>Rating</th>
                                        <th class="pe-4">Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($latest_feedbacks) > 0): ?>
                                        <?php foreach ($latest_feedbacks as $fb): ?>
                                            <tr>
                                                <td class="ps-4 fw-semibold"><?= htmlspecialchars($fb['nama_pelanggan'] ?? 'Unknown') ?></td>
                                                <td><?= htmlspecialchars($fb['nama_menu'] ?? 'Unknown') ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-star text-warning me-1"></i>
                                                        <strong><?= htmlspecialchars($fb['rating']) ?></strong>
                                                    </div>
                                                </td>
                                                <td class="pe-4 text-truncate" style="max-width: 150px;"><?= htmlspecialchars($fb['komentar']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No feedback found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
