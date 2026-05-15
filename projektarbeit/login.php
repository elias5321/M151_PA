<?php

$errors         = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['email'], $_POST['password'])) {
        $errors[] = 'Ungültige Anfrage: Pflichtfelder fehlen.';
    } else {

        $rawEmail    = trim($_POST['email']);
        $rawPassword = trim($_POST['password']);

        if (empty($rawEmail))    { $errors[] = 'E-Mail-Adresse darf nicht leer sein.'; }
        if (empty($rawPassword)) { $errors[] = 'Passwort darf nicht leer sein.'; }

        if (empty($errors)) {

            if (strlen($rawEmail) > 100) {
                $errors[] = 'E-Mail-Adresse darf maximal 100 Zeichen lang sein.';
            }

            if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-Mail-Adresse ist nicht gültig.';
            }

            if (strlen($rawPassword) < 8) {
                $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
            }

            $pattern = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$/';
            if (!preg_match($pattern, $rawPassword)) {
                $errors[] = 'Passwort muss Gross-/Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.';
            }
        }

    }
}

function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$safeEmail = isset($rawEmail) ? safe($rawEmail) : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h1>Login</h1>


<form method="post" action="login.php">

    <label for="email">E-Mail-Adresse</label><br>
    
    <input
        type="email"
        id="email"
        name="email"
        required
        maxlength="100"
        value="<?= $safeEmail ?>"
    ><br><br>

    <label for="password">Passwort</label><br>
    
    <input
        type="password"
        id="password"
        name="password"
        required
        minlength="8"
        pattern="^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$"
        title="Mindestens 8 Zeichen, Gross- und Kleinbuchstaben, Zahlen und Sonderzeichen"
    ><br><br>

    <button type="submit">Anmelden</button>
</form>

</body>
</html>
