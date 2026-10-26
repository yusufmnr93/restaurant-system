<?php
session_start();
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error = "Username already taken.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nama, $username, $hashed_password, $role]);
                $id_user = $pdo->lastInsertId();

                // Cari nomor pelanggan terakhir
                $stmtLast = $pdo->query("
                SELECT MAX(CAST(SUBSTRING(id_pelanggan,5) AS UNSIGNED)) AS last_number
                FROM pelanggan
                ");

                $lastNumber = $stmtLast->fetch()['last_number'];

                $newNumber = ($lastNumber == null) ? 1 : $lastNumber + 1;

                $id_pelanggan = 'PLG-' . $newNumber;

                $stmt_pelg = $pdo->prepare(
                    "INSERT INTO pelanggan (id_pelanggan,nama_pelanggan)
                    VALUES (?,?)"
                );

                $stmt_pelg->execute([$id_pelanggan,$nama]);

                $pdo->commit();

                $_SESSION['register_success'] = "Registration successful! You can now login.";
                header("Location: login.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - L'Essence D'Or</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header text-center py-3">
                        <h4 class="mb-0">Create an Account</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="nama" class="form-control" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Register</button>
                        </form>
                        <div class="mt-4 text-center">
                            Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 500;">Login here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
