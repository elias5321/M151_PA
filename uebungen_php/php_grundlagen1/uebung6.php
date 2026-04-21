<?php
  $monate = [
      "Januar", "Februar", "März", "April", 
      "Mai", "Juni", "Juli", "August", 
      "September", "Oktober", "November", "Dezember"
  ];

  sort($monate);

  foreach ($monate as $monat) {
      echo $monat . "<br>\n";
  }
?>