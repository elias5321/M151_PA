<?php

$errors         = [];
$successMessage = '';

// C6: Wurde das Formular per POST gesendet?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // C6: Sind alle Felder vorhanden?
    if (!isset($_POST['email'], $_POST['password'], $_POST['password_confirm'])) {
        $errors[] = 'Ungültige Anfrage: Pflichtfelder fehlen.';
    } else {

        // C7: trim() vor jeder Validierung
        $rawEmail           = trim($_POST['email']);
        $rawPassword        = trim($_POST['password']);
        $rawPasswordConfirm = trim($_POST['password_confirm']);

        // C6: empty() – Felder nicht leer?
        if (empty($rawEmail))           { $errors[] = 'E-Mail-Adresse darf nicht leer sein.'; }
        if (empty($rawPassword))        { $errors[] = 'Passwort darf nicht leer sein.'; }
        if (empty($rawPasswordConfirm)) { $errors[] = 'Passwort-Bestätigung darf nicht leer sein.'; }

        if (empty($errors)) {

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

        if (empty($errors)) {
            $successMessage = 'Registrierung erfolgreich!';
        }
    }
}

// C7: htmlspecialchars() auf alle Benutzereingaben vor der Ausgabe
function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// C7: E-Mail sicher für Wiederanzeige vorbereiten – niemals $_POST direkt ausgeben
$safeEmail = isset($rawEmail) ? safe($rawEmail) : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Registrierung</title>
</head>
<body>

<h1>Registrierung</h1>

<?php if (!empty($errors)): ?>
    <ul>
        <?php foreach ($errors as $error): ?>
            <!-- C7: safe() schützt vor XSS in Fehlermeldungen -->
            <li><?= safe($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($successMessage !== ''): ?>
    <p><?= safe($successMessage) ?></p>
<?php endif; ?>

<form method="post" action="register.php">

    <label for="first_name">Vorname</label><br>
    <!--
        C5: type="email" + required + maxlength – clientseitige Validierung
        C7: value wird durch safe() geschützt ausgegeben
    -->
    <input
        type="text"
        id="first_name"
        name="first_name"
        required
        maxlength="100"
    ><br><br>

    <label for="last_name">Nachname</label><br>
    <!--
        C5: type="email" + required + maxlength – clientseitige Validierung
        C7: value wird durch safe() geschützt ausgegeben
    -->
    <input
        type="text"
        id="last_name"
        name="last_name"
        required
        maxlength="100"
    ><br><br>

    <label for="email">E-Mail-Adresse</label><br>
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
    ><br><br>

    <label for="password">Passwort</label><br>
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
    ><br><br>

    <label for="password_confirm">Passwort bestätigen</label><br>
    <!-- C5: required – Feld darf nicht leer sein -->
    <input
        type="password"
        id="password_confirm"
        name="password_confirm"
        required
        minlength="8"
    ><br><br>

    <button type="submit">Registrieren</button>
</form>

<p><a href="login.php">Bereits registriert? Zum Login</a></p>

</body>
</html>
