<?php
session_start();
// C9: Session-Variablen löschen und Session zerstören – Benutzer wird abgemeldet
session_unset();
session_destroy();
header('Location: login.php');
exit;
