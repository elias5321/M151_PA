<?php
session_start();
require_once 'auth.php';
require_once 'db.php';
requireLogin();

/** @var \mysqli $conn */

$profileErrors  = [];
$profileSuccess = false;
$passwordErrors  = [];
$passwordSuccess = false;

// ── Profil aktualisieren ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $rawUsername = trim($_POST['username'] ?? '');
    $rawEmail    = trim($_POST['email']    ?? '');

    // C6: Pflichtfelder + Längengrenzen – spiegeln required/maxlength
    if (empty($rawUsername))          { $profileErrors[] = 'Benutzername darf nicht leer sein.'; }
    if (strlen($rawUsername) > 50)    { $profileErrors[] = 'Benutzername darf maximal 50 Zeichen lang sein.'; }
    if (empty($rawEmail))             { $profileErrors[] = 'E-Mail-Adresse darf nicht leer sein.'; }
    if (strlen($rawEmail) > 100)      { $profileErrors[] = 'E-Mail-Adresse darf maximal 100 Zeichen lang sein.'; }
    if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
        $profileErrors[] = 'E-Mail-Adresse ist nicht gültig.';
    }

    if (empty($profileErrors)) {
        $userId = $_SESSION['user_id'];

        // C19: Eindeutigkeit Benutzername prüfen (eigenen Eintrag ausschliessen)
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? AND id != ?');
        mysqli_stmt_bind_param($stmt, 'si', $rawUsername, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $profileErrors[] = 'Dieser Benutzername ist bereits vergeben.';
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($profileErrors)) {
        $userId = $_SESSION['user_id'];

        // C19: Eindeutigkeit E-Mail prüfen (eigenen Eintrag ausschliessen)
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? AND id != ?');
        mysqli_stmt_bind_param($stmt, 'si', $rawEmail, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $profileErrors[] = 'Diese E-Mail-Adresse ist bereits registriert.';
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($profileErrors)) {
        $userId = $_SESSION['user_id'];

        // C19: Prepared Statement – Profil speichern
        $stmt = mysqli_prepare($conn, 'UPDATE users SET username = ?, email = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $rawUsername, $rawEmail, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // C14: Session-Username aktualisieren
        $_SESSION['username'] = $rawUsername;
        $profileSuccess = true;
    }
}

// ── Passwort ändern ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = trim($_POST['new_password']     ?? '');
    $confirmPw = trim($_POST['confirm_password'] ?? '');

    // C6: Pflichtfelder + Längengrenzen – spiegeln required/minlength
    if (empty($currentPw))       { $passwordErrors[] = 'Aktuelles Passwort darf nicht leer sein.'; }
    if (empty($newPw))           { $passwordErrors[] = 'Neues Passwort darf nicht leer sein.'; }
    if (strlen($newPw) < 8)      { $passwordErrors[] = 'Neues Passwort muss mindestens 8 Zeichen lang sein.'; }
    if (empty($confirmPw))       { $passwordErrors[] = 'Passwort-Bestätigung darf nicht leer sein.'; }
    if (strlen($confirmPw) < 8)  { $passwordErrors[] = 'Passwort-Bestätigung muss mindestens 8 Zeichen lang sein.'; }
    if (!empty($newPw) && !empty($confirmPw) && $newPw !== $confirmPw) {
        $passwordErrors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
    }

    // C6: Passwort-Komplexität – spiegelt pattern-Attribut
    if (empty($passwordErrors)) {
        $pattern = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$/';
        if (!preg_match($pattern, $newPw)) {
            $passwordErrors[] = 'Passwort muss Gross-/Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.';
        }
    }

    if (empty($passwordErrors)) {
        $userId = $_SESSION['user_id'];

        // C19: Prepared Statement – aktuellen Hash laden
        $stmt = mysqli_prepare($conn, 'SELECT password_hash FROM users WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // C11: password_verify() prüft gegen bcrypt-Hash
        if ($hash === null || !password_verify($currentPw, $hash)) {
            $passwordErrors[] = 'Aktuelles Passwort ist falsch.';
        } else {
            // C15/C11: Neues Passwort mit bcrypt hashen
            $newHash = password_hash($newPw, PASSWORD_BCRYPT);
            // C19: Prepared Statement – Passwort speichern
            $stmt = mysqli_prepare($conn, 'UPDATE users SET password_hash = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $newHash, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $passwordSuccess = true;
        }
    }
}

// ── Aktuelle Benutzerdaten laden ──────────────────────────────────────────────
$userId = $_SESSION['user_id'];
$stmt   = mysqli_prepare($conn, 'SELECT username, email FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $currentUsername, $currentEmail);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$pageTitle = 'Mein Profil';
$activeNav = 'profile';
?>
<?php include 'partials/head.php'; ?>
<?php include 'partials/nav.php'; ?>

<main class="container mt-5">

    <div class="row justify-content-center mb-4">
        <div class="col-md-10 col-lg-8">
            <h1 class="h3 mb-0">Mein Profil</h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="row">

                <!-- Profil bearbeiten -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-4">Profil bearbeiten</h2>

                            <?php if ($profileSuccess): ?>
                                <div class="alert alert-success">Profil erfolgreich gespeichert.</div>
                            <?php endif; ?>
                            <?php if (!empty($profileErrors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        <?php foreach ($profileErrors as $e): ?>
                                            <li><?= safe($e) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="profile.php">
                                <input type="hidden" name="action" value="update_profile">

                                <div class="form-group">
                                    <label for="username">Benutzername</label>
                                    <!-- C5: required + maxlength -->
                                    <input
                                        type="text"
                                        id="username"
                                        name="username"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?= safe($profileSuccess ? $_SESSION['username'] : $currentUsername) ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="email">E-Mail-Adresse</label>
                                    <!-- C5: type="email" + required + maxlength -->
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="form-control"
                                        required
                                        maxlength="100"
                                        value="<?= safe($currentEmail) ?>"
                                    >
                                </div>

                                <div class="form-group mb-0">
                                    <label>Rolle</label>
                                    <input type="text" class="form-control" value="<?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Benutzer' ?>" disabled>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mt-4">Speichern</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Passwort ändern -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-4">Passwort ändern</h2>

                            <?php if ($passwordSuccess): ?>
                                <div class="alert alert-success">Passwort erfolgreich geändert.</div>
                            <?php endif; ?>
                            <?php if (!empty($passwordErrors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        <?php foreach ($passwordErrors as $e): ?>
                                            <li><?= safe($e) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="profile.php">
                                <input type="hidden" name="action" value="change_password">

                                <div class="form-group">
                                    <label for="current_password">Aktuelles Passwort</label>
                                    <!-- C5: required -->
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">Neues Passwort</label>
                                    <!-- C5: required + minlength -->
                                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Neues Passwort bestätigen</label>
                                    <!-- C5: required + minlength -->
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mt-1">Passwort ändern</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <p class="mt-1">
                <a href="index.php" class="text-muted small">Zurück zum Dashboard</a>
            </p>
        </div>
    </div>

</main>

<?php include 'partials/footer.php'; ?>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
