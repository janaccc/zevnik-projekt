<?php
require_once 'povezava.php'; // vsebuje $conn
require_once 'session.php';  // vsebuje session_start()

// Če je uporabnik že prijavljen, ga preusmeri na glavna.php
if (isset($_SESSION['user'])) {
    header("Location: glavna.php");
    exit;
}

$napaka = '';

if (isset($_POST['prijava'])) {
    $uporabnisko_ime = trim($_POST['naziv']);
    $geslo = $_POST['tip'];

    if (empty($uporabnisko_ime) || empty($geslo)) {
        $napaka = "Vnesite uporabniško ime in geslo.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, password FROM uporabniki WHERE user = ?");
        mysqli_stmt_bind_param($stmt, "s", $uporabnisko_ime);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id, $hashed_password);
            mysqli_stmt_fetch($stmt);

            if (password_verify($geslo, $hashed_password)) {
                $_SESSION['id'] = $id;
                $_SESSION['user'] = $uporabnisko_ime;
                header("Location: glavna.php");
                exit;
            } else {
                $napaka = "Napačno geslo.";
            }
        } else {
            $napaka = "Uporabnik ne obstaja.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava v aplikacijo</title>
</head>
<body>
<section id="section">
    <h1>Prijava</h1>

    <?php if (!empty($napaka)): ?>
        <div style="color:red;"><?= htmlspecialchars($napaka) ?></div>
    <?php endif; ?>

    <p id="prvi">
        Za uporabo aplikacije je potrebna prijava.
    </p>
    <form action="" method="POST">
        Uporabniško ime: <input type="text" name="naziv" value="" required class="vnos" placeholder="Vnesi uporabniško ime"><br>
        Geslo: <input type="password" name="tip" value="" required class="vnos" placeholder="Vnesi geslo"><br>
        <div>
            <input type="submit" name="prijava" value="Prijava" id="posljigumb">
            <a href="registracija.php"><button type="button" id="registergumb">Registracija</button></a>
        </div>
    </form>
</section>
</body>
</html>
