<?php
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava v aplikacijo</title>
</head>
<body>
<section id="section">
    <h1>Prijava</h1>
    <p id="prvi">
        Za uporabo aplikacije je potrebna prijava.
    </p>
    <form action="" method="POST">
        E-mail: <input type="text" name="naziv" value="" required class="vnos" placeholder="Vnesi e-pošto"><br>
        Password: <input type="password" name="tip" value="" required class="vnos" placeholder="Vnesi geslo"><br>
        <div>
            <input type="submit" name="prijava" value="Prijava" id="posljigumb">
            <button type="button" id="registergumb">Registracija</button>
        </div>
    </form>
</section>
</body>
</html>

