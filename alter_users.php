<?php
require_once 'config.php';

try {
    // Check if email column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    $emailExists = $stmt->fetch();

    if (!$emailExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER username");
        echo "Successfully added 'email' column to 'users' table.\n";
    } else {
        echo "'email' column already exists.\n";
    }

    // Check if created_at column already exists
    $stmt2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    $createdAtExists = $stmt2->fetch();

    if (!$createdAtExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER role");
        echo "Successfully added 'created_at' column to 'users' table.\n";
    } else {
        echo "'created_at' column already exists.\n";
    }

    // Seed existing users with default/inferred emails
    $users = $pdo->query("SELECT * FROM users")->fetchAll();
    foreach ($users as $u) {
        if (empty($u['email'])) {
            $email = $u['username'] . "@goldenessence.com";
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id_user = ?");
            $stmt->execute([$email, $u['id_user']]);
            echo "Seeded email for user '{$u['username']}' as '$email'.\n";
        }
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
