<?php
session_start();

require_once 'db.php';
/** @var \mysqli $conn */

$errors = [];

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
        }

        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, 'SELECT id, username, password_hash, role FROM users WHERE email = ?');
            mysqli_stmt_bind_param($stmt, 's', $rawEmail);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $userId, $username, $passwordHash, $role);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($userId && password_verify($rawPassword, $passwordHash)) {
                session_regenerate_id(true);
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['role']     = $role;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'E-Mail-Adresse oder Passwort ist falsch.';
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

<p><a href="register.php">Noch kein Konto? Hier registrieren</a></p>

</body>
</html>
