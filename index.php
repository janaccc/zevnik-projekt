<?php
require_once 'povezava.php'; 
require_once 'session.php'; 


if (isset($_SESSION['user']) && isset($_SESSION['vloga'])) {
    if ($_SESSION['vloga'] === 'admin') {
        header("Location: admin_glavna.php");
        exit();
    } else {
        header("Location: glavna.php");
        exit();
    }
}

$napaka = '';

if (isset($_POST['prijava'])) {
    $uporabnisko_ime = trim($_POST['naziv']);
    $geslo = $_POST['tip'];

    if (empty($uporabnisko_ime) || empty($geslo)) {
        $napaka = "Vnesite uporabniško ime in geslo.";
    } else {

        //statement zascita pred sql injection
        $statement = mysqli_prepare($conn, "SELECT id, password, vloga FROM uporabniki WHERE user = ?");
        mysqli_stmt_bind_param($statement, "s", $uporabnisko_ime);
        mysqli_stmt_execute($statement);
        mysqli_stmt_store_result($statement);

        if (mysqli_stmt_num_rows($statement) === 1) {
            mysqli_stmt_bind_result($statement, $id, $hashed_geslo, $vloga);
            mysqli_stmt_fetch($statement);

            if (password_verify($geslo, $hashed_geslo)) {
                $_SESSION['id'] = $id;
                $_SESSION['user'] = $uporabnisko_ime;
                $_SESSION['vloga'] = $vloga;

                if ($vloga === 'admin') {
                    header("Location: admin_glavna.php");
                    exit();
                } else {
                    header("Location: glavna.php");
                    exit();
                }
            } else {
                $napaka = "Napačno geslo.";
            }
        } else {
            $napaka = "Uporabnik ne obstaja.";
        }

        mysqli_stmt_close($statement);
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Prijava v aplikacijo</title>
</head>
<body>
<section id="section">
    <h1>Prijava</h1>

    <?php if ($napaka != ''): ?>
        <div style="color:red;"><?php echo htmlspecialchars($napaka); ?></div>
    <?php endif; ?>

    <p id="prvi">
        Za uporabo aplikacije je potrebna prijava.
    </p>
    <form action="" method="POST">
        Uporabniško ime: <input type="text" name="naziv" required class="vnos" placeholder="Vnesi uporabniško ime" /><br />
        Geslo: <input type="password" name="tip" required class="vnos" placeholder="Vnesi geslo" /><br />
        <div>
            <input type="submit" name="prijava" value="Prijava" id="posljigumb" />
            <a href="registracija.php"><button type="button" id="registergumb">Registracija</button></a>
        </div>
    </form>
</section>
</body>
<footer id="footer">
    Viri: <a href="https://www.w3schools.com" target="_blank">w3schools</a>, 
    <a href="https://ucilnice.arnes.si" target="_blank">Arnes učilnice</a> in zvezek.
</footer>
</html>
