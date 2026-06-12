<?php
// C8: Session starten und Login prüfen
session_start();
require_once 'auth.php';
require_once 'db.php';
requireLogin();

/** @var \mysqli $conn */

$messages = [];
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceId = (int)($_POST['device_id'] ?? 0);
    $dueDate  = trim($_POST['due_date'] ?? '');

    // C6: Serverseitige Validierung der Eingaben
    if ($deviceId <= 0) {
        $errors[] = 'Ungültiges Gerät.';
    } elseif (empty($dueDate)) {
        $errors[] = 'Bitte ein Rückgabedatum angeben.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        // C6: Format YYYY-MM-DD prüfen – spiegelt type="date"
        $errors[] = 'Ungültiges Datumsformat.';
    } elseif ($dueDate <= date('Y-m-d')) {
        // C6: Datum muss in der Zukunft liegen – spiegelt min-Attribut
        $errors[] = 'Rückgabedatum muss in der Zukunft liegen.';
    } else {
        // C19: Prepared Statement – prüft ob Gerät noch verfügbar ist
        $stmt = mysqli_prepare($conn, 'SELECT is_available FROM devices WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $deviceId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $isAvailable);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // C6: Verfügbarkeit serverseitig prüfen (nicht nur clientseitig vertrauen)
        if (!$isAvailable) {
            $errors[] = 'Dieses Gerät ist bereits ausgeliehen.';
        } else {
            $userId = $_SESSION['user_id'];

            // C16: Ausleihe in DB erfassen – C19: Prepared Statement
            $stmt = mysqli_prepare($conn,
                'INSERT INTO loans (device_id, user_id, due_date) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iis', $deviceId, $userId, $dueDate);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // C19: Prepared Statement – Gerät als ausgeliehen markieren
            $stmt = mysqli_prepare($conn, 'UPDATE devices SET is_available = 0 WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $deviceId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $messages[] = 'Gerät erfolgreich ausgeliehen.';
        }
    }
}

// C19: Keine Benutzereingabe – direktes mysqli_query ist sicher
$result  = mysqli_query($conn,
    'SELECT id, name, category, serial_number, description FROM devices WHERE is_available = 1 ORDER BY name');
$devices = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

$minDate = date('Y-m-d', strtotime('+1 day'));

$pageTitle = 'Geräte ausleihen';
$activeNav = 'borrow';
?>
<?php include 'partials/head.php'; ?>
<?php include 'partials/nav.php'; ?>

<main class="container mt-4">

    <h1 class="h3 mb-4">Geräte ausleihen</h1>

    <?php foreach ($messages as $m): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="alert alert-success"><?= safe($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <!-- C7: safe() schützt vor XSS -->
        <div class="alert alert-danger"><?= safe($e) ?></div>
    <?php endforeach; ?>

    <?php if (empty($devices)): ?>
        <p>Aktuell sind keine Geräte verfügbar.</p>
    <?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>Kategorie</th>
                <th>Seriennummer</th>
                <th>Beschreibung</th>
                <th>Rückgabe bis</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devices as $d): ?>
            <tr>
                <!-- C7: safe() schützt alle DB-Ausgaben vor XSS -->
                <td><?= safe($d['name']) ?></td>
                <td><?= safe($d['category'] ?? '') ?></td>
                <td><?= safe($d['serial_number'] ?? '') ?></td>
                <td><?= safe($d['description'] ?? '') ?></td>
                <td>
                    <!-- C16: Ausleihe erfassen -->
                    <form method="post" action="borrow.php" class="row-form">
                        <input type="hidden" name="device_id" value="<?= (int)$d['id'] ?>">
                        <!-- C5: type="date" + required + min – clientseitige Validierung -->
                        <input type="date" name="due_date" min="<?= safe($minDate) ?>" required class="form-control form-control-sm" style="width:auto">
                        <button type="submit" class="btn btn-primary btn-sm">Ausleihen</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</main>

<?php include 'partials/footer.php'; ?>
<?php include 'partials/scripts.php'; ?>
</body>
</html>
