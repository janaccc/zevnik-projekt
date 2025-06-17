<?php
require_once 'session.php';
require_once 'povezava.php';

if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit();
}

$pesmi = array();
$trenutna_pesem = null;
$uporabnik_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['like_pesem_id'])) {
        // preveri če je metoda post in če je uporabnik kliknil gumb like
        $pesem_id = intval($_POST['like_pesem_id']);
        $statement = mysqli_prepare($conn, "INSERT IGNORE INTO uporabniki_like (uporabnik_id, pesem_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($statement, "ii", $uporabnik_id, $pesem_id);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
        header("Location: glavna.php?id=" . $pesem_id);
        exit();
    } elseif (isset($_POST['unlike_pesem_id'])) {
        // Odstrani pesem iz všečkov če klikne unlike
        $pesem_id = intval($_POST['unlike_pesem_id']);
        $statement = mysqli_prepare($conn, "DELETE FROM uporabniki_like WHERE uporabnik_id = ? AND pesem_id = ?");
        mysqli_stmt_bind_param($statement, "ii", $uporabnik_id, $pesem_id);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
        header("Location: glavna.php");
        exit();
    }
}

// Pridobi vse pesmi, prikazana pesem je prva iz seznama, trenutna pesem se shrani v $trenutna_pesem
$sql = "SELECT 
            Pesmi.id AS pesem_id,
            Pesmi.Ime AS pesem_naslov,
            Pesmi.leto_izdaje,
            Pesmi.Dolzina,
            Pesmi.pod_do_pesmi,
            Pesmi.pot_do_slike
        FROM Pesmi
        ORDER BY Pesmi.Ime ASC";

$rezultat = mysqli_query($conn, $sql);

if ($rezultat && mysqli_num_rows($rezultat) > 0) {
    while ($vrstica = mysqli_fetch_assoc($rezultat)) {
        $pesmi[] = $vrstica;
    }
    $id = isset($_GET['id']) ? intval($_GET['id']) : $pesmi[0]['pesem_id'];

    foreach ($pesmi as $pesem) {
        if ($pesem['pesem_id'] == $id) {
            $trenutna_pesem = $pesem;
            break;
        }
    }
}

//pridobitev izvajalcev za določeno pesem
function getIzvajalciZaPesem($povezava, $pesem_id) {
    $izvajalci = array();
    $sql = "SELECT Ime
            FROM Izvajalci
            INNER JOIN pesem_izvajalci ON Izvajalci.id = pesem_izvajalci.izvajalec_id
            WHERE pesem_izvajalci.pesem_id = " . intval($pesem_id);
    $rezultat = mysqli_query($povezava, $sql);
    if ($rezultat) {
        while ($vrstica = mysqli_fetch_assoc($rezultat)) {
            $izvajalci[] = $vrstica['Ime'];
        }
    }
    return $izvajalci;
}

// Pridobi všečkane pesmi trenutnega uporabnika
$vsecki = array();
$sql_vsecki = "SELECT Pesmi.id, Pesmi.Ime
               FROM uporabniki_like
               INNER JOIN Pesmi ON uporabniki_like.pesem_id = Pesmi.id
               WHERE uporabniki_like.uporabnik_id = " . intval($uporabnik_id) . "
               ORDER BY Pesmi.Ime ASC";

$rezultat_vsecki = mysqli_query($conn, $sql_vsecki);

if ($rezultat_vsecki && mysqli_num_rows($rezultat_vsecki) > 0) {
    while ($vrstica = mysqli_fetch_assoc($rezultat_vsecki)) {
        $vsecki[] = $vrstica;
    }
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
                    <a href="glavna.php?id=<?php echo $pesem['pesem_id']; ?>">
                        <?php echo htmlspecialchars($pesem['pesem_naslov']); ?>
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
                        <a href="glavna.php?id=<?php echo $v['id']; ?>">
                            <?php echo htmlspecialchars($v['Ime']); ?>
                        </a>
                        <form method="POST" style="margin-left:10px;">
                            <input type="hidden" name="unlike_pesem_id" value="<?php echo $v['id']; ?>">
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
            <h1><?php echo htmlspecialchars($trenutna_pesem['pesem_naslov']); ?></h1>
            <p><strong>Leto:</strong> <?php echo htmlspecialchars($trenutna_pesem['leto_izdaje']); ?></p>
            <p><strong>Dolžina:</strong> <?php echo htmlspecialchars($trenutna_pesem['Dolzina']); ?></p>
            <p><strong>Izvajalci:</strong> <?php echo implode(", ", getIzvajalciZaPesem($conn, $trenutna_pesem['pesem_id'])); ?></p>

            <?php if (!empty($trenutna_pesem['pot_do_slike'])): ?>
                <img src="<?php echo htmlspecialchars($trenutna_pesem['pot_do_slike']); ?>" alt="Slika pesmi" style="max-width:300px; margin-top:10px; margin-bottom:20px;">
            <?php endif; ?>

            <?php if (!empty($trenutna_pesem['pod_do_pesmi'])): ?>
                <h3>Predvajaj:</h3>
                <audio controls style="width:100%; max-width:600px;">
                    <source src="<?php echo htmlspecialchars($trenutna_pesem['pod_do_pesmi']); ?>" type="audio/mpeg">
                    Vaš brskalnik ne podpira predvajalnika zvoka.
                </audio>
            <?php else: ?>
                <p><em>Ni datoteke za predvajanje.</em></p>
            <?php endif; ?>

            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="like_pesem_id" value="<?php echo $trenutna_pesem['pesem_id']; ?>">
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
