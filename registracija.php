<?php
require_once 'povezava.php'; // mora vsebovati $conn = mysqli_connect(...)

if (!$conn) {
    die("Napaka pri povezavi z bazo: " . mysqli_connect_error());
}

require_once 'session.php';

$napaka = '';
$uspeh = '';

if (isset($_POST['registergumb'])) {
    $uporabnisko_ime = trim($_POST['uporabnisko_ime']);
    $geslo = $_POST['geslo'];

    if (empty($uporabnisko_ime) || empty($geslo)) {
        $napaka = "Vsa polja so obvezna.";
    } else {
        $pripravi_poizvedbo = mysqli_prepare($conn, "SELECT id FROM uporabniki WHERE user = ?");
        mysqli_stmt_bind_param($pripravi_poizvedbo, "s", $uporabnisko_ime);
        mysqli_stmt_execute($pripravi_poizvedbo);
        mysqli_stmt_store_result($pripravi_poizvedbo);

        if (mysqli_stmt_num_rows($pripravi_poizvedbo) > 0) {
            $napaka = "Uporabniško ime je že zasedeno.";
        } else {
            $varnostno_geslo = password_hash($geslo, PASSWORD_DEFAULT);

            $pripravi_vnos = mysqli_prepare($conn, "INSERT INTO uporabniki (user, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($pripravi_vnos, "ss", $uporabnisko_ime, $varnostno_geslo);

            if (mysqli_stmt_execute($pripravi_vnos)) {
                $uspeh = "Registracija uspešna. Preusmerjam na prijavo...";
                header("Refresh: 3; URL=prijava.php");
            } else {
                $napaka = "Napaka pri vnosu v bazo.";
            }
            mysqli_stmt_close($pripravi_vnos);
        }

        mysqli_stmt_close($pripravi_poizvedbo);
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registracija</title>
</head>
<body>
<section id="section">
    <h1>Registracija</h1>

    <?php if ($napaka != '') { ?>
        <div style="color: red;"><?php echo htmlspecialchars($napaka); ?></div>
    <?php } ?>

    <?php if ($uspeh != '') { ?>
        <div style="color: green;"><?php echo htmlspecialchars($uspeh); ?></div>
    <?php } ?>

    <p id="prvi">
        Vnesite podatke za registracijo.
    </p>
    <form action="" method="POST">
        <b>Uporabniško ime:</b> <input type="text" name="uporabnisko_ime" required class="vnos" placeholder="Vnesi uporabniško ime" /><br />
        <b>Geslo:</b> <input type="password" name="geslo" required class="vnos" placeholder="Vnesi geslo" /><br />
        <div>
            <input type="submit" name="registergumb" value="Registracija" id="posljigumb" />
            <a href="prijava.php"><button type="button" id="registergumb">Nazaj na prijavo?</button></a>
        </div>
    </form>
</section>
</body>
<footer id="footer">
    Viri: <a href="https://www.w3schools.com" target="_blank">w3schools</a>, 
    <a href="https://ucilnice.arnes.si" target="_blank">Arnes učilnice</a> in zvezek.
</footer>
</html>
