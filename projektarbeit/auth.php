<?php
// C7: htmlspecialchars() verhindert XSS bei jeder Ausgabe von Benutzerdaten
function safe(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// C8: Zugriffskontrolle – nicht angemeldete Personen werden weitergeleitet
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// C8: Zugriffskontrolle – nur Admins erhalten Zugriff, alle anderen werden weitergeleitet
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}
