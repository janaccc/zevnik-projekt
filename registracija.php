<?php
require_once 'povezava.php'; // mora vsebovati $conn = mysqli_connect(...)

if (!$conn) {
    die("Napaka pri povezavi z bazo: " . mysqli_connect_error());
}

require_once 'session.php';

$napaka = '';
$uspeh = '';

try {
    if (isset($_POST['registergumb'])) {
        $uporabnisko_ime = trim($_POST['uporabnisko_ime']);
$geslo = $_POST['geslo'];

        if (empty($uporabnisko_ime) || empty($geslo)) {
            $napaka = "Vsa polja so obvezna.";
        } else {
            // Preverimo, če uporabnik že obstaja
            $stmt = mysqli_prepare($conn, "SELECT id FROM uporabniki WHERE user = ?");
            mysqli_stmt_bind_param($stmt, "s", $uporabnisko_ime);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $napaka = "Uporabniško ime je že zasedeno.";
            } else {
                // Hash gesla
                $hashed_password = password_hash($geslo, PASSWORD_DEFAULT);

                // Vnos novega uporabnika
                $stmt = mysqli_prepare($conn, "INSERT INTO uporabniki (user, password) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ss", $uporabnisko_ime, $hashed_password);

                if (mysqli_stmt_execute($stmt)) {
                    header("Refresh: 3; URL=prijava.php");
                    $uspeh = "Registracija uspešna.";
                } else {
                    $napaka = "Napaka pri vnosu v bazo.";
                }
            }

            mysqli_stmt_close($stmt);
        }
    }
// Nekaj dela, npr. priprava stavka
$stmt = mysqli_prepare($conn, "SELECT ...");
if (!$stmt) {
    $napaka = "Napaka pri pripravi poizvedbe: " . mysqli_error($conn);
} else {
    // nadaljuj z izvajanjem
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <link rel="stylesheet" type="text/css" href="index.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija</title>
</head>
<body>
<section id="section">
    <h1>Registracija</h1>

<?php
if (!empty($napaka)) { //izpise njiz napake oz uspeha
    echo '<div style="color: red;">' . htmlspecialchars($napaka) . '</div>';
}

if (!empty($uspeh)) {
    echo '<div style="color: green;">' . htmlspecialchars($uspeh) . '</div>';
};
?>

    <p id="prvi">
        Vnesite podatke za registracijo.
    </p>
    <form action="" method="POST">
        <b>Uporabniško ime:</b> <input type="text" name="uporabnisko_ime" required class="vnos" placeholder="Vnesi uporabniško ime"><br>
        <b>Geslo:</b> <input type="password" name="geslo" required class="vnos" placeholder="Vnesi geslo"><br>
        <div>
            <input type="submit" name="registergumb" value="Registracija" id="posljigumb">
            <a href="prijava.php"><button type="button" id="registergumb">Nazaj na prijavo?</button></a>
        </div>
    </form>
</section>
</body>
</html>
