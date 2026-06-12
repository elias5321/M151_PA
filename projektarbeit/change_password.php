<?php
// C8: Session starten und Zugriff prüfen
session_start();
require_once 'auth.php';
require_once 'db.php';
requireLogin();

/** @var \mysqli $conn */

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = trim($_POST['new_password'] ?? '');
    $confirmPw = trim($_POST['confirm_password'] ?? '');

    // C6: Pflichtfelder + Längengrenzen – spiegeln required/minlength
    if (empty($currentPw))       { $errors[] = 'Aktuelles Passwort darf nicht leer sein.'; }
    if (empty($newPw))           { $errors[] = 'Neues Passwort darf nicht leer sein.'; }
    if (strlen($newPw) < 8)      { $errors[] = 'Neues Passwort muss mindestens 8 Zeichen lang sein.'; }
    if (empty($confirmPw))       { $errors[] = 'Passwort-Bestätigung darf nicht leer sein.'; }
    if (strlen($confirmPw) < 8)  { $errors[] = 'Passwort-Bestätigung muss mindestens 8 Zeichen lang sein.'; }
    if (!empty($newPw) && !empty($confirmPw) && $newPw !== $confirmPw) {
        $errors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
    }
    // C6: Passwort-Komplexität prüfen – spiegelt pattern-Attribut
    if (empty($errors)) {
        $pattern = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[\W_]).{8,})\S$/';
        if (!preg_match($pattern, $newPw)) {
            $errors[] = 'Passwort muss Gross-/Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.';
        }
    }

    if (empty($errors)) {
        $userId = $_SESSION['user_id'];
        // C19: Prepared Statement verhindert SQL-Injection
        $stmt   = mysqli_prepare($conn, 'SELECT password_hash FROM users WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // C11: password_verify() prüft gegen gespeicherten bcrypt-Hash
        if ($hash === null || !password_verify($currentPw, $hash)) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        } else {
            // C15/C11: Neues Passwort mit bcrypt hashen und speichern
            $newHash = password_hash($newPw, PASSWORD_BCRYPT);
            // C19: Prepared Statement verhindert SQL-Injection
            $stmt    = mysqli_prepare($conn, 'UPDATE users SET password_hash = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $newHash, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = true;
        }
    }
}

$pageTitle = 'Passwort ändern';
$activeNav = 'profile';
?>
<?php include 'partials/head.php'; ?>
<?php include 'partials/nav.php'; ?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- C15: Passwort ändern – nur für angemeldete Personen zugänglich (C8) -->
                    <h1 class="h4 mb-4">Passwort ändern</h1>

                    <?php if ($success): ?>
                        <div class="alert alert-success">Passwort erfolgreich geändert.</div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                <?php foreach ($errors as $e): ?>
                                    <!-- C7: safe() schützt vor XSS -->
                                    <li><?= safe($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="change_password.php">
                        <div class="form-group">
                            <label for="current_password">Aktuelles Passwort</label>
                            <!-- C5: required – clientseitige Validierung -->
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Neues Passwort</label>
                            <!-- C5: required + minlength – clientseitige Validierung -->
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Neues Passwort bestätigen</label>
                            <!-- C5: required + minlength – clientseitige Validierung -->
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Passwort ändern</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        <a href="index.php">Zurück zum Dashboard</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
