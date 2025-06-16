<?php
require_once 'session.php';
require_once 'povezava.php';

if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit;
}

$pesmi = [];
$trenutna_pesem = null;

$sql = "
    SELECT 
        Pesmi.id AS pesem_id,
        Pesmi.Ime AS pesem_naslov,
        Pesmi.leto_izdaje,
        Pesmi.Dolzina,
        Pesmi.pod_do_pesmi,
        Pesmi.pot_do_slike
    FROM Pesmi
    ORDER BY Pesmi.Ime ASC
";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pesmi[] = $row;
    }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : $pesmi[0]['pesem_id'];
    foreach ($pesmi as $p) {
        if ($p['pesem_id'] == $id) {
            $trenutna_pesem = $p;
            break;
        }
    }
}

function getIzvajalciZaPesem($conn, $pesem_id) {
    $izvajalci = [];
    $sql = "
        SELECT Ime
        FROM Izvajalci
        INNER JOIN pesem_izvajalci ON Izvajalci.id = pesem_izvajalci.izvajalec_id
        WHERE pesem_izvajalci.pesem_id = $pesem_id
    ";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $izvajalci[] = $row['Ime'];
        }
    }
    return $izvajalci;
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8" />
    <title>Predvajalnik pesmi</title>
    <link rel="stylesheet" href="glavna.css" />
</head>
<body>

<div id="vsebina">

    <section id="meni">
        <h1 id="meniH1">Meni</h1>
        <h2 id="odjava"><a href="odjava.php">Odjava</a></h2>
        <ol id="pesmi">
            <?php foreach ($pesmi as $pesem): ?>
                <li>
                    <a href="glavna.php?id=<?= $pesem['pesem_id'] ?>">
                        <?= htmlspecialchars($pesem['pesem_naslov']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section id="predvajalnik">
        <?php if ($trenutna_pesem): ?>
            <h1><?= htmlspecialchars($trenutna_pesem['pesem_naslov']) ?></h1>
            <p><strong>Leto:</strong> <?= htmlspecialchars($trenutna_pesem['leto_izdaje']) ?></p>
            <p><strong>Dolžina:</strong> <?= htmlspecialchars($trenutna_pesem['Dolzina']) ?></p>
            <p><strong>Izvajalci:</strong> <?= implode(", ", getIzvajalciZaPesem($conn, $trenutna_pesem['pesem_id'])) ?></p>

            <?php if (!empty($trenutna_pesem['pot_do_slike'])): ?>
                <img src="<?= htmlspecialchars($trenutna_pesem['pot_do_slike']) ?>" alt="Slika pesmi" style="max-width:300px;margin-top:10px;margin-bottom:20px;">
            <?php endif; ?>

            <?php if (!empty($trenutna_pesem['pod_do_pesmi'])): ?>
                <h3>Predvajaj:</h3>
                <audio controls style="width:100%;max-width:600px;">
                    <source src="<?= htmlspecialchars($trenutna_pesem['pod_do_pesmi']) ?>" type="audio/mpeg">
                    Vaš brskalnik ne podpira predvajalnika zvoka.
                </audio>
            <?php else: ?>
                <p><em>Ni datoteke za predvajanje.</em></p>
            <?php endif; ?>
        <?php else: ?>
            <p>Ni pesmi za prikaz.</p>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
