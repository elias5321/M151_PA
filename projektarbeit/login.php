<?php
// C8: Session starten – wird für Session Handling benötigt
session_start();

require_once 'auth.php';
require_once 'db.php';
/** @var \mysqli $conn */

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // C6: Pflichtfelder vorhanden?
    if (!isset($_POST['login'], $_POST['password'])) {
        $errors[] = 'Ungültige Anfrage: Pflichtfelder fehlen.';
    } else {

        $rawLogin    = trim($_POST['login']);
        $rawPassword = trim($_POST['password']);

        // C6: Felder nicht leer?
        if (empty($rawLogin))    { $errors[] = 'Benutzername oder E-Mail darf nicht leer sein.'; }
        if (empty($rawPassword)) { $errors[] = 'Passwort darf nicht leer sein.'; }

        if (empty($errors)) {
            // C19: Prepared Statement verhindert SQL-Injection
            $stmt = mysqli_prepare($conn, 'SELECT id, username, password_hash, role FROM users WHERE email = ? OR username = ?');
            mysqli_stmt_bind_param($stmt, 'ss', $rawLogin, $rawLogin);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $userId, $username, $passwordHash, $role);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // C11: password_verify() prüft gegen bcrypt-Hash
            if ($userId && $passwordHash !== null && password_verify($rawPassword, $passwordHash)) {
                // C10: session_regenerate_id() verhindert Session-Fixation
                session_regenerate_id(true);
                // C8/C14: Session-Variablen setzen – Benutzer ist nun angemeldet
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['role']     = $role;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Benutzername/E-Mail oder Passwort ist falsch.';
            }
        }
    }
}

$safeLogin = isset($rawLogin) ? safe($rawLogin) : '';

$pageTitle = 'Login';
?>
<?php include 'partials/head.php'; ?>
<?php include 'partials/nav_guest.php'; ?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">Login</h1>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                <?php foreach ($errors as $error): ?>
                                    <!-- C7: safe() schützt vor XSS in Fehlermeldungen -->
                                    <li><?= safe($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php">

                        <div class="form-group">
                            <label for="login">Benutzername oder E-Mail</label>
                            <!-- C5: required + maxlength – clientseitige Validierung -->
                            <input
                                type="text"
                                id="login"
                                name="login"
                                class="form-control"
                                required
                                maxlength="100"
                                value="<?= $safeLogin ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Passwort</label>
                            <!-- C5: required + minlength – clientseitige Validierung -->
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required
                                minlength="8"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Anmelden</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        <a href="register.php">Noch kein Konto? Hier registrieren</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
</body>
</html>
