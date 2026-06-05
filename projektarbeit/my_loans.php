<?php
// C8: Session starten und Login prüfen
session_start();
require_once 'auth.php';
require_once 'db.php';
requireLogin();

/** @var \mysqli $conn */

// C7: Ausgaben werden mit safe() vor XSS geschützt
function safe(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$messages = [];
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if ($loanId > 0) {
        // C17/C18: Prüfen ob die Ausleihe dem angemeldeten Benutzer gehört
        // C19: Prepared Statement verhindert SQL-Injection
        $stmt = mysqli_prepare($conn,
            'SELECT device_id FROM loans WHERE id = ? AND user_id = ? AND returned_at IS NULL');
        mysqli_stmt_bind_param($stmt, 'ii', $loanId, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $deviceId);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($deviceId) {
            // C18: Nur der Ausleiher kann zurückgeben (user_id-Prüfung oben)
            // C19: Prepared Statement
            $stmt = mysqli_prepare($conn, 'UPDATE loans SET returned_at = NOW() WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $loanId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // C19: Prepared Statement – Gerät wieder verfügbar setzen
            $stmt = mysqli_prepare($conn, 'UPDATE devices SET is_available = 1 WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $deviceId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $messages[] = 'Gerät erfolgreich zurückgegeben.';
        } else {
            $errors[] = 'Ausleihe nicht gefunden.';
        }
    }
}

$userId = $_SESSION['user_id'];
// C19: Prepared Statement – nur eigene Ausleihen laden (C17)
$stmt   = mysqli_prepare($conn,
    'SELECT l.id, d.name, d.category, d.serial_number, l.borrowed_at, l.due_date
     FROM loans l
     JOIN devices d ON d.id = l.device_id
     WHERE l.user_id = ? AND l.returned_at IS NULL
     ORDER BY l.due_date ASC');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$loans  = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Ausleihen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card-wide">

    <!-- C8: Nav nur erreichbar wenn angemeldet (requireLogin) -->
    <div class="nav-bar">
        <a href="index.php">Dashboard</a>
        <a href="borrow.php">Ausleihen</a>
        <a href="my_loans.php" class="active">Meine Ausleihen</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="devices.php">Geräte verwalten</a>
        <?php endif; ?>
        <a href="change_password.php">Passwort</a>
        <form method="post" action="logout.php">
            <button class="btn-nav">Abmelden</button>
        </form>
    </div>

    <!-- C17/C18: Benutzer sieht und verwaltet nur seine eigenen Ausleihen -->
    <h1>Meine Ausleihen</h1>

    <?php foreach ($messages as $m): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="success"><?= safe($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="errors"><?= safe($e) ?></div>
    <?php endforeach; ?>

    <?php if (empty($loans)): ?>
        <p>Du hast aktuell keine aktiven Ausleihen.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Gerät</th>
                <th>Kategorie</th>
                <th>Seriennummer</th>
                <th>Ausgeliehen am</th>
                <th>Rückgabe bis</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loans as $loan):
                $overdue = $loan['due_date'] < $today;
            ?>
            <tr class="<?= $overdue ? 'overdue' : '' ?>">
                <!-- C7: safe() schützt alle DB-Ausgaben vor XSS -->
                <td><?= safe($loan['name']) ?></td>
                <td><?= safe($loan['category'] ?? '') ?></td>
                <td><?= safe($loan['serial_number'] ?? '') ?></td>
                <td><?= safe($loan['borrowed_at']) ?></td>
                <td><?= safe($loan['due_date']) ?><?= $overdue ? ' &mdash; <strong>überfällig</strong>' : '' ?></td>
                <td>
                    <!-- C18: Zurückgeben nur für eigene Ausleihen (loan_id + user_id geprüft) -->
                    <form method="post" action="my_loans.php">
                        <input type="hidden" name="loan_id" value="<?= (int)$loan['id'] ?>">
                        <button type="submit" class="btn-sm">Zurückgeben</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>
</body>
</html>
