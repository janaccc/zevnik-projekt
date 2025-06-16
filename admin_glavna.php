<?php
require_once 'session.php';
require_once 'povezava.php';

// Preveri prijavo in vlogo
if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit;
}
if ($_SESSION['vloga'] !== 'admin') {
    header("Location: glavna.php");
    exit;
}

$pesmi = [];
$trenutna_pesem = null;

// Pridobi vse pesmi z osnovnimi podatki, vključno z audio in sliko
$sql = "
    SELECT 
        Pesmi.id AS pesem_id,
        Pesmi.Ime AS pesem_naslov,
        Pesmi.leto_izdaje,
        Pesmi.Dolzina,
        Pesmi.pod_do_pesmi,
        Pesmi.pot_do_slike,
        Albumi.Ime AS album,
        zanri.Ime AS zanr
    FROM Pesmi
    LEFT JOIN Albumi ON Pesmi.album_id = Albumi.id
    LEFT JOIN zanri ON Pesmi.zanr_id = zanri.id
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

// Funkcija za pridobivanje izvajalcev za dano pesem
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
    <meta charset="UTF-8">
    <title>Admin Glavna Stran - Predvajalnik</title>
    <link rel="stylesheet" type="text/css" href="glavna.css">
</head>
<body>

<div id="edit">
    <h1><a id="adminpanel" href="admin_panel.php">ADMIN PANEL</a></h1>
</div>

<div id="vsebina">
    <section id="meni">
        <h1 id="meniH1">Meni</h1>
        <h2 id="odjava"><a href="odjava.php">Odjava</a></h2>
        <ol id="pesmi">
            <?php foreach ($pesmi as $pesem): ?>
                <li>
                    <a href="admin_glavna.php?id=<?= $pesem['pesem_id'] ?>">
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
            <p><strong>Žanr:</strong> <?= htmlspecialchars($trenutna_pesem['zanr']) ?></p>
            <p><strong>Album:</strong> <?= htmlspecialchars($trenutna_pesem['album']) ?></p>
            <p><strong>Dolžina:</strong> <?= htmlspecialchars($trenutna_pesem['Dolzina']) ?></p>
            <p><strong>Izvajalci:</strong> <?= implode(", ", getIzvajalciZaPesem($conn, $trenutna_pesem['pesem_id'])) ?></p>

            <?php if (!empty($trenutna_pesem['slika_path'])): ?>
                <img src="<?= htmlspecialchars($trenutna_pesem['slika_path']) ?>" alt="Slika pesmi" style="max-width: 300px; margin-top: 10px; margin-bottom: 20px;">
            <?php endif; ?>

            <?php if (!empty($trenutna_pesem['audio_path'])): ?>
                <h3>Predvajaj:</h3>
                <audio controls style="width: 100%; max-width: 600px;">
                    <source src="<?= htmlspecialchars($trenutna_pesem['audio_path']) ?>" type="audio/mpeg">
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
