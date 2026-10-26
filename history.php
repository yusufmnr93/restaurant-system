<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$id_pelanggan = 'PLG-' . $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT id_reservasi, tanggal_reservasi, jam_reservasi, jumlah_orang, status_reservasi, preferensi_menu FROM reservasi WHERE id_pelanggan = ? ORDER BY tanggal_reservasi DESC");
$stmt->execute([$id_pelanggan]);
$reservations = $stmt->fetchAll();

// Calculate Taste Evolution Profile
$stmt_mood = $pdo->prepare("SELECT mood_preference FROM reservasi WHERE id_pelanggan = ? AND mood_preference IS NOT NULL AND mood_preference != '' GROUP BY mood_preference ORDER BY COUNT(*) DESC LIMIT 1");
$stmt_mood->execute([$id_pelanggan]);
$pref_mood = $stmt_mood->fetchColumn() ?: 'Romantic Dinner';

$stmt_dish = $pdo->prepare("SELECT preferensi_menu FROM reservasi WHERE id_pelanggan = ? AND preferensi_menu IS NOT NULL AND preferensi_menu != '' GROUP BY preferensi_menu ORDER BY COUNT(*) DESC LIMIT 1");
$stmt_dish->execute([$id_pelanggan]);
$most_ordered = $stmt_dish->fetchColumn() ?: 'Crème Brûlée';

$stmt_cat = $pdo->prepare("SELECT m.kategori_menu FROM feedback f JOIN menu m ON f.id_menu = m.id_menu WHERE f.id_pelanggan = ? GROUP BY m.kategori_menu ORDER BY COUNT(*) DESC LIMIT 1");
$stmt_cat->execute([$id_pelanggan]);
$fav_cat = $stmt_cat->fetchColumn() ?: 'Dessert';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation History - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5 flex-grow-1">
        <h2 class="fw-bold mb-4 text-center" style="color: var(--primary-color);">My Reservation History</h2>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3">Reservation ID</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Time</th>
                            <th class="py-3">Guests</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Menu Preference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reservations) > 0): ?>
                            <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td class="fw-semibold py-3"><?= htmlspecialchars($res['id_reservasi']) ?></td>
                                    <td class="py-3"><?= htmlspecialchars($res['tanggal_reservasi']) ?></td>
                                    <td class="py-3"><?= htmlspecialchars($res['jam_reservasi']) ?></td>
                                    <td class="py-3"><?= htmlspecialchars($res['jumlah_orang']) ?></td>
                                    <td class="py-3">
                                        <?php if ($res['status_reservasi'] == 'Confirmed'): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-2">Confirmed</span>
                                        <?php elseif ($res['status_reservasi'] == 'Pending'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-2"><?= htmlspecialchars($res['status_reservasi']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3"><?= htmlspecialchars($res['preferensi_menu']) ?: '<span class="text-muted">-</span>' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">You have no reservations yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Taste Evolution Profile Section -->
        <div class="mt-5 fade-in" style="animation-delay: 0.2s;">
            <div class="section-divider">
                <span><i class="fas fa-chart-line me-1"></i> Taste Evolution Profile</span>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="taste-profile-card">
                        <div class="profile-icon">
                            <i class="fas fa-utensils text-primary"></i>
                        </div>
                        <div class="profile-label">Favorite Category</div>
                        <div class="profile-value"><?= htmlspecialchars($fav_cat) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="taste-profile-card">
                        <div class="profile-icon">
                            <i class="fas fa-heart text-primary"></i>
                        </div>
                        <div class="profile-label">Preferred Mood</div>
                        <div class="profile-value"><?= htmlspecialchars($pref_mood) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="taste-profile-card">
                        <div class="profile-icon">
                            <i class="fas fa-star text-primary"></i>
                        </div>
                        <div class="profile-label">Most Ordered Dish</div>
                        <div class="profile-value"><?= htmlspecialchars($most_ordered) ?></div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 rounded-4 p-4 taste-profile-card">
                        <h5 class="fw-bold text-center mb-4" style="color: var(--primary-color);">Taste Journey Timeline</h5>
                        
                        <div class="timeline text-start">
                            <div class="timeline-item">
                                <div class="timeline-date">2024</div>
                                <div class="timeline-content fw-semibold">
                                    <i class="fas fa-ice-cream text-warning me-2"></i> Dessert Lover
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date">2025</div>
                                <div class="timeline-content fw-semibold">
                                    <i class="fas fa-wine-glass-alt text-danger me-2"></i> French Cuisine Enthusiast
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date">2026</div>
                                <div class="timeline-content fw-semibold">
                                    <i class="fas fa-crown text-primary me-2"></i> Premium Fine Dining Preference
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
