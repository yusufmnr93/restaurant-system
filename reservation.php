<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure pelanggan record exists dynamically
$id_pelanggan = 'PLG-' . $_SESSION['id_user'];
$stmt_pelanggan = $pdo->prepare("INSERT IGNORE INTO pelanggan (id_pelanggan, nama_pelanggan) VALUES (?, ?)");
$stmt_pelanggan->execute([$id_pelanggan, $_SESSION['nama']]);

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal_reservasi = $_POST['tanggal_reservasi'];
    $jam_reservasi = $_POST['jam_reservasi'];
    $jumlah_orang = $_POST['jumlah_orang'];
    $preferensi_menu = $_POST['preferensi_menu'];
    $mood_preference = $_POST['mood_preference'] ?? null;
    $favorite_flavor = $_POST['favorite_flavor'] ?? null;
    $special_occasion = $_POST['special_occasion'] ?? 'None';
    
    $id_reservasi = uniqid('RSV-');
    $status_reservasi = 'Confirmed';

    $stmt = $pdo->prepare("INSERT INTO reservasi (id_reservasi, tanggal_reservasi, jam_reservasi, jumlah_orang, status_reservasi, preferensi_menu, mood_preference, favorite_flavor, special_occasion, id_pelanggan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$id_reservasi, $tanggal_reservasi, $jam_reservasi, $jumlah_orang, $status_reservasi, $preferensi_menu, $mood_preference, $favorite_flavor, $special_occasion, $id_pelanggan])) {
        $success = "Reservation confirmed successfully! We'll prepare your perfect ambiance.";
    } else {
        $error = "Failed to create reservation. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header py-3 text-center">
                        <h4 class="mb-0">Table Reservation</h4>
                    </div>
                    <div class="card-body p-5">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Customer Name</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($_SESSION['nama']) ?>" readonly>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Reservation Date</label>
                                    <input type="date" name="tanggal_reservasi" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Reservation Time</label>
                                    <input type="time" name="jam_reservasi" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Number of Guests</label>
                                    <input type="number" name="jumlah_orang" class="form-control" min="1" max="20" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Menu Preference (Optional)</label>
                                <textarea name="preferensi_menu" class="form-control" rows="3" placeholder="Any dietary requirements or specific menu items you'd like?"></textarea>
                            </div>

                            <!-- Emotional Journey Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-heart me-1"></i> Pre-Arrival Mood Setting</span>
                            </div>

                            <!-- Mood Preference -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Mood Preference</label>
                                <p class="text-muted small mb-3">What kind of experience are you looking for?</p>
                                <input type="hidden" name="mood_preference" id="mood_preference" value="">
                                <div class="mood-selector">
                                    <div class="mood-option" data-value="Romantic Dinner" onclick="selectMood(this)">
                                        <span class="mood-icon">🕯️</span>
                                        <span class="mood-label">Romantic Dinner</span>
                                    </div>
                                    <div class="mood-option" data-value="Family Gathering" onclick="selectMood(this)">
                                        <span class="mood-icon">👨‍👩‍👧‍👦</span>
                                        <span class="mood-label">Family Gathering</span>
                                    </div>
                                    <div class="mood-option" data-value="Celebration" onclick="selectMood(this)">
                                        <span class="mood-icon">🎉</span>
                                        <span class="mood-label">Celebration</span>
                                    </div>
                                    <div class="mood-option" data-value="Business Meeting" onclick="selectMood(this)">
                                        <span class="mood-icon">💼</span>
                                        <span class="mood-label">Business Meeting</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Favorite Flavor -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Favorite Flavor</label>
                                <p class="text-muted small mb-3">What taste profile speaks to you today?</p>
                                <input type="hidden" name="favorite_flavor" id="favorite_flavor" value="">
                                <div class="mood-selector">
                                    <div class="mood-option" data-value="Sweet" onclick="selectFlavor(this)">
                                        <span class="mood-icon">🍯</span>
                                        <span class="mood-label">Sweet</span>
                                    </div>
                                    <div class="mood-option" data-value="Savory" onclick="selectFlavor(this)">
                                        <span class="mood-icon">🧀</span>
                                        <span class="mood-label">Savory</span>
                                    </div>
                                    <div class="mood-option" data-value="Spicy" onclick="selectFlavor(this)">
                                        <span class="mood-icon">🌶️</span>
                                        <span class="mood-label">Spicy</span>
                                    </div>
                                    <div class="mood-option" data-value="Umami" onclick="selectFlavor(this)">
                                        <span class="mood-icon">🍄</span>
                                        <span class="mood-label">Umami</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Occasion -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Special Occasion</label>
                                <p class="text-muted small mb-3">Let us know if you're celebrating something special.</p>
                                <input type="hidden" name="special_occasion" id="special_occasion" value="None">
                                <div class="mood-selector">
                                    <div class="mood-option" data-value="Birthday" onclick="selectOccasion(this)">
                                        <span class="mood-icon">🎂</span>
                                        <span class="mood-label">Birthday</span>
                                    </div>
                                    <div class="mood-option" data-value="Anniversary" onclick="selectOccasion(this)">
                                        <span class="mood-icon">💍</span>
                                        <span class="mood-label">Anniversary</span>
                                    </div>
                                    <div class="mood-option" data-value="Graduation" onclick="selectOccasion(this)">
                                        <span class="mood-icon">🎓</span>
                                        <span class="mood-label">Graduation</span>
                                    </div>
                                    <div class="mood-option" data-value="None" onclick="selectOccasion(this)">
                                        <span class="mood-icon">✨</span>
                                        <span class="mood-label">None</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-5 fw-semibold rounded-pill">Confirm Reservation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        function selectMood(el) {
            el.parentElement.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('mood_preference').value = el.dataset.value;
        }
        function selectFlavor(el) {
            el.parentElement.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('favorite_flavor').value = el.dataset.value;
        }
        function selectOccasion(el) {
            el.parentElement.querySelectorAll('.mood-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('special_occasion').value = el.dataset.value;
        }
    </script>
</body>
</html>
