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

    <div class="row justify-content-center mt-4">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">Static Site Generators (SSGs)</h2>
                    <p class="text-muted mb-4">Bei einer statischen Website macht automatisch generierter Code nicht nur Sinn – er ist heutzutage der absolute Standard für professionelle Entwickler.</p>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h3 class="h6 font-weight-bold mb-2">Das Problem ohne Generator (Die Handarbeit)</h3>
                            <p class="mb-0">
                                Stell dir vor, du baust eine Website mit 5 Unterseiten (Home, Über uns, Leistungen, Blog, Kontakt).
                                Jede Seite hat denselben Header (Menü) und denselben Footer.
                                Wenn du einen Link im Menü änderst, musst du alle 5 HTML-Dateien händisch öffnen und den Link überall einzeln ändern.
                                Das ist extrem fehleranfällig.
                            </p>
                        </div>
                    </div>

                    <div class="card border-primary mb-0">
                        <div class="card-body">
                            <h3 class="h6 font-weight-bold text-primary mb-2">Die Lösung mit Generator (Auto-Generated)</h3>
                            <p>
                                Du schreibst den Header nur <strong>ein einziges Mal</strong> als Baustein (Component).
                                Den eigentlichen Inhalt der Seiten schreibst du oft in ganz einfachem Text (Markdown).
                            </p>
                            <p class="mb-0">
                                Wenn du fertig bist, drückst du auf <em>„Build" (Generieren)</em>.
                                Das Tool nimmt deine Bausteine, fügt sie zusammen und spuckt automatisch generierte HTML- und CSS-Dateien aus –
                                du bekommst die Vorteile eines dynamischen Systems beim Schreiben, aber der Besucher bekommt eine blitzschnelle, reine HTML-Seite.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
