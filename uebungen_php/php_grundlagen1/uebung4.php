<?php
  $monate = [
      "Januar", "Februar", "März", "April", 
      "Mai", "Juni", "Juli", "August", 
      "September", "Oktober", "November", "Dezember"
  ];

  $monatsNummer = date("n");

  $aktuellerMonat = $monate[$monatsNummer - 1];

  echo "Wir haben aktuell den Monat: " . $aktuellerMonat;
?>