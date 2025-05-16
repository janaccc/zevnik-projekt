<?php
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knjižna polica slovarjev</title>
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
        <input type="submit" name="prijava" value="Prijava" id="posljigumb">
        <input type="submit" name="register" value="Nov uporabnik" id="registergumb">
    </form>

</body>
</html>

