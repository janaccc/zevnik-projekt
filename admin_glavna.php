<?php
require_once 'session.php';
require_once 'povezava.php';

if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit();
}

if ($_SESSION['vloga'] != 'admin') {
    header("Location: glavna.php");
    exit();
}

$uporabnik_id = $_SESSION['id'];
$pesmi = array();
$trenutna_pesem = null;
$vsecki = array();

// Obdelava všečkov
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['like_pesem_id'])) {
        $pesem_id = (int) $_POST['like_pesem_id'];

        $sql = "INSERT IGNORE INTO uporabniki_like (uporabnik_id, pesem_id) VALUES (" . $uporabnik_id . ", " . $pesem_id . ")";
        mysqli_query($conn, $sql);

        header("Location: admin_glavna.php?id=" . $pesem_id);
        exit();
    }

    if (isset($_POST['unlike_pesem_id'])) {
        $pesem_id = (int) $_POST['unlike_pesem_id'];

        $sql = "DELETE FROM uporabniki_like WHERE uporabnik_id = " . $uporabnik_id . " AND pesem_id = " . $pesem_id;
        mysqli_query($conn, $sql);

        header("Location: admin_glavna.php");
        exit();
    }
}

// Pridobi vse pesmi
$sql_pesmi = "
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

$rezultat = mysqli_query($conn, $sql_pesmi);

if ($rezultat && mysqli_num_rows($rezultat) > 0) {
    while ($vrstica = mysqli_fetch_assoc($rezultat)) {
        $pesmi[] = $vrstica;
    }

    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
    } else {
        $id = $pesmi[0]['pesem_id'];
    }

    foreach ($pesmi as $p) {
        if ($p['pesem_id'] == $id) {
            $trenutna_pesem = $p;
            break;
        }
    }
}

// Pridobi vsecke uporabnika
$sql_vsecki = "
    SELECT Pesmi.id, Pesmi.Ime
    FROM uporabniki_like
    INNER JOIN Pesmi ON uporabniki_like.pesem_id = Pesmi.id
    WHERE uporabniki_like.uporabnik_id = " . $uporabnik_id . "
    ORDER BY Pesmi.Ime ASC
";

$rezultat_vsecki = mysqli_query($conn, $sql_vsecki);
if ($rezultat_vsecki && mysqli_num_rows($rezultat_vsecki) > 0) {
    while ($vrstica = mysqli_fetch_assoc($rezultat_vsecki)) {
        $vsecki[] = $vrstica;
    }
}

function getIzvajalciZaPesem($conn, $pesem_id) {
    $izvajalci = array();
    $sql = "
        SELECT Ime
        FROM Izvajalci
        INNER JOIN pesem_izvajalci ON Izvajalci.id = pesem_izvajalci.izvajalec_id
        WHERE pesem_izvajalci.pesem_id = " . $pesem_id;

    $rezultat = mysqli_query($conn, $sql);
    if ($rezultat && mysqli_num_rows($rezultat) > 0) {
        while ($vrstica = mysqli_fetch_assoc($rezultat)) {
            $izvajalci[] = $vrstica['Ime'];
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
            <?php
            foreach ($pesmi as $pesem) {
                echo '<li>';
                echo '<a href="admin_glavna.php?id=' . $pesem['pesem_id'] . '">' . htmlspecialchars($pesem['pesem_naslov']) . '</a>';
                echo '<a href="uredi_pesem.php?id=' . $pesem['pesem_id'] . '" style="margin-left: 10px;"><button>Uredi</button></a>';
                echo '</li>';
            }
            ?>
        </ol>
    </section>

    <section id="vsecki">
        <h1 id="vseckiH1">Všečki</h1>
        <?php
        if (!empty($vsecki)) {
            echo '<ol>';
            foreach ($vsecki as $v) {
                echo '<li>';
                echo '<a href="admin_glavna.php?id=' . $v['id'] . '">' . htmlspecialchars($v['Ime']) . '</a>';
                echo '<form method="POST" style="margin-left:10px;">';
                echo '<input type="hidden" name="unlike_pesem_id" value="' . $v['id'] . '">';
                echo '<button type="submit" id="unlike_gumb">Unlike</button>';
                echo '</form>';
                echo '</li>';
            }
            echo '</ol>';
        } else {
            echo '<p>Ni všečkanih pesmi.</p>';
        }
        ?>
    </section>

    <section id="predvajalnik">
        <?php
        if ($trenutna_pesem != null) {
            echo '<h1>' . htmlspecialchars($trenutna_pesem['pesem_naslov']) . '</h1>';
            echo '<p><strong>Leto:</strong> ' . htmlspecialchars($trenutna_pesem['leto_izdaje']) . '</p>';
            echo '<p><strong>Žanr:</strong> ' . htmlspecialchars($trenutna_pesem['zanr']) . '</p>';
            echo '<p><strong>Album:</strong> ' . htmlspecialchars($trenutna_pesem['album']) . '</p>';
            echo '<p><strong>Dolžina:</strong> ' . htmlspecialchars($trenutna_pesem['Dolzina']) . '</p>';

            $izvajalci = getIzvajalciZaPesem($conn, $trenutna_pesem['pesem_id']);
            echo '<p><strong>Izvajalci:</strong> ' . implode(", ", $izvajalci) . '</p>';

            if (!empty($trenutna_pesem['pot_do_slike'])) {
                echo '<img src="' . htmlspecialchars($trenutna_pesem['pot_do_slike']) . '" alt="Slika pesmi" style="max-width:300px; margin-top:10px; margin-bottom:20px;">';
            }

            if (!empty($trenutna_pesem['pod_do_pesmi'])) {
                echo '<h3>Predvajaj:</h3>';
                echo '<audio controls style="width:100%; max-width:600px;">';
                echo '<source src="' . htmlspecialchars($trenutna_pesem['pod_do_pesmi']) . '" type="audio/mpeg">';
                echo 'Vaš brskalnik ne podpira predvajalnika zvoka.';
                echo '</audio>';
            } else {
                echo '<p><em>Ni datoteke za predvajanje.</em></p>';
            }

            echo '<form method="POST" style="margin-top:15px;">';
            echo '<input type="hidden" name="like_pesem_id" value="' . $trenutna_pesem['pesem_id'] . '">';
            echo '<button type="submit" id="like_gumb">Like</button>';
            echo '</form>';
        } else {
            echo '<p>Ni pesmi za prikaz.</p>';
        }
        ?>
    </section>
</div>

</body>
<footer id="footer">
    Viri: <a href="https://www.w3schools.com" target="_blank">w3schools</a>,
    <a href="https://ucilnice.arnes.si" target="_blank">Arnes učilnice</a> in zvezek.
</footer>
</html>
