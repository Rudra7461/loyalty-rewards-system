<?php
session_start();
require 'db.php';

// Force login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';
$ip = $_SERVER['REMOTE_ADDR'];

// Anti-Brute Force Mechanism
function checkRateLimit($pdo, $ip) {
    $stmt = $pdo->prepare("SELECT failed_attempts, last_attempt FROM rate_limits WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $limit = $stmt->fetch();
    if ($limit && $limit['failed_attempts'] >= 5) {
        $timeDiff = time() - strtotime($limit['last_attempt']);
        if ($timeDiff < 900) { // 15 minute lockout
            return false;
        } else {
            $pdo->prepare("UPDATE rate_limits SET failed_attempts = 0 WHERE ip_address = ?")->execute([$ip]);
        }
    }
    return true;
}

function recordFailedAttempt($pdo, $ip) {
    $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, failed_attempts) VALUES (?, 1) ON DUPLICATE KEY UPDATE failed_attempts = failed_attempts + 1, last_attempt = CURRENT_TIMESTAMP");
    $stmt->execute([$ip]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'redeem') {
        $code = strtoupper(trim($_POST['reward_code']));
        
        if (!checkRateLimit($pdo, $ip)) {
            $message = "Too many failed attempts. Locked out for 15 minutes."; $messageType = "error";
        } else {
            // Atomic Transaction to prevent race condition fraud (Double spend)
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("SELECT id, points_value FROM reward_codes WHERE code = ? AND is_redeemed = 0");
            $stmt->execute([$code]);
            $reward = $stmt->fetch();

            if ($reward) {
                // Mark as redeemed
                $updateCode = $pdo->prepare("UPDATE reward_codes SET is_redeemed = 1, redeemed_by = ?, redeemed_at = CURRENT_TIMESTAMP WHERE id = ?");
                $updateCode->execute([$_SESSION['user_id'], $reward['id']]);
                
                // Add points
                $updateUser = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
                $updateUser->execute([$reward['points_value'], $_SESSION['user_id']]);
                
                $pdo->commit();
                $pdo->prepare("UPDATE rate_limits SET failed_attempts = 0 WHERE ip_address = ?")->execute([$ip]);
                
                $message = "Success! You earned {$reward['points_value']} points."; $messageType = "success";
            } else {
                $pdo->rollBack();
                recordFailedAttempt($pdo, $ip);
                $message = "Invalid or already redeemed code."; $messageType = "error";
            }
        }
    }
    elseif ($action === 'logout') {
        session_destroy(); 
        header("Location: login.php"); 
        exit;
    }
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Loyalty Rewards</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Loyalty Rewards</h1>
        <p class="subtitle">Welcome back, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
        
        <?php if ($message): ?>
            <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= number_format($currentUser['total_points']) ?></h3>
                <p>Total Points Balance</p>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="redeem">
            <div class="form-group">
                <label>Found a code inside your box?</label>
                <input type="text" name="reward_code" placeholder="e.g. FACT-XXXX-XXXX" required>
            </div>
            <button type="submit">Redeem Code</button>
        </form>
        
        <div class="nav-links">
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="logout"><a href="#" onclick="this.parentNode.submit()">Log Out</a></form>
        </div>
    </div>
</body>
</html>
