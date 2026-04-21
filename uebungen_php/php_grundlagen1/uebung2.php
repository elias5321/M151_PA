<?php
  // Kommentar zur Datentyp-Speicherung:
  // In PHP werden Strings (Zeichenketten) in Anführungszeichen gesetzt 
  // (entweder ' ' oder " "). Zahlen hingegen werden ohne Anführungszeichen 
  // geschrieben, damit PHP sie direkt als Integer (Ganzzahl) erkennt 
  // und mathematische Operationen damit zulässt.

  $vorname = "Elias";        
  $nachname = "Düggeli";  
  $jahrgang = 2008;          

  // Mit dem . können wir die Variablen (Stirings) zu einem Satz zusammenfügen (Konkatenation).
  echo "Mein Name ist " . $vorname . " " . $nachname . " und ich bin Jahrgang " . $jahrgang;
?>