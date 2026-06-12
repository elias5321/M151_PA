<?php
session_start();
require_once 'auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
?>
<?php include 'partials/head.php'; ?>
<?php include 'partials/nav.php'; ?>

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

<?php include 'partials/footer.php'; ?>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
