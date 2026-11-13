<?php
session_start();
require 'db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        
        // Pass success message to login page
        $_SESSION['flash_message'] = "Registration successful! Please log in.";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $message = "Username already exists. Please choose another one."; 
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Loyalty Rewards</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Loyalty Rewards</h1>
        <p class="subtitle">Create a new customer account.</p>

        <?php if ($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>Choose Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Choose Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" style="background: #10b981;">Create Account</button>
        </form>

        <div class="nav-links">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
