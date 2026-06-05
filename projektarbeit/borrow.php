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
    $deviceId = (int)($_POST['device_id'] ?? 0);
    $dueDate  = trim($_POST['due_date'] ?? '');

    // C6: Serverseitige Validierung der Eingaben
    if ($deviceId <= 0) {
        $errors[] = 'Ungültiges Gerät.';
    } elseif (empty($dueDate)) {
        $errors[] = 'Bitte ein Rückgabedatum angeben.';
    } elseif ($dueDate <= date('Y-m-d')) {
        // C6: Datum muss in der Zukunft liegen
        $errors[] = 'Rückgabedatum muss in der Zukunft liegen.';
    } else {
        // C19: Prepared Statement – prüft ob Gerät noch verfügbar ist
        $stmt = mysqli_prepare($conn, 'SELECT is_available FROM devices WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $deviceId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $isAvailable);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // C6: Verfügbarkeit serverseitig prüfen (nicht nur clientseitig vertrauen)
        if (!$isAvailable) {
            $errors[] = 'Dieses Gerät ist bereits ausgeliehen.';
        } else {
            $userId = $_SESSION['user_id'];

            // C16: Ausleihe in DB erfassen – C19: Prepared Statement
            $stmt = mysqli_prepare($conn,
                'INSERT INTO loans (device_id, user_id, due_date) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iis', $deviceId, $userId, $dueDate);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // C19: Prepared Statement – Gerät als ausgeliehen markieren
            $stmt = mysqli_prepare($conn, 'UPDATE devices SET is_available = 0 WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $deviceId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $messages[] = 'Gerät erfolgreich ausgeliehen.';
        }
    }
}

// C19: Keine Benutzereingabe – direktes mysqli_query ist sicher
$result  = mysqli_query($conn,
    'SELECT id, name, category, serial_number, description FROM devices WHERE is_available = 1 ORDER BY name');
$devices = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

$minDate = date('Y-m-d', strtotime('+1 day'));
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Geräte ausleihen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card-wide">

    <!-- C8: Nav nur erreichbar wenn angemeldet (requireLogin) -->
    <div class="nav-bar">
        <a href="index.php">Dashboard</a>
        <a href="borrow.php" class="active">Ausleihen</a>
        <a href="my_loans.php">Meine Ausleihen</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="devices.php">Geräte verwalten</a>
        <?php endif; ?>
        <a href="change_password.php">Passwort</a>
        <form method="post" action="logout.php">
            <button class="btn-nav">Abmelden</button>
        </form>
    </div>

    <h1>Geräte ausleihen</h1>

    <?php foreach ($messages as $m): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="success"><?= safe($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="errors"><?= safe($e) ?></div>
    <?php endforeach; ?>

    <?php if (empty($devices)): ?>
        <p>Aktuell sind keine Geräte verfügbar.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Kategorie</th>
                <th>Seriennummer</th>
                <th>Beschreibung</th>
                <th>Rückgabe bis</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devices as $d): ?>
            <tr>
                <!-- C7: safe() schützt alle DB-Ausgaben vor XSS -->
                <td><?= safe($d['name']) ?></td>
                <td><?= safe($d['category'] ?? '') ?></td>
                <td><?= safe($d['serial_number'] ?? '') ?></td>
                <td><?= safe($d['description'] ?? '') ?></td>
                <td>
                    <!-- C16: Ausleihe erfassen -->
                    <form method="post" action="borrow.php" class="row-form">
                        <input type="hidden" name="device_id" value="<?= (int)$d['id'] ?>">
                        <!-- C5: type="date" + required + min – clientseitige Validierung -->
                        <input type="date" name="due_date" min="<?= safe($minDate) ?>" required>
                        <button type="submit" class="btn-sm">Ausleihen</button>
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
