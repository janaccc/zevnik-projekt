<?php
require_once 'session.php';

if (!isset($_SESSION['user'])) {
    // Če uporabnik ni prijavljen, ga preusmeri na prijavo
    header("Location: prijava.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="glavna.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music player</title>
</head>
<body>

<div id="vsebina">
    <section id="meni">
        <h1 id="meniH1">Meni</h1>
        <h2 id="odjava"><a href="odjava.php">Odjava</a></h2>
        <ol id="pesmi">
            <li><a href="pesmi/pesem1">Test</a></li>
        </ol>
    </section>

    <section id="predvajalnik">
        <h1>Pesem 1</h1>
        <h3>Avtor: </h3>
        <h3>Izdano: </h3>
        <audio controls>
            <source src="pesmi/mesanomeso.mp3" type="audio/mpeg">
            Vaš brskalnik ne podpira predvajalca pesmi.
        </audio>
    </section>
</div>

</body>
</html>
