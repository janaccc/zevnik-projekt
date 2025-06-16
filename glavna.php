<?php
require_once 'session.php';
require_once 'povezava.php'; // $conn

if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit;
}

$pesmi = [];
$trenutna_pesem = null;

// Pridobi pesmi iz baze
$sql = "SELECT * FROM pesmi ORDER BY title ASC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pesmi[] = $row;
    }

    // Če ni bila podana ID pesmi, vzamemo prvo
    $id = isset($_GET['id']) ? (int)$_GET['id'] : $pesmi[0]['id'];

    foreach ($pesmi as $p) {
        if ($p['id'] == $id) {
            $trenutna_pesem = $p;
            break;
        }
    }
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
            <?php foreach ($pesmi as $pesem): ?>
                <li><a href="predvajalnik.php?id=<?= $pesem['id'] ?>"><?= htmlspecialchars($pesem['title']) ?></a></li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section id="predvajalnik">
        <?php if ($trenutna_pesem): ?>
            <h1><?= htmlspecialchars($trenutna_pesem['title']) ?></h1>
            <h3>Opis: <?= htmlspecialchars($trenutna_pesem['description']) ?></h3>
            <audio controls>
                <source src="<?= htmlspecialchars($trenutna_pesem['audio_path']) ?>" type="audio/mpeg">
                Vaš brskalnik ne podpira predvajalnika.
            </audio>
        <?php else: ?>
            <p>Ni pesmi za prikaz.</p>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
