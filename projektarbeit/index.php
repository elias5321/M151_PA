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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard – IT Ausleihesystem</title>
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
        <li class="nav-item"><a class="nav-link active font-weight-bold" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="borrow.php">Ausleihen</a></li>
        <li class="nav-item"><a class="nav-link" href="my_loans.php">Meine Ausleihen</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="devices.php">Geräte</a></li>
        <?php endif; ?>
        <li class="nav-item dropdown ml-auto">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= safe($_SESSION['username']) ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="index.php">Mein Profil</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="change_password.php">Passwort ändern</a>
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
                    <h1 class="h4 mb-1">Willkommen, <?= safe($_SESSION['username']) ?>!</h1>
                    <p class="text-muted mb-4">Rolle: <strong><?= safe($_SESSION['role']) ?></strong></p>

                    <div class="menu">
                        <a href="borrow.php" class="menu-item">Geräte ausleihen</a>
                        <a href="my_loans.php" class="menu-item">Meine Ausleihen</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="devices.php" class="menu-item">Geräte verwalten</a>
                        <?php endif; ?>
                        <a href="change_password.php" class="menu-item">Passwort ändern</a>
                    </div>

                    <form method="post" action="logout.php" class="mt-2">
                        <button type="submit" class="btn btn-danger btn-block">Abmelden</button>
                    </form>
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
