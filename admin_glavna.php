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
$uporabnik_id = $_SESSION['id'];

// Obdelaj Like gumb
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['like_pesem_id'])) {
        // Dodaj v všečke
        $pesem_id = (int)$_POST['like_pesem_id'];
        $stmt = $conn->prepare("INSERT IGNORE INTO uporabniki_like (uporabnik_id, pesem_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $uporabnik_id, $pesem_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_glavna.php?id=" . $pesem_id);
        exit;
    } elseif (isset($_POST['unlike_pesem_id'])) {
        // Odstrani iz všečkov
        $pesem_id = (int)$_POST['unlike_pesem_id'];
        $stmt = $conn->prepare("DELETE FROM uporabniki_like WHERE uporabnik_id = ? AND pesem_id = ?");
        $stmt->bind_param("ii", $uporabnik_id, $pesem_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_glavna.php");
        exit;
    }
}

// Pridobi vse pesmi
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

// Funkcija za izvajalce
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

// Pridobi všečkane pesmi trenutnega uporabnika
$vsecki = [];
$sql_vsecki = "
    SELECT Pesmi.id, Pesmi.Ime
    FROM uporabniki_like
    INNER JOIN Pesmi ON uporabniki_like.pesem_id = Pesmi.id
    WHERE uporabniki_like.uporabnik_id = $uporabnik_id
    ORDER BY Pesmi.Ime ASC
";
$result_vsecki = mysqli_query($conn, $sql_vsecki);
if ($result_vsecki && mysqli_num_rows($result_vsecki) > 0) {
    while ($row = mysqli_fetch_assoc($result_vsecki)) {
        $vsecki[] = $row;
    }
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
                    <a href="uredi_pesem.php?id=<?= $pesem['pesem_id'] ?>" style="margin-left: 10px;">
                        <button>Uredi</button>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section id="vsecki">
        <h1 id="vseckiH1">Všečki</h1>
        <?php if (!empty($vsecki)): ?>
            <ol>
                <?php foreach ($vsecki as $v): ?>
                    <li>
                        <a href="admin_glavna.php?id=<?= $v['id'] ?>">
                            <?= htmlspecialchars($v['Ime']) ?>
                        </a>
                        <form method="POST" style="display:inline; margin-left:10px;">
                            <input type="hidden" name="unlike_pesem_id" value="<?= $v['id'] ?>">
                            <button type="submit" id="unlike_gumb">Unlike</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p>Ni všečkanih pesmi.</p>
        <?php endif; ?>
    </section>

    <section id="predvajalnik">
        <?php if ($trenutna_pesem): ?>
            <h1><?= htmlspecialchars($trenutna_pesem['pesem_naslov']) ?></h1>
            <p><strong>Leto:</strong> <?= htmlspecialchars($trenutna_pesem['leto_izdaje']) ?></p>
            <p><strong>Žanr:</strong> <?= htmlspecialchars($trenutna_pesem['zanr']) ?></p>
            <p><strong>Album:</strong> <?= htmlspecialchars($trenutna_pesem['album']) ?></p>
            <p><strong>Dolžina:</strong> <?= htmlspecialchars($trenutna_pesem['Dolzina']) ?></p>
            <p><strong>Izvajalci:</strong> <?= implode(", ", getIzvajalciZaPesem($conn, $trenutna_pesem['pesem_id'])) ?></p>

            <?php if (!empty($trenutna_pesem['pot_do_slike'])): ?>
                <img src="<?= htmlspecialchars($trenutna_pesem['pot_do_slike']) ?>" alt="Slika pesmi" style="max-width: 300px; margin-top: 10px; margin-bottom: 20px;">
            <?php endif; ?>

            <?php if (!empty($trenutna_pesem['pod_do_pesmi'])): ?>
                <h3>Predvajaj:</h3>
                <audio controls style="width: 100%; max-width: 600px;">
                    <source src="<?= htmlspecialchars($trenutna_pesem['pod_do_pesmi']) ?>" type="audio/mpeg">
                    Vaš brskalnik ne podpira predvajalnika zvoka.
                </audio>
            <?php else: ?>
                <p><em>Ni datoteke za predvajanje.</em></p>
            <?php endif; ?>

            <!-- Gumb Like -->
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="like_pesem_id" value="<?= $trenutna_pesem['pesem_id'] ?>">
                <button type="submit" id="like_gumb">Like</button>
            </form>

        <?php else: ?>
            <p>Ni pesmi za prikaz.</p>
        <?php endif; ?>
    </section>
</div>

</body>
<footer id="footer">
    Viri: <a href="https://www.w3schools.com" target="_blank">w3schools</a>, 
    <a href="https://ucilnice.arnes.si" target="_blank">Arnes učilnice</a> in zvezek.
</footer>
</html>
