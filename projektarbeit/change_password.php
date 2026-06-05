<?php
session_start();
require_once 'auth.php';
require_once 'db.php';
requireLogin();

/** @var \mysqli $conn */

function safe(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = trim($_POST['new_password'] ?? '');
    $confirmPw = trim($_POST['confirm_password'] ?? '');

    if (empty($currentPw) || empty($newPw) || empty($confirmPw)) {
        $errors[] = 'Alle Felder müssen ausgefüllt sein.';
    } elseif ($newPw !== $confirmPw) {
        $errors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
    } elseif (strlen($newPw) < 8) {
        $errors[] = 'Neues Passwort muss mindestens 8 Zeichen lang sein.';
    } else {
        $pattern = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$/';
        if (!preg_match($pattern, $newPw)) {
            $errors[] = 'Passwort muss Gross-/Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.';
        }
    }

    if (empty($errors)) {
        $userId = $_SESSION['user_id'];
        $stmt   = mysqli_prepare($conn, 'SELECT password_hash FROM users WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!password_verify($currentPw, $hash)) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        } else {
            $newHash = password_hash($newPw, PASSWORD_BCRYPT);
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
    <title>Passwort ändern</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="centered-wrapper">
<div class="card">
    <h1>Passwort ändern</h1>

    <?php if ($success): ?>
        <div class="success">Passwort erfolgreich geändert.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= safe($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="change_password.php">
        <label for="current_password">Aktuelles Passwort</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">Neues Passwort</label>
        <input type="password" id="new_password" name="new_password" required minlength="8">

        <label for="confirm_password">Neues Passwort bestätigen</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

        <button type="submit">Passwort ändern</button>
    </form>

    <p class="link"><a href="index.php">Zurück zum Dashboard</a></p>
</div>
</div>
</body>
</html>
