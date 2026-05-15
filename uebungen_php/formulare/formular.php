<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular-Daten</title>
</head>

<body>

    <h1>Formulardaten</h1>
    <!-- startet den PHP Codeblock -->
    <?php
    // Ausgabe des HMTL-Elementes für vorfomatierten Text
    echo "<pre>";
    /**
     * alle vom Benutzer eingegebenen Formularinformationen 
     * werden in PHP in einem superglobalen assoziativen Array $_POST -> POST, $_GET -> GET, $_REQUEST -> POST und GET
     * gespeichert und können mit print_r in einer für uns Menschen
     * lesbaren Form angezeigt werden
     */ 
    print_r($_REQUEST);
    echo "</pre>";
    ?>

</body>

</html>