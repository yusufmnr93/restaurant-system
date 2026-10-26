<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$id_pelanggan = 'PLG-' . $_SESSION['id_user'];

// Fetch memory journey data
$stmt = $pdo->prepare("
    SELECT mj.id_memory, mj.id_menu, mj.emotional_memory, mj.reinforcement_message, mj.created_at, 
           m.nama_menu, m.story_behind, m.kategori_menu, m.image,
           r.tanggal_reservasi
    FROM memory_journey mj
    JOIN menu m ON mj.id_menu = m.id_menu
    JOIN reservasi r ON mj.id_reservasi = r.id_reservasi
    WHERE mj.id_pelanggan = ?
    ORDER BY mj.created_at ASC
");
$stmt->execute([$id_pelanggan]);
$memories = $stmt->fetchAll();

$has_memories = count($memories) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Journey - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5 flex-grow-1">
        <h2 class="text-center fw-bold mb-2" style="color: var(--primary-color);">Your Memory Journey</h2>
        <p class="text-center text-muted mb-5">Relive the beautiful emotions and stories of your past dining experiences.</p>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($has_memories): ?>
                    <div class="timeline">
                        <?php foreach ($memories as $mem): ?>
                            <?php
                            $image = !empty($mem['image']) ? $mem['image'] : "images/default.jpg";
                            
                            $emotion_emoji = '✨';
                            if ($mem['emotional_memory'] == 'Happy') $emotion_emoji = '😊';
                            if ($mem['emotional_memory'] == 'Relaxed') $emotion_emoji = '😌';
                            if ($mem['emotional_memory'] == 'Excited') $emotion_emoji = '🤩';
                            if ($mem['emotional_memory'] == 'Memorable') $emotion_emoji = '💫';
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-date mb-2"><?= date('F d, Y', strtotime($mem['tanggal_reservasi'])) ?></div>
                                <div class="memory-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <img src="<?= $image ?>" alt="<?= htmlspecialchars($mem['nama_menu']) ?>" class="img-fluid rounded-4 shadow-sm" style="height: 160px; object-fit: cover; width: 100%;">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($mem['nama_menu']) ?> Experience</h4>
                                                <span class="memory-emotion-badge">
                                                    <?= $emotion_emoji ?> <?= htmlspecialchars($mem['emotional_memory']) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <small class="text-uppercase fw-bold" style="color: var(--primary-color); letter-spacing: 1px;">The Story</small>
                                                <p class="text-muted small mb-0 mt-1"><?= htmlspecialchars($mem['story_behind']) ?></p>
                                            </div>
                                            
                                            <div class="memory-quote">
                                                "<?= htmlspecialchars($mem['reinforcement_message']) ?>"
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info bg-white border-0 shadow-sm rounded-4 p-4 text-center mb-5">
                        <i class="fas fa-info-circle text-primary fs-3 mb-3"></i>
                        <h5 class="fw-bold">Your journey is just beginning.</h5>
                        <p class="text-muted mb-0">Submit feedback after your dining experience to begin mapping your emotional memory journey.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
