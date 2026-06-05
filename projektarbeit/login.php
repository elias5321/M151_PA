<?php
// C8: Session starten – wird für Session Handling benötigt
session_start();

require_once 'db.php';
/** @var \mysqli $conn */

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // C6: Pflichtfelder vorhanden?
    if (!isset($_POST['login'], $_POST['password'])) {
        $errors[] = 'Ungültige Anfrage: Pflichtfelder fehlen.';
    } else {

        $rawLogin    = trim($_POST['login']);
        $rawPassword = trim($_POST['password']);

        // C6: Felder nicht leer?
        if (empty($rawLogin))    { $errors[] = 'Benutzername oder E-Mail darf nicht leer sein.'; }
        if (empty($rawPassword)) { $errors[] = 'Passwort darf nicht leer sein.'; }

        if (empty($errors)) {
            // C19: Prepared Statement verhindert SQL-Injection
            $stmt = mysqli_prepare($conn, 'SELECT id, username, password_hash, role FROM users WHERE email = ? OR username = ?');
            mysqli_stmt_bind_param($stmt, 'ss', $rawLogin, $rawLogin);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $userId, $username, $passwordHash, $role);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // C11: password_verify() prüft gegen bcrypt-Hash
            if ($userId && password_verify($rawPassword, $passwordHash)) {
                // C10: session_regenerate_id() verhindert Session-Fixation
                session_regenerate_id(true);
                // C8/C14: Session-Variablen setzen – Benutzer ist nun angemeldet
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['role']     = $role;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Benutzername/E-Mail oder Passwort ist falsch.';
            }
        }
    }
}

// C7: htmlspecialchars() verhindert XSS bei der Ausgabe
function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$safeLogin = isset($rawLogin) ? safe($rawLogin) : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="centered-wrapper">
<div class="card">
    <h1>Login</h1>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <!-- C7: safe() schützt vor XSS in Fehlermeldungen -->
                    <li><?= safe($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">

        <label for="login">Benutzername oder E-Mail</label>
        <!-- C5: required + maxlength – clientseitige Validierung -->
        <input
            type="text"
            id="login"
            name="login"
            required
            maxlength="100"
            value="<?= $safeLogin ?>"
        >

        <label for="password">Passwort</label>
        <!-- C5: required + minlength – clientseitige Validierung -->
        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="8"
        >

        <button type="submit">Anmelden</button>
    </form>

    <p class="link"><a href="register.php">Noch kein Konto? Hier registrieren</a></p>
</div>
</div>

</body>
</html>
