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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">
    <h1>Willkommen, <?= safe($_SESSION['username']) ?>!</h1>

    <p class="info">Du bist eingeloggt als <strong><?= safe($_SESSION['username']) ?></strong>.</p>

    <form method="post" action="logout.php">
        <button type="submit" class="btn-danger">Abmelden</button>
    </form>
</div>

</body>
</html>
