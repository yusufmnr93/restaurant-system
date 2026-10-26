<?php
$is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base = $is_admin ? '../' : '';
?>
<footer class="text-light mt-auto w-100">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-6 mb-3">
                <h4 class="footer-title fs-3 mb-3">L'Essence D'Or</h4>
                <p class="text-muted pe-md-5">Experience the pinnacle of fine dining with curated culinary masterpieces in an elegant, modern ambiance.</p>
            </div>
            <div class="col-md-6 mb-3">
                <h5 class="footer-title mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= $base ?>index.php">Home</a></li>
                    <li class="mb-2"><a href="<?= $base ?>menu.php">Menu</a></li>
                    <li class="mb-2"><a href="<?= $base ?>reservation.php">Reservation</a></li>
                    <li class="mb-2"><a href="<?= $base ?>recommendation.php">Recommendation</a></li>
                    <li class="mb-2"><a href="<?= $base ?>feedback.php">Feedback</a></li>
                    <li class="mb-2"><a href="<?= $base ?>memory_journey.php">Memory Journey</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="text-center py-3" style="background-color: #111;">
        <p class="mb-0 text-muted small">&copy; <?= date('Y') ?> L'Essence D'Or. All Rights Reserved.</p>
    </div>
</footer>
