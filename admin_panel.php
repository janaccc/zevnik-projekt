<?php
require_once 'session.php';
require_once 'povezava.php';

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['user'])) {
    header("Location: prijava.php");
    exit();
}

// Preveri, če je uporabnik admin
if ($_SESSION['vloga'] !== 'admin') {
    header("Location: glavna.php");
    exit();
}

// Pridobi seznam izvajalcev
$izvajalci = array();
$poizvedba_izvajalci = "SELECT id, Ime FROM Izvajalci ORDER BY Ime ASC";
$rezultat_izvajalci = mysqli_query($conn, $poizvedba_izvajalci);
if ($rezultat_izvajalci) {
    while ($vrstica = mysqli_fetch_assoc($rezultat_izvajalci)) {
        $izvajalci[] = $vrstica;
    }
}

// Pridobi seznam žanrov
$zanri = array();
$poizvedba_zanri = "SELECT id, Ime FROM zanri ORDER BY Ime ASC";
$rezultat_zanri = mysqli_query($conn, $poizvedba_zanri);
if ($rezultat_zanri) {
    while ($vrstica = mysqli_fetch_assoc($rezultat_zanri)) {
        $zanri[] = $vrstica;
    }
}

// Pridobi seznam albumov
$albumi = array();
$poizvedba_albumi = "SELECT id, Ime FROM Albumi ORDER BY Ime ASC";
$rezultat_albumi = mysqli_query($conn, $poizvedba_albumi);
if ($rezultat_albumi) {
    while ($vrstica = mysqli_fetch_assoc($rezultat_albumi)) {
        $albumi[] = $vrstica;
    }
}

$sporocilo = "";
$sporocilo_izvajalec = "";
$sporocilo_album = "";

// Obdelava obrazca za dodajanje pesmi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_pesem'])) {

    $ime = isset($_POST['ime']) ? $_POST['ime'] : "";
    $leto = isset($_POST['leto_izdaje']) ? $_POST['leto_izdaje'] : "";
    $dolzina = isset($_POST['dolzina']) ? $_POST['dolzina'] : "";
    $izvajalec_id = isset($_POST['izvajalec']) ? (int)$_POST['izvajalec'] : 0;
    $zanr_id = isset($_POST['zanr']) ? (int)$_POST['zanr'] : 0;

    $album_id = null;
    if (isset($_POST['album'])) {
        if ($_POST['album'] === "" || $_POST['album'] == 0) {
            $album_id = null;
        } else {
            $album_id = (int)$_POST['album'];
        }
    }

    // Naloži mp3
    $audio_path = "";
    if (isset($_FILES['mp3']) && $_FILES['mp3']['error'] == 0) {
        if (!is_dir('pesmi')) {
            mkdir('pesmi', 0777, true);
        }
        $filename = basename($_FILES['mp3']['name']);
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        $audio_path = 'pesmi/' . $filename;
        move_uploaded_file($_FILES['mp3']['tmp_name'], $audio_path);
    }

    // Naloži sliko
    $slika_path = "";
    if (isset($_FILES['slika']) && $_FILES['slika']['error'] == 0) {
        if (!is_dir('slike_pesmi')) {
            mkdir('slike_pesmi', 0777, true);
        }
        $filename = basename($_FILES['slika']['name']);
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
        $slika_path = 'slike_pesmi/' . $filename;
        move_uploaded_file($_FILES['slika']['tmp_name'], $slika_path);
    }

    if ($album_id === null) {
        $poizvedba = "INSERT INTO Pesmi (Ime, leto_izdaje, Dolzina, pod_do_pesmi, pot_do_slike, zanr_id, album_id)
                      VALUES (?, ?, ?, ?, ?, ?, NULL)";
        $stmt = mysqli_prepare($conn, $poizvedba);
        // Tipi: ime, leto, dolzina, pod_do_pesmi, pot_do_slike so stringi, zanr_id je int
        mysqli_stmt_bind_param($stmt, "sssssi", $ime, $leto, $dolzina, $audio_path, $slika_path, $zanr_id);
    } else {
        $poizvedba = "INSERT INTO Pesmi (Ime, leto_izdaje, Dolzina, pod_do_pesmi, pot_do_slike, zanr_id, album_id)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $poizvedba);
        // Tipi: ime, leto, dolzina, pod_do_pesmi, pot_do_slike so stringi, zanr_id, album_id so int
        mysqli_stmt_bind_param($stmt, "sssssii", $ime, $leto, $dolzina, $audio_path, $slika_path, $zanr_id, $album_id);
    }

    mysqli_stmt_execute($stmt);
    $pesem_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($pesem_id && $izvajalec_id) {
        $poizvedba2 = "INSERT INTO pesem_izvajalci (pesem_id, izvajalec_id) VALUES (?, ?)";
        $stmt2 = mysqli_prepare($conn, $poizvedba2);
        mysqli_stmt_bind_param($stmt2, "ii", $pesem_id, $izvajalec_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }

    $sporocilo = "Pesem uspešno dodana!";
}

// Obdelava obrazca za dodajanje izvajalcev
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_izvajalec'])) {

    $ime_izvajalca = isset($_POST['ime_izvajalca']) ? trim($_POST['ime_izvajalca']) : "";
    $opis_izvajalca = isset($_POST['opis_izvajalca']) ? trim($_POST['opis_izvajalca']) : "";

    if ($ime_izvajalca == "") {
        $sporocilo_izvajalec = "Ime izvajalca ne sme biti prazno.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM Izvajalci WHERE Ime = ?");
        mysqli_stmt_bind_param($stmt, "s", $ime_izvajalca);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $st_vrstic = mysqli_stmt_num_rows($stmt);

        if ($st_vrstic > 0) {
            $sporocilo_izvajalec = "Ta izvajalec že obstaja.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "INSERT INTO Izvajalci (Ime, Opis) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $ime_izvajalca, $opis_izvajalca);
            if (mysqli_stmt_execute($stmt)) {
                $sporocilo_izvajalec = "Izvajalec uspešno dodan!";
            } else {
                $sporocilo_izvajalec = "Napaka pri dodajanju izvajalca.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Obdelava obrazca za dodajanje albuma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_album'])) {

    $ime_albuma = isset($_POST['ime_albuma']) ? trim($_POST['ime_albuma']) : "";
    $opis_albuma = isset($_POST['opis_albuma']) ? trim($_POST['opis_albuma']) : "";
    $izvajalec_id = isset($_POST['izvajalci_album']) ? (int)$_POST['izvajalci_album'] : 0;

    if ($ime_albuma == "" || $izvajalec_id == 0) {
        $sporocilo_album = "Vnesi ime albuma in izberi izvajalca.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Albumi (Ime, Opis) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $ime_albuma, $opis_albuma);

        if (mysqli_stmt_execute($stmt)) {
            $album_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            $stmt2 = mysqli_prepare($conn, "INSERT INTO album_izvajalci (album_id, izvajalec_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, "ii", $album_id, $izvajalec_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            $sporocilo_album = "Album uspešno dodan!";
        } else {
            $sporocilo_album = "Napaka pri dodajanju albuma.";
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8" />
    <title>Admin Panel - Dodaj pesem, izvajalca in album</title>
    <link rel="stylesheet" href="glavna.css" />
</head>
<body>

<div id="nazaj_gumb">
    <h2><a href="admin_glavna.php" id="nazajtext">Nazaj</a></h2>
</div>

<div id="dodaj_pesem">
    <h1>Dodaj novo pesem</h1>

    <?php
    if ($sporocilo != "") {
        echo '<p style="color: green;">' . htmlspecialchars($sporocilo) . '</p>';
    }
    ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="dodaj_pesem" value="1" />

        <label>Ime pesmi: <input type="text" name="ime" required /></label><br /><br />
        <label>Leto izdaje: <input type="text" name="leto_izdaje" required /></label><br /><br />
        <label>Dolžina (hh:mm:ss): <input type="text" name="dolzina" required /></label><br /><br />

        <label>MP3 datoteka: <input type="file" name="mp3" accept=".mp3" required /></label><br /><br />
        <label>Slika pesmi: <input type="file" name="slika" accept="image/*" required /></label><br /><br />

        <label>Izvajalec:
            <select name="izvajalec" required>
                <option value="">izberi izvajalca</option>
                <?php
                foreach ($izvajalci as $iz) {
                    echo '<option value="' . $iz['id'] . '">' . htmlspecialchars($iz['Ime']) . '</option>';
                }
                ?>
            </select>
        </label><br /><br />

        <label>Žanr:
            <select name="zanr" required>
                <option value="">izberi žanr</option>
                <?php
                foreach ($zanri as $z) {
                    echo '<option value="' . $z['id'] . '">' . htmlspecialchars($z['Ime']) . '</option>';
                }
                ?>
            </select>
        </label><br /><br />

        <label>Album:
            <select name="album">
                <option value="">izberi album</option>
                <?php
                foreach ($albumi as $album) {
                    echo '<option value="' . $album['id'] . '">' . htmlspecialchars($album['Ime']) . '</option>';
                }
                ?>
            </select>
        </label><br /><br />

        <button type="submit">Dodaj pesem</button>
    </form>
</div>

<div id="dodaj_izvajalca" style="margin-top:50px;">
    <h1>Dodaj novega izvajalca</h1>

    <?php
    if ($sporocilo_izvajalec != "") {
        echo '<p style="color: green;">' . htmlspecialchars($sporocilo_izvajalec) . '</p>';
    }
    ?>

    <form method="post">
        <input type="hidden" name="dodaj_izvajalec" value="1" />

        <label>Ime izvajalca: <input type="text" name="ime_izvajalca" required /></label><br /><br />
        <label>Opis izvajalca: <input type="text" name="opis_izvajalca" placeholder="Vnesite opis izvajalca" /></label><br /><br />

        <button type="submit">Dodaj izvajalca</button>
    </form>
</div>

<div id="dodaj_album" style="margin-top:50px;">
    <h1>Dodaj nov album</h1>

    <?php
    if ($sporocilo_album != "") {
        echo '<p style="color: green;">' . htmlspecialchars($sporocilo_album) . '</p>';
    }
    ?>

    <form method="post
