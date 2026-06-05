<?php
require_once 'db.php';
/** @var \mysqli $conn */

$errors         = [];
$successMessage = '';

// C6: Wurde das Formular per POST gesendet?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // C6: Sind alle Felder vorhanden?
    if (!isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_confirm'])) {
        $errors[] = 'Ungültige Anfrage: Pflichtfelder fehlen.';
    } else {

        // C7: trim() vor jeder Validierung
        $rawUsername        = trim($_POST['username']);
        $rawEmail           = trim($_POST['email']);
        $rawPassword        = trim($_POST['password']);
        $rawPasswordConfirm = trim($_POST['password_confirm']);

        // C6: empty() – Felder nicht leer?
        if (empty($rawUsername))        { $errors[] = 'Benutzername darf nicht leer sein.'; }
        if (empty($rawEmail))           { $errors[] = 'E-Mail-Adresse darf nicht leer sein.'; }
        if (empty($rawPassword))        { $errors[] = 'Passwort darf nicht leer sein.'; }
        if (empty($rawPasswordConfirm)) { $errors[] = 'Passwort-Bestätigung darf nicht leer sein.'; }

        if (empty($errors)) {

            // C6: strlen() – Maximallänge Benutzername
            if (strlen($rawUsername) > 50) {
                $errors[] = 'Benutzername darf maximal 50 Zeichen lang sein.';
            }

            // C6: strlen() – Maximallänge E-Mail
            if (strlen($rawEmail) > 100) {
                $errors[] = 'E-Mail-Adresse darf maximal 100 Zeichen lang sein.';
            }

            // C6: filter_var() – Gültige E-Mail-Adresse?
            if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-Mail-Adresse ist nicht gültig.';
            }

            // C6: strlen() – Mindestlänge Passwort
            if (strlen($rawPassword) < 8) {
                $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
            }

            // C6: preg_match() – Passwort-Komplexität
            $pattern = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$/';
            if (!preg_match($pattern, $rawPassword)) {
                $errors[] = 'Passwort muss Gross-/Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.';
            }

            // C6: Passwörter stimmen überein?
            if ($rawPassword !== $rawPasswordConfirm) {
                $errors[] = 'Passwörter stimmen nicht überein.';
            }
        }

        // C19: Prepared Statement – prüft ob E-Mail bereits existiert
        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
            mysqli_stmt_bind_param($stmt, 's', $rawEmail);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = 'Diese E-Mail-Adresse ist bereits registriert.';
            }
            mysqli_stmt_close($stmt);
        }

        // C19: Prepared Statement – prüft ob Benutzername bereits existiert
        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ?');
            mysqli_stmt_bind_param($stmt, 's', $rawUsername);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = 'Dieser Benutzername ist bereits vergeben.';
            }
            mysqli_stmt_close($stmt);
        }

        // C13/C16: Benutzer erfassen – C11: Passwort wird mit bcrypt gehasht und gesaltet
        if (empty($errors)) {
            $passwordHash = password_hash($rawPassword, PASSWORD_BCRYPT);
            $role         = 'user';

            // C19: Prepared Statement verhindert SQL-Injection beim INSERT
            $stmt = mysqli_prepare($conn, 'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssss', $rawUsername, $rawEmail, $passwordHash, $role);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Registrierung fehlgeschlagen. Bitte versuche es erneut.';
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// C7: htmlspecialchars() auf alle Benutzereingaben vor der Ausgabe
function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// C7: Werte sicher für Wiederanzeige vorbereiten – niemals $_POST direkt ausgeben
$safeUsername = isset($rawUsername) ? safe($rawUsername) : '';
$safeEmail    = isset($rawEmail)    ? safe($rawEmail)    : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Registrierung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="centered-wrapper">
<div class="card">
    <h1>Registrierung</h1>

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

    <!-- C13: Registrierungsformular – erfasst Benutzername, E-Mail und Passwort -->
    <form method="post" action="register.php">

        <label for="username">Benutzername</label>
        <!-- C5: required + maxlength – clientseitige Validierung -->
        <input
            type="text"
            id="username"
            name="username"
            required
            maxlength="50"
            value="<?= $safeUsername ?>"
        >

        <label for="email">E-Mail-Adresse</label>
        <!--
            C5: type="email" + required + maxlength – clientseitige Validierung
            C7: value wird durch safe() geschützt ausgegeben
        -->
        <input
            type="email"
            id="email"
            name="email"
            required
            maxlength="100"
            value="<?= $safeEmail ?>"
        >

        <label for="password">Passwort</label>
        <!--
            C5: minlength + pattern erzwingen Komplexität bereits im Browser
            C7: Passwort wird NICHT als value zurückgegeben
        -->
        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="8"
            pattern="^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$"
            title="Mindestens 8 Zeichen, Gross- und Kleinbuchstaben, Zahlen und Sonderzeichen"
        >

        <label for="password_confirm">Passwort bestätigen</label>
        <!-- C5: required + minlength – clientseitige Validierung -->
        <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            required
            minlength="8"
        >

        <button type="submit">Registrieren</button>
    </form>

    <p class="link"><a href="login.php">Bereits registriert? Zum Login</a></p>
</div>
</div>

</body>
</html>
