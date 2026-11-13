<?php
session_start();
require 'db.php';
$ADMIN_PASS = 'factory2026'; 
$message = '';
$generatedCodes = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['admin_login']) && $_POST['admin_pass'] === $ADMIN_PASS) { $_SESSION['is_admin'] = true; }
    elseif (isset($_POST['admin_logout'])) { unset($_SESSION['is_admin']); }
    elseif (isset($_POST['generate_codes']) && isset($_SESSION['is_admin'])) {
        $amount = (int)$_POST['amount'];
        $points = (int)$_POST['points'];
        $batch = trim($_POST['batch_name']);
        if ($amount > 0 && $amount <= 500) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO reward_codes (code, points_value, batch_name) VALUES (?, ?, ?)");
            for ($i = 0; $i < $amount; $i++) {
                $code = 'FACT-' . strtoupper(bin2hex(random_bytes(2))) . '-' . strtoupper(bin2hex(random_bytes(2)));
                $stmt->execute([$code, $points, $batch]);
                $generatedCodes[] = $code;
            }
            $pdo->commit();
            $message = "Successfully generated $amount codes for batch '$batch'.";
        }
    }
}
$stats = ['total_codes' => 0, 'redeemed_codes' => 0, 'total_users' => 0];
if (isset($_SESSION['is_admin'])) {
    $stats['total_codes'] = $pdo->query("SELECT COUNT(*) FROM reward_codes")->fetchColumn();
    $stats['redeemed_codes'] = $pdo->query("SELECT COUNT(*) FROM reward_codes WHERE is_redeemed = 1")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Factory Owner Dashboard</title><link rel="stylesheet" href="style.css"></head>
<body><div class="container admin-container"><h1>Factory Owner Dashboard</h1>
<?php if (!isset($_SESSION['is_admin'])): ?><p class="subtitle">Secure operational access.</p>
<form method="POST"><div class="form-group"><label>Admin Password</label><input type="password" name="admin_pass" required></div><button type="submit" name="admin_login">Access Dashboard</button></form>
<?php else: ?><div class="stats-grid"><div class="stat-card"><h3><?= $stats['total_codes'] ?></h3><p>Total Codes Printed</p></div><div class="stat-card"><h3><?= $stats['redeemed_codes'] ?></h3><p>Codes Claimed</p></div><div class="stat-card"><h3><?= $stats['total_codes'] > 0 ? round(($stats['redeemed_codes'] / $stats['total_codes']) * 100, 1) : 0 ?>%</h3><p>Redemption Rate</p></div><div class="stat-card"><h3><?= $stats['total_users'] ?></h3><p>Registered Customers</p></div></div>
<?php if ($message): ?><div class="alert success"><?= $message ?></div><?php endif; ?>
<div style="background: #fff; padding: 20px; border: 1px solid var(--border); border-radius: 8px;"><h2>Generate New Box Codes</h2><form method="POST"><div class="form-group"><label>Batch Name (e.g., Nov 2026 Production)</label><input type="text" name="batch_name" required></div><div class="stats-grid" style="margin-bottom: 0;"><div class="form-group"><label>Amount of Codes</label><input type="number" name="amount" min="1" max="500" value="50" required></div><div class="form-group"><label>Points Value per Code</label><input type="number" name="points" value="100" required></div></div><button type="submit" name="generate_codes" style="background: #111827;">Generate Codes for Printing</button></form></div>
<?php if (!empty($generatedCodes)): ?><div class="code-list"><strong>Export to Printer:</strong><br><br><?= implode("<br>", $generatedCodes) ?></div><?php endif; ?>
<div class="nav-links"><form method="POST"><button type="submit" name="admin_logout" style="background: transparent; color: var(--primary); border: none; box-shadow: none; width: auto; padding: 0;">Secure Logout</button></form></div><?php endif; ?>
</div></body></html>