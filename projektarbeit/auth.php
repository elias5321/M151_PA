<?php
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
