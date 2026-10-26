<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM menu ORDER BY rating_menu DESC");
$menus = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emotional Journey Recommendations - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5 flex-grow-1">
        <h2 class="text-center fw-bold mb-2" style="color: var(--primary-color);">Emotional Journey Recommendations</h2>
        <p class="text-center text-muted mb-5">Discover the story, mood, and sensory experience behind every dish.</p>
        
        <div class="row g-4">
            <?php foreach ($menus as $index => $menu): ?>
                <?php
                $image = !empty($menu['image']) ? $menu['image'] : "images/default.jpg";
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="emotional-card h-100 shadow-sm">
                        <img src="<?= $image ?>" class="card-img-top menu-img" alt="<?= htmlspecialchars($menu['nama_menu']) ?>">
                        <div class="card-body text-center p-4">
                            <h5 class="fw-bold fs-5 mb-1"><?= htmlspecialchars($menu['nama_menu']) ?></h5>
                            <span class="badge bg-secondary mb-2"><?= htmlspecialchars($menu['kategori_menu']) ?></span>
                            <div class="d-flex justify-content-center align-items-center fs-5 mb-3">
                                <i class="fas fa-star text-warning me-2"></i>
                                <span class="fw-bold" style="color: var(--primary-color);"><?= htmlspecialchars($menu['rating_menu']) ?></span>
                            </div>
                            
                            <?php if (!empty($menu['story_behind'])): ?>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#details<?= $index ?>" aria-expanded="false">
                                <i class="fas fa-sparkles me-1"></i> Explore Emotional Journey
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($menu['story_behind'])): ?>
                        <div class="collapse" id="details<?= $index ?>">
                            <div class="px-3 pb-3">
                                <!-- Story Behind -->
                                <div class="emotional-detail d-flex align-items-start">
                                    <div class="detail-icon story"><i class="fas fa-book-open"></i></div>
                                    <div>
                                        <span class="detail-label">Story Behind The Dish</span>
                                        <span class="detail-text"><?= htmlspecialchars($menu['story_behind']) ?></span>
                                    </div>
                                </div>

                                <!-- Recommended Atmosphere -->
                                <?php if (!empty($menu['recommended_atmosphere'])): ?>
                                <div class="emotional-detail d-flex align-items-start">
                                    <div class="detail-icon atmosphere"><i class="fas fa-moon"></i></div>
                                    <div>
                                        <span class="detail-label">Recommended Atmosphere</span>
                                        <span class="detail-text"><?= htmlspecialchars($menu['recommended_atmosphere']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Why This Menu Fits Your Mood -->
                                <?php if (!empty($menu['mood_fit'])): ?>
                                <div class="emotional-detail d-flex align-items-start">
                                    <div class="detail-icon mood"><i class="fas fa-heart"></i></div>
                                    <div>
                                        <span class="detail-label">Why This Menu Fits Your Mood</span>
                                        <span class="detail-text"><?= htmlspecialchars($menu['mood_fit']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Aroma Notes -->
                                <?php if (!empty($menu['aroma_notes'])): ?>
                                <div class="emotional-detail d-flex align-items-start">
                                    <div class="detail-icon aroma"><i class="fas fa-leaf"></i></div>
                                    <div>
                                        <span class="detail-label">Aroma Notes</span>
                                        <span class="detail-text"><?= htmlspecialchars($menu['aroma_notes']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Texture Profile -->
                                <?php if (!empty($menu['texture_profile'])): ?>
                                <div class="emotional-detail d-flex align-items-start">
                                    <div class="detail-icon texture"><i class="fas fa-hand-sparkles"></i></div>
                                    <div>
                                        <span class="detail-label">Texture Profile</span>
                                        <span class="detail-text"><?= htmlspecialchars($menu['texture_profile']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Presentation Style -->
                                <?php if (!empty($menu['presentation_style'])): ?>
                                <div class="emotional-detail d-flex align-items-start">
                                    <div class="detail-icon presentation"><i class="fas fa-palette"></i></div>
                                    <div>
                                        <span class="detail-label">Presentation Style</span>
                                        <span class="detail-text"><?= htmlspecialchars($menu['presentation_style']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
