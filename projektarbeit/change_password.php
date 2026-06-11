<?php
// C8: Session starten und Zugriff prüfen
session_start();
require_once 'auth.php';
require_once 'db.php';
requireLogin();

/** @var \mysqli $conn */

// C7: Ausgaben werden mit safe() geschützt
function safe(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = trim($_POST['new_password'] ?? '');
    $confirmPw = trim($_POST['confirm_password'] ?? '');

    // C6: Alle Felder ausgefüllt?
    if (empty($currentPw) || empty($newPw) || empty($confirmPw)) {
        $errors[] = 'Alle Felder müssen ausgefüllt sein.';
    } elseif ($newPw !== $confirmPw) {
        // C6: Passwörter stimmen überein?
        $errors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
    } elseif (strlen($newPw) < 8) {
        // C6: Mindestlänge prüfen
        $errors[] = 'Neues Passwort muss mindestens 8 Zeichen lang sein.';
    } else {
        // C6: Passwort-Komplexität prüfen
        $pattern = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$/';
        if (!preg_match($pattern, $newPw)) {
            $errors[] = 'Passwort muss Gross-/Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.';
        }
    }

    if (empty($errors)) {
        $userId = $_SESSION['user_id'];
        // C19: Prepared Statement verhindert SQL-Injection
        $stmt   = mysqli_prepare($conn, 'SELECT password_hash FROM users WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // C11: password_verify() prüft gegen gespeicherten bcrypt-Hash
        if ($hash === null || !password_verify($currentPw, $hash)) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        } else {
            // C15/C11: Neues Passwort mit bcrypt hashen und speichern
            $newHash = password_hash($newPw, PASSWORD_BCRYPT);
            // C19: Prepared Statement verhindert SQL-Injection
            $stmt    = mysqli_prepare($conn, 'UPDATE users SET password_hash = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $newHash, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Passwort ändern – IT Ausleihesystem</title>
    <link rel="stylesheet" href="https://web.fhnw.ch/fhnw-styleguide-v5/assets/css/fhnw.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light" role="navigation" style="min-height: 60px">
    <a href="index.php" class="navbar-brand">
        <img src="https://web.fhnw.ch/fhnw-styleguide-v5/assets/img/fachhochschule-nordwestschweiz-fhnw-logo.svg" alt="FHNW - Fachhochschule Nordwestschweiz"/>
        <span class="navbar-title">IT Ausleihesystem</span>
    </a>
    <span class="navbar-title d-sm-none">IT Ausleihesystem</span>
    <ul class="navbar-nav ml-auto align-items-center flex-row">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php">Ausleihen</a></li>
        <li class="nav-item"><a class="nav-link" href="my_loans.php">Meine Ausleihen</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="devices.php">Geräte</a></li>
        <?php endif; ?>
        <li class="nav-item dropdown ml-auto">
            <a class="nav-link dropdown-toggle active font-weight-bold" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= safe($_SESSION['username']) ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="index.php">Mein Profil</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item active" href="change_password.php">Passwort ändern</a>
                <div class="dropdown-divider"></div>
                <form method="post" action="logout.php">
                    <button type="submit" class="dropdown-item text-danger">Abmelden</button>
                </form>
            </div>
        </li>
    </ul>
</nav>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- C15: Passwort ändern – nur für angemeldete Personen zugänglich (C8) -->
                    <h1 class="h4 mb-4">Passwort ändern</h1>

                    <?php if ($success): ?>
                        <div class="alert alert-success">Passwort erfolgreich geändert.</div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                <?php foreach ($errors as $e): ?>
                                    <!-- C7: safe() schützt vor XSS -->
                                    <li><?= safe($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="change_password.php">
                        <div class="form-group">
                            <label for="current_password">Aktuelles Passwort</label>
                            <!-- C5: required – clientseitige Validierung -->
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Neues Passwort</label>
                            <!-- C5: required + minlength – clientseitige Validierung -->
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Neues Passwort bestätigen</label>
                            <!-- C5: required + minlength – clientseitige Validierung -->
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Passwort ändern</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        <a href="index.php">Zurück zum Dashboard</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
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
