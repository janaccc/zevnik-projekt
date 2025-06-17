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

// Pridobi seznam izvajalcev
$izvajalci = [];
$res = mysqli_query($conn, "SELECT id, Ime FROM Izvajalci ORDER BY Ime ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $izvajalci[] = $row;
}

// Pridobi seznam žanrov
$zanri = [];
$res_zanri = mysqli_query($conn, "SELECT id, Ime FROM zanri ORDER BY Ime ASC");
while ($row = mysqli_fetch_assoc($res_zanri)) {
    $zanri[] = $row;
}

// Preveri, če je podan id pesmi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_glavna.php");
    exit;
}
$pesem_id = (int)$_GET['id'];

// Obdelava obrazca za update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = $_POST['ime'] ?? '';
    $leto = $_POST['leto_izdaje'] ?? '';
    $dolzina = $_POST['dolzina'] ?? '';
    $izvajalec_id = (int)($_POST['izvajalec'] ?? 0);
    $zanr_id = (int)($_POST['zanr'] ?? 0);

    // Naloži trenutne poti do datotek, da jih ohranimo (ker ne spreminjamo slik/avdia)
    $sql = "SELECT pod_do_pesmi, pot_do_slike FROM Pesmi WHERE id = $pesem_id LIMIT 1";
    $res_current = mysqli_query($conn, $sql);
    $row_current = mysqli_fetch_assoc($res_current);
    $audio_path = $row_current['pod_do_pesmi'];
    $slika_path = $row_current['pot_do_slike'];

    // Update brez menjave mp3 in slike
    $stmt = mysqli_prepare($conn, "
        UPDATE Pesmi
        SET Ime = ?, leto_izdaje = ?, Dolzina = ?, pod_do_pesmi = ?, pot_do_slike = ?, zanr_id = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'sssssii', $ime, $leto, $dolzina, $audio_path, $slika_path, $zanr_id, $pesem_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Posodobi izvajalca: izbriši obstoječe povezave in vstavi novo
    mysqli_query($conn, "DELETE FROM pesem_izvajalci WHERE pesem_id = $pesem_id");
    if ($izvajalec_id) {
        $stmt2 = mysqli_prepare($conn, "
            INSERT INTO pesem_izvajalci (pesem_id, izvajalec_id)
            VALUES (?, ?)
        ");
        mysqli_stmt_bind_param($stmt2, 'ii', $pesem_id, $izvajalec_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }

    $sporocilo = "Pesem uspešno posodobljena!";
}

// Pridobi podatke pesmi za predizpolnitev
$sql = "
    SELECT Pesmi.*, pesem_izvajalci.izvajalec_id
    FROM Pesmi
    LEFT JOIN pesem_izvajalci ON Pesmi.id = pesem_izvajalci.pesem_id
    WHERE Pesmi.id = $pesem_id
    LIMIT 1
";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: admin_glavna.php");
    exit;
}
$pesem = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Uredi pesem</title>
    <link rel="stylesheet" href="glavna.css">
</head>
<body>
<div id="uredi_pesem">
    <h1>Uredi pesem</h1>

    <?php if (!empty($sporocilo)): ?>
        <p style="color: green;"><?= htmlspecialchars($sporocilo) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Ime pesmi: <input type="text" name="ime" required value="<?= htmlspecialchars($pesem['Ime']) ?>"></label><br><br>
        <label>Leto izdaje: <input type="text" name="leto_izdaje" required value="<?= htmlspecialchars($pesem['leto_izdaje']) ?>"></label><br><br>
        <label>Dolžina (ure:minute:sekunde): <input type="text" name="dolzina" required value="<?= htmlspecialchars($pesem['Dolzina']) ?>"></label><br><br>



        <label>Izvajalec:
            <select name="izvajalec" required>
                <option value=""> izberi izvajalca </option>
                <?php foreach ($izvajalci as $iz): ?>
                    <option value="<?= $iz['id'] ?>" <?= ($iz['id'] == $pesem['izvajalec_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($iz['Ime']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>Žanr:
            <select name="zanr" required>
                <option value=""> izberi žanr </option>
                <?php foreach ($zanri as $z): ?>
                    <option value="<?= $z['id'] ?>" <?= ($z['id'] == $pesem['zanr_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($z['Ime']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Posodobi pesem</button>
        <h2><a href="admin_glavna.php">Nazaj</a></h2>
    </form>
</div>
</body>
<footer id="footer">
    Viri: <a href="https://www.w3schools.com" target="_blank">w3schools</a>, 
    <a href="https://ucilnice.arnes.si" target="_blank">Arnes učilnice</a> in zvezek.
</footer>
</html>
