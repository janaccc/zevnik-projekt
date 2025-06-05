<?php
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija</title>
</head>
<body>
<section id="section">
    <h1>Registracija</h1>
    <p id="prvi">
        Vnesite podatke za registracijo.
    </p>
    <form action="" method="POST">
        Uporabniško ime: <input type="text" name="naziv" value="" required class="vnos" placeholder="Vnesi uporabniško ime"><br>
        Geslo: <input type="password" name="tip" value="" required class="vnos" placeholder="Vnesi geslo"><br>
        <div>
            <input type="submit" name="registergumb" value="Registracija" id="posljigumb">
            <a href="prijava.php"><button type="button" id="registergumb">Nazaj na prijavo?</button></a>
        </div>
    </form>
</section>
</body>
</html>

