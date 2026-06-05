<?php
// C8: Session starten und Admin-Zugriff prüfen
session_start();
require_once 'auth.php';
require_once 'db.php';
requireAdmin();

/** @var \mysqli $conn */

// C7: Ausgaben werden mit safe() vor XSS geschützt
function safe(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$messages = [];
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── C16: Gerät manuell hinzufügen ────────────────
    if ($action === 'add') {
        $name   = trim($_POST['name']          ?? '');
        $desc   = trim($_POST['description']   ?? '');
        $cat    = trim($_POST['category']      ?? '');
        $serial = trim($_POST['serial_number'] ?? '') ?: null;

        // C6: Serverseitige Validierung – Name darf nicht leer sein
        if (empty($name)) {
            $errors[] = 'Name darf nicht leer sein.';
        } else {
            // C19: Prepared Statement verhindert SQL-Injection
            $stmt = mysqli_prepare($conn,
                'INSERT INTO devices (name, description, category, serial_number, created_by) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssssi', $name, $desc, $cat, $serial, $_SESSION['user_id']);
            if (mysqli_stmt_execute($stmt)) {
                $messages[] = 'Gerät erfolgreich hinzugefügt.';
            } else {
                $errors[] = 'Fehler: Seriennummer bereits vorhanden.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // ── C18: Gerät löschen – nur der Ersteller darf löschen ──
    if ($action === 'delete') {
        $id = (int)($_POST['device_id'] ?? 0);
        if ($id > 0) {
            // C19: Prepared Statement verhindert SQL-Injection
            $stmt = mysqli_prepare($conn, 'SELECT is_available, created_by FROM devices WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $isAvailable, $createdBy);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // C18: Nur der Ersteller darf das Gerät löschen
            if ((int)$createdBy !== (int)$_SESSION['user_id']) {
                $errors[] = 'Nur der Ersteller darf dieses Gerät löschen.';
            } elseif (!$isAvailable) {
                $errors[] = 'Gerät ist aktuell ausgeliehen und kann nicht gelöscht werden.';
            } else {
                // C19: Prepared Statement + created_by-Prüfung verhindert unbefugtes Löschen
                $stmt = mysqli_prepare($conn, 'DELETE FROM devices WHERE id = ? AND created_by = ?');
                mysqli_stmt_bind_param($stmt, 'ii', $id, $_SESSION['user_id']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $messages[] = 'Gerät gelöscht.';
            }
        }
    }

    // ── C16: CSV-Import ───────────────────────────────
    if ($action === 'import_csv') {
        // C6: Datei-Upload serverseitig prüfen
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Bitte eine CSV-Datei auswählen.';
        } else {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if (!$handle) {
                $errors[] = 'CSV-Datei konnte nicht gelesen werden.';
            } else {
                $line     = 0;
                $imported = 0;
                $skipped  = 0;

                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    $line++;
                    if ($line === 1) continue;
                    if (count($row) < 4) { $skipped++; continue; }

                    $csvName   = trim($row[0]);
                    $csvDesc   = trim($row[1]);
                    $csvCat    = trim($row[2]);
                    $csvSerial = trim($row[3]) ?: null;

                    // C6: Leere Namen überspringen
                    if (empty($csvName)) { $skipped++; continue; }

                    // C19: Prepared Statement verhindert SQL-Injection beim Import
                    $stmt = mysqli_prepare($conn,
                        'INSERT IGNORE INTO devices (name, description, category, serial_number, created_by) VALUES (?, ?, ?, ?, ?)');
                    mysqli_stmt_bind_param($stmt, 'ssssi', $csvName, $csvDesc, $csvCat, $csvSerial, $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                    mysqli_stmt_close($stmt);
                }

                fclose($handle);
                $messages[] = "$imported Gerät(e) importiert, $skipped übersprungen (z.B. doppelte Seriennummer).";
            }
        }
    }
}

// C19: Keine Benutzereingabe in der Query – direktes mysqli_query ist sicher
$result  = mysqli_query($conn,
    'SELECT d.id, d.name, d.description, d.category, d.serial_number, d.is_available,
            d.created_by, u.username AS created_by_name
     FROM devices d
     LEFT JOIN users u ON u.id = d.created_by
     ORDER BY d.created_at DESC');
$devices = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Geräte verwalten</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card-wide">

    <!-- C8: Nav nur erreichbar wenn angemeldet (requireAdmin) -->
    <div class="nav-bar">
        <a href="index.php">Dashboard</a>
        <a href="borrow.php">Ausleihen</a>
        <a href="my_loans.php">Meine Ausleihen</a>
        <a href="devices.php" class="active">Geräte verwalten</a>
        <a href="change_password.php">Passwort</a>
        <form method="post" action="logout.php">
            <button class="btn-nav">Abmelden</button>
        </form>
    </div>

    <h1>Geräte verwalten</h1>

    <?php foreach ($messages as $m): ?>
        <!-- C7: safe() schützt vor XSS in Erfolgsmeldungen -->
        <div class="success"><?= safe($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <!-- C7: safe() schützt vor XSS in Fehlermeldungen -->
        <div class="errors"><?= safe($e) ?></div>
    <?php endforeach; ?>

    <!-- C16: Gerät manuell erfassen -->
    <div class="section">
        <h2>Gerät manuell hinzufügen</h2>
        <form method="post" action="devices.php">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div>
                    <!-- C5: required + maxlength – clientseitige Validierung -->
                    <label>Name *</label>
                    <input type="text" name="name" required maxlength="100">
                </div>
                <div>
                    <!-- C5: maxlength – clientseitige Validierung -->
                    <label>Kategorie</label>
                    <input type="text" name="category" maxlength="50">
                </div>
                <div>
                    <!-- C5: maxlength – clientseitige Validierung -->
                    <label>Seriennummer</label>
                    <input type="text" name="serial_number" maxlength="100">
                </div>
                <div>
                    <label>Beschreibung</label>
                    <input type="text" name="description">
                </div>
            </div>
            <button type="submit" class="btn-auto">Gerät hinzufügen</button>
        </form>
    </div>

    <!-- C16: Geräte via CSV erfassen -->
    <div class="section">
        <h2>Import via CSV</h2>
        <p class="info-text">
            Format (Semikolon-getrennt, erste Zeile = Kopfzeile):<br>
            <code>name;description;category;serial_number</code><br>
            Vorlage: <a href="geraete_vorlage.csv" download>geraete_vorlage.csv</a>
        </p>
        <!-- C5: accept=".csv" – clientseitige Dateiprüfung -->
        <form method="post" action="devices.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import_csv">
            <input type="file" name="csv_file" accept=".csv" style="width:auto; margin-bottom:12px;">
            <button type="submit" class="btn-auto">Importieren</button>
        </form>
    </div>

    <!-- Geräteliste -->
    <div class="section">
        <h2>Alle Geräte (<?= count($devices) ?>)</h2>
        <?php if (empty($devices)): ?>
            <p>Noch keine Geräte erfasst.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Kategorie</th>
                    <th>Seriennummer</th>
                    <th>Beschreibung</th>
                    <th>Status</th>
                    <th>Erfasst von</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $d): ?>
                <tr>
                    <!-- C7: safe() schützt alle Datenbankwerte vor XSS -->
                    <td><?= safe($d['name']) ?></td>
                    <td><?= safe($d['category'] ?? '') ?></td>
                    <td><?= safe($d['serial_number'] ?? '') ?></td>
                    <td><?= safe($d['description'] ?? '') ?></td>
                    <td>
                        <?php if ($d['is_available']): ?>
                            <span class="badge-ok">Verfügbar</span>
                        <?php else: ?>
                            <span class="badge-busy">Ausgeliehen</span>
                        <?php endif; ?>
                    </td>
                    <td><?= safe($d['created_by_name'] ?? '') ?></td>
                    <td>
                        <!-- C18: Löschen-Button nur anzeigen wenn eigener Datensatz -->
                        <?php if ((int)$d['created_by'] === (int)$_SESSION['user_id']): ?>
                        <form method="post" action="devices.php"
                              onsubmit="return confirm('Gerät «<?= safe($d['name']) ?>» wirklich löschen?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="device_id" value="<?= (int)$d['id'] ?>">
                            <button type="submit" class="btn-sm btn-danger">Löschen</button>
                        </form>
                        <?php else: ?>
                            <span style="color:#aaa; font-size:0.8rem;">Nicht dein Gerät</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
