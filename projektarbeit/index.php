<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Startseite</title>
</head>
<body>

<h1>Willkommen, <?= safe($_SESSION['username']) ?>!</h1>

<p>Du bist eingeloggt als <strong><?= safe($_SESSION['role']) ?></strong>.</p>

<form method="post" action="logout.php">
    <button type="submit">Abmelden</button>
</form>

</body>
</html>
