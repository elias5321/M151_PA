<?php
session_start();
require_once 'auth.php';
requireLogin();

function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="centered-wrapper">
<div class="card">
    <h1>Willkommen, <?= safe($_SESSION['username']) ?>!</h1>
    <p class="info">Rolle: <strong><?= safe($_SESSION['role']) ?></strong></p>

    <div class="menu">
        <a href="borrow.php" class="menu-item">Geräte ausleihen</a>
        <a href="my_loans.php" class="menu-item">Meine Ausleihen</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="devices.php" class="menu-item">Geräte verwalten</a>
        <?php endif; ?>
        <a href="change_password.php" class="menu-item">Passwort ändern</a>
    </div>

    <form method="post" action="logout.php">
        <button type="submit" class="btn-danger">Abmelden</button>
    </form>
</div>
</div>
</body>
</html>
