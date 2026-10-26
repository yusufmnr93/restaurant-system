<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$id_pelanggan = 'PLG-' . $_SESSION['id_user'];
$stmt_pelanggan = $pdo->prepare("INSERT IGNORE INTO pelanggan (id_pelanggan, nama_pelanggan) VALUES (?, ?)");
$stmt_pelanggan->execute([$id_pelanggan, $_SESSION['nama']]);

$success = '';
$error = '';

// Fetch menus for the dropdown
$menu_stmt = $pdo->query("SELECT id_menu, nama_menu FROM menu");
$menus = $menu_stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    $id_menu = $_POST['id_menu'];
    $rating_aroma = $_POST['rating_aroma'] ?? null;
    $rating_texture = $_POST['rating_texture'] ?? null;
    $rating_presentation = $_POST['rating_presentation'] ?? null;
    $rating_story = $_POST['rating_story'] ?? null;
    $overall_emotion = $_POST['overall_emotion'] ?? null;
    
    $id_feedback = uniqid('FDB-');

    $stmt = $pdo->prepare("INSERT INTO feedback (id_feedback, komentar, rating, rating_aroma, rating_texture, rating_presentation, rating_story, overall_emotion, id_pelanggan, id_menu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$id_feedback, $komentar, $rating, $rating_aroma, $rating_texture, $rating_presentation, $rating_story, $overall_emotion, $id_pelanggan, $id_menu])) {
        // Also create a memory journey entry
        $menu_data = $pdo->prepare("SELECT nama_menu, story_behind FROM menu WHERE id_menu = ?");
        $menu_data->execute([$id_menu]);
        $menu_info = $menu_data->fetch();
        
        if ($menu_info) {
            $id_memory = uniqid('MEM-');
            $emotional_memory = $overall_emotion ?? 'Memorable';
            
            // Generate reinforcement message
            $reinforcement = "Thank you for experiencing " . $menu_info['nama_menu'] . ". ";
            if (!empty($menu_info['story_behind'])) {
                $short_story = substr($menu_info['story_behind'], 0, 150);
                $reinforcement .= $short_story . "... This dish was designed to create a warm and elegant dining memory.";
            } else {
                $reinforcement .= "This dish was crafted with care to create a warm and elegant dining memory for you.";
            }
            
            // Fetch the customer's latest reservation to associate it
            $res_stmt = $pdo->prepare("SELECT id_reservasi FROM reservasi WHERE id_pelanggan = ? ORDER BY tanggal_reservasi DESC, jam_reservasi DESC LIMIT 1");
            $res_stmt->execute([$id_pelanggan]);
            $id_reservasi = $res_stmt->fetchColumn() ?: null;

            $mem_stmt = $pdo->prepare("INSERT INTO memory_journey (id_memory, id_pelanggan, id_menu, id_reservasi, emotional_memory, reinforcement_message) VALUES (?, ?, ?, ?, ?, ?)");
            $mem_stmt->execute([$id_memory, $id_pelanggan, $id_menu, $id_reservasi, $emotional_memory, $reinforcement]);
        }
        
        $success = "Thank you! Your multi-sensory experience has been recorded.";
    } else {
        $error = "Failed to submit feedback. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow border-0">
                    <div class="card-header py-3 text-center">
                        <h4 class="mb-0">Share Your Experience</h4>
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
                                <label class="form-label fw-semibold">Which menu item did you try?</label>
                                <select name="id_menu" class="form-select rounded-pill" required>
                                    <option value="">-- Select Menu --</option>
                                    <?php foreach ($menus as $m): ?>
                                        <option value="<?= htmlspecialchars($m['id_menu']) ?>"><?= htmlspecialchars($m['nama_menu']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Overall Rating (1 to 5)</label>
                                <input type="number" name="rating" class="form-control rounded-pill" min="1" max="5" step="0.1" required>
                            </div>

                            <!-- Multi-Sensory Experience Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-hand-sparkles me-1"></i> Multi-Sensory Experience</span>
                            </div>

                            <!-- Aroma Rating -->
                            <div class="sensory-group">
                                <div class="sensory-label">
                                    <i class="fas fa-leaf" style="color: #689F38;"></i> Aroma Experience
                                </div>
                                <div class="sensory-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating_aroma" id="aroma<?= $i ?>" value="<?= $i ?>">
                                        <label for="aroma<?= $i ?>">&#9733;</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Texture Rating -->
                            <div class="sensory-group">
                                <div class="sensory-label">
                                    <i class="fas fa-hand-sparkles" style="color: #F57C00;"></i> Texture Experience
                                </div>
                                <div class="sensory-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating_texture" id="texture<?= $i ?>" value="<?= $i ?>">
                                        <label for="texture<?= $i ?>">&#9733;</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Presentation Rating -->
                            <div class="sensory-group">
                                <div class="sensory-label">
                                    <i class="fas fa-palette" style="color: #8E24AA;"></i> Presentation Experience
                                </div>
                                <div class="sensory-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating_presentation" id="presentation<?= $i ?>" value="<?= $i ?>">
                                        <label for="presentation<?= $i ?>">&#9733;</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Story Rating -->
                            <div class="sensory-group">
                                <div class="sensory-label">
                                    <i class="fas fa-book-open" style="color: #1976D2;"></i> Story Experience
                                </div>
                                <div class="sensory-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating_story" id="story<?= $i ?>" value="<?= $i ?>">
                                        <label for="story<?= $i ?>">&#9733;</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Overall Emotion -->
                            <div class="section-divider">
                                <span><i class="fas fa-face-smile me-1"></i> Overall Emotion</span>
                            </div>

                            <div class="mb-4">
                                <p class="text-muted small mb-3">How did this experience make you feel?</p>
                                <input type="hidden" name="overall_emotion" id="overall_emotion" value="">
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="emotion-option" data-value="Happy" onclick="selectEmotion(this)">😊 Happy</div>
                                    <div class="emotion-option" data-value="Relaxed" onclick="selectEmotion(this)">😌 Relaxed</div>
                                    <div class="emotion-option" data-value="Excited" onclick="selectEmotion(this)">🤩 Excited</div>
                                    <div class="emotion-option" data-value="Memorable" onclick="selectEmotion(this)">💫 Memorable</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Additional Comment</label>
                                <textarea name="komentar" class="form-control" rows="4" placeholder="Tell us about your complete dining experience..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold fs-5">Submit Feedback</button>
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
        function selectEmotion(el) {
            document.querySelectorAll('.emotion-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('overall_emotion').value = el.dataset.value;
        }
    </script>
</body>
</html>
