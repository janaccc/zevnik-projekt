<?php
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
        <h1>Seznam pesem</h1>
        <ol>
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
