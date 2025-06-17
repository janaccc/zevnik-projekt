<?php
require_once 'session.php';
require_once 'povezava.php';

// Preveri prijavo in vlogo
if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit();
}
if ($_SESSION['vloga'] !== 'admin') {
    header("Location: glavna.php");
    exit();
}

// Pridobi seznam izvajalcev
$izvajalci = array();
$poizvedba_izvajalci = "SELECT id, Ime FROM Izvajalci ORDER BY Ime ASC";
$rezultat_izvajalci = mysqli_query($conn, $poizvedba_izvajalci);
while ($vrstica = mysqli_fetch_assoc($rezultat_izvajalci)) {
    $izvajalci[] = $vrstica;
}

// Pridobi seznam žanrov
$zanri = array();
$poizvedba_zanri = "SELECT id, Ime FROM zanri ORDER BY Ime ASC";
$rezultat_zanri = mysqli_query($conn, $poizvedba_zanri);
while ($vrstica = mysqli_fetch_assoc($rezultat_zanri)) {
    $zanri[] = $vrstica;
}

// Preveri, če je podan id pesmi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_glavna.php");
    exit();
}
$pesem_id = intval($_GET['id']);

// Obdelava izbrisa pesmi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete']) && $_POST['delete'] == '1') {
    // Izbriši povezave z izvajalci za to pesem
    $poizvedba_brisi_izvajalci = "DELETE FROM pesem_izvajalci WHERE pesem_id = " . $pesem_id;
    mysqli_query($conn, $poizvedba_brisi_izvajalci);

    // Izbriši uporabniške všečke za to pesem
    $poizvedba_brisi_like = "DELETE FROM Uporabniki_like WHERE pesem_id = " . $pesem_id;
    mysqli_query($conn, $poizvedba_brisi_like);

    // Izbriši pesem
    $poizvedba_brisi_pesem = "DELETE FROM Pesmi WHERE id = " . $pesem_id;
    mysqli_query($conn, $poizvedba_brisi_pesem);

    // Preusmeri nazaj
    header("Location: admin_glavna.php");
    exit();
}

// Obdelava obrazca za update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete'])) {
    $ime = isset($_POST['ime']) ? $_POST['ime'] : '';
    $leto = isset($_POST['leto_izdaje']) ? $_POST['leto_izdaje'] : '';
    $dolzina = isset($_POST['dolzina']) ? $_POST['dolzina'] : '';
    $izvajalec_id = isset($_POST['izvajalec']) ? intval($_POST['izvajalec']) : 0;
    $zanr_id = isset($_POST['zanr']) ? intval($_POST['zanr']) : 0;

    // Naloži trenutne poti do datotek, da jih ohranimo (ker ne spreminjamo slik/avdia)
    $poizvedba_poti = "SELECT pod_do_pesmi, pot_do_slike FROM Pesmi WHERE id = " . $pesem_id . " LIMIT 1";
    $rezultat_poti = mysqli_query($conn, $poizvedba_poti);
    $vrstica_poti = mysqli_fetch_assoc($rezultat_poti);
    $audio_path = $vrstica_poti['pod_do_pesmi'];
    $slika_path = $vrstica_poti['pot_do_slike'];

    // Update brez menjave mp3 in slike - pripravi poizvedbo z mysqli_prepare
    $poizvedba_update = "UPDATE Pesmi SET Ime = ?, leto_izdaje = ?, Dolzina = ?, pod_do_pesmi = ?, pot_do_slike = ?, zanr_id = ? WHERE id = ?";
    $pripravi = mysqli_prepare($conn, $poizvedba_update);
    mysqli_stmt_bind_param($pripravi, "ssssiii", $ime, $leto, $dolzina, $audio_path, $slika_path, $zanr_id, $pesem_id);
    mysqli_stmt_execute($pripravi);
    mysqli_stmt_close($pripravi);

    // Posodobi izvajalca: izbriši obstoječe povezave in vstavi novo
    $poizvedba_izbrisi_izvajalce = "DELETE FROM pesem_izvajalci WHERE pesem_id = " . $pesem_id;
    mysqli_query($conn, $poizvedba_izbrisi_izvajalce);
    if ($izvajalec_id > 0) {
        $poizvedba_vstavi_izvajalca = "INSERT INTO pesem_izvajalci (pesem_id, izvajalec_id) VALUES (?, ?)";
        $pripravi_izvajalca = mysqli_prepare($conn, $poizvedba_vstavi_izvajalca);
        mysqli_stmt_bind_param($pripravi_izvajalca, "ii", $pesem_id, $izvajalec_id);
        mysqli_stmt_execute($pripravi_izvajalca);
        mysqli_stmt_close($pripravi_izvajalca);
    }

    $sporocilo = "Pesem uspešno posodobljena!";
}

// Pridobi podatke pesmi za predizpolnitev
$poizvedba_pesem = "
    SELECT Pesmi.*, pesem_izvajalci.izvajalec_id
    FROM Pesmi
    LEFT JOIN pesem_izvajalci ON Pesmi.id = pesem_izvajalci.pesem_id
    WHERE Pesmi.id = " . $pesem_id . "
    LIMIT 1
";
$rezultat_pesem = mysqli_query($conn, $poizvedba_pesem);
if (!$rezultat_pesem || mysqli_num_rows($rezultat_pesem) == 0) {
    header("Location: admin_glavna.php");
    exit();
}
$pesem = mysqli_fetch_assoc($rezultat_pesem);
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Uredi pesem</title>
    <link rel="stylesheet" href="glavna.css" />
</head>
<body>
<div id="uredi_pesem">
    <h1>Uredi pesem</h1>

    <?php
    if (!empty($sporocilo)) {
        echo '<p style="color: green;">' . htmlspecialchars($sporocilo) . '</p>';
    }
    ?>

    <form method="post" >
        <label>Ime pesmi:
            <input type="text" name="ime" required value="<?php echo htmlspecialchars($pesem['Ime']); ?>" />
        </label>
        <br /><br />
        <label>Leto izdaje:
            <input type="text" name="leto_izdaje" required value="<?php echo htmlspecialchars($pesem['leto_izdaje']); ?>" />
        </label>
        <br /><br />
        <label>Dolžina (ure:minute:sekunde):
            <input type="text" name="dolzina" required value="<?php echo htmlspecialchars($pesem['Dolzina']); ?>" />
        </label>
        <br /><br />

        <label>Izvajalec:
            <select name="izvajalec" required>
                <option value=""> izberi izvajalca </option>
                <?php
                foreach ($izvajalci as $iz) {
                    $iz_selected = ($iz['id'] == $pesem['izvajalec_id']) ? "selected" : "";
                    echo '<option value="' . $iz['id'] . '" ' . $iz_selected . '>' . htmlspecialchars($iz['Ime']) . '</option>';
                }
                ?>
            </select>
        </label>
        <br /><br />

        <label>Žanr:
            <select name="zanr" required>
                <option value=""> izberi žanr </option>
                <?php
                foreach ($zanri as $z) {
                    $z_selected = ($z['id'] == $pesem['zanr_id']) ? "selected" : "";
                    echo '<option value="' . $z['id'] . '" ' . $z_selected . '>' . htmlspecialchars($z['Ime']) . '</option>';
                }
                ?>
            </select>
        </label>
        <br /><br />

        <button type="submit">Posodobi pesem</button>
    </form>

    <form method="post" onsubmit="return confirm('Ali si prepričan, da želiš izbrisati to pesem?');">
        <input type="hidden" name="delete" value="1" />
        <button type="submit">Izbriši pesem</button>
    </form>

    <h2 style="margin-top: 20px;"><a href="admin_glavna.php">Nazaj</a></h2>
</div>
</body>
<footer id="footer">
    Viri: <a href="https://www.w3schools.com" target="_blank">w3schools</a>,
    <a href="https://ucilnice.arnes.si" target="_blank">Arnes učilnice</a> in zvezek.
</footer>
</html>
