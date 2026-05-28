<?php
$conn = mysqli_connect('localhost', 'm151_user', 'ausleihSecure!', 'ausleihesystem');

if (!$conn) {
    die('Datenbankverbindung fehlgeschlagen: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
