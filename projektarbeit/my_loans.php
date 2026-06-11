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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meine Ausleihen – IT Ausleihesystem</title>
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
        <li class="nav-item"><a class="nav-link" href="borrow.php">Ausleihen</a></li>
        <li class="nav-item"><a class="nav-link active font-weight-bold" href="my_loans.php">Meine Ausleihen</a></li>
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

    <!-- C17/C18: Benutzer sieht und verwaltet nur seine eigenen Ausleihen -->
    <h1 class="h3 mb-4">Meine Ausleihen</h1>

    <?php foreach ($messages as $m): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="alert alert-success"><?= safe($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="alert alert-danger"><?= safe($e) ?></div>
    <?php endforeach; ?>

    <?php if (empty($loans)): ?>
        <p>Du hast aktuell keine aktiven Ausleihen.</p>
    <?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="thead-light">
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
                        <button type="submit" class="btn btn-secondary btn-sm">Zurückgeben</button>
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
