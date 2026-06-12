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

    <div class="row justify-content-center mb-4">
        <div class="col-md-10 col-lg-8">
            <h1 class="h3 mb-1">Willkommen, <?= safe($_SESSION['username']) ?>!</h1>
            <p class="text-muted mb-0">
                <span class="dashboard-role-badge">
                    <?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Benutzer' ?>
                </span>
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="row">

                <div class="col-sm-6 mb-3">
                    <a href="borrow.php" class="dashboard-card">
                        <div class="dashboard-card__title">Geräte ausleihen</div>
                        <div class="dashboard-card__desc">Verfügbare Geräte ansehen und ausleihen</div>
                    </a>
                </div>

                <div class="col-sm-6 mb-3">
                    <a href="my_loans.php" class="dashboard-card">
                        <div class="dashboard-card__title">Meine Ausleihen</div>
                        <div class="dashboard-card__desc">Aktive Ausleihen verwalten und zurückgeben</div>
                    </a>
                </div>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="col-sm-6 mb-3">
                    <a href="devices.php" class="dashboard-card dashboard-card--admin">
                        <div class="dashboard-card__title">Geräte verwalten</div>
                        <div class="dashboard-card__desc">Geräte hinzufügen, importieren und löschen</div>
                    </a>
                </div>
                <?php endif; ?>

                <div class="col-sm-6 mb-3">
                    <a href="profile.php" class="dashboard-card">
                        <div class="dashboard-card__title">Mein Profil</div>
                        <div class="dashboard-card__desc">Benutzername, E-Mail und Passwort ändern</div>
                    </a>
                </div>

            </div>

            <form method="post" action="logout.php" class="mt-1">
                <button type="submit" class="btn btn-outline-danger">Abmelden</button>
            </form>
        </div>
    </div>

</main>

<?php include 'partials/footer.php'; ?>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
