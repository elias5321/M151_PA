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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Geräte ausleihen – IT Ausleihesystem</title>
    <link rel="stylesheet" href="https://web.fhnw.ch/fhnw-styleguide-v5/assets/css/fhnw.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- C8: Nav nur erreichbar wenn angemeldet (requireLogin) -->
<nav class="navbar navbar-expand-lg navbar-light" role="navigation" style="min-height: 60px">
    <a href="index.php" class="navbar-brand">
        <img src="https://web.fhnw.ch/fhnw-styleguide-v5/assets/img/fachhochschule-nordwestschweiz-fhnw-logo.svg" alt="FHNW - Fachhochschule Nordwestschweiz"/>
        <span class="navbar-title">IT Ausleihesystem</span>
    </a>
    <span class="navbar-title d-sm-none">IT Ausleihesystem</span>
    <ul class="navbar-nav ml-auto align-items-center flex-row">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active font-weight-bold" href="borrow.php">Ausleihen</a></li>
        <li class="nav-item"><a class="nav-link" href="my_loans.php">Meine Ausleihen</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="devices.php">Geräte</a></li>
        <?php endif; ?>
        <li class="nav-item dropdown ml-auto">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= safe($_SESSION['username']) ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="index.php">Mein Profil</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="change_password.php">Passwort ändern</a>
                <div class="dropdown-divider"></div>
                <form method="post" action="logout.php">
                    <button type="submit" class="dropdown-item text-danger">Abmelden</button>
                </form>
            </div>
        </li>
    </ul>
</nav>

<main class="container mt-4">

    <h1 class="h3 mb-4">Geräte ausleihen</h1>

    <?php foreach ($messages as $m): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="alert alert-success"><?= safe($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="alert alert-danger"><?= safe($e) ?></div>
    <?php endforeach; ?>

    <?php if (empty($devices)): ?>
        <p>Aktuell sind keine Geräte verfügbar.</p>
    <?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="thead-light">
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
                        <input type="date" name="due_date" min="<?= safe($minDate) ?>" required class="form-control form-control-sm" style="width:auto">
                        <button type="submit" class="btn btn-primary btn-sm">Ausleihen</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</main>

<footer id="footer" class="mt-5">
    <div class="container tools__footer pt-4">
        <div class="row">
            <div class="d-flex justify-content-center w-100">
                <p>
                    <a href="https://www.fhnw.ch/de/die-fhnw/it-support" target="_blank">
                        www.fhnw.ch/de/die-fhnw/it-support
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
