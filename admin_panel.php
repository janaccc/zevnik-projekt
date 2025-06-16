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

// Obdelava obrazca
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = $_POST['ime'] ?? '';
    $leto = $_POST['leto_izdaje'] ?? '';
    $dolzina = $_POST['dolzina'] ?? '';
    $izvajalec_id = (int)($_POST['izvajalec'] ?? 0);

$audio_path = '';
if (isset($_FILES['mp3']) && $_FILES['mp3']['error'] === 0) {
    if (!is_dir('pesmi')) {
        mkdir('pesmi', 0777, true);
    }
    $filename = basename($_FILES['mp3']['name']);
    // Lahko dodamo sanacijo imena, npr. odstranjevanje presledkov:
    $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
    $audio_path = 'pesmi/' . $filename;
    move_uploaded_file($_FILES['mp3']['tmp_name'], $audio_path);
}

// Naloži sliko
$slika_path = '';
if (isset($_FILES['slika']) && $_FILES['slika']['error'] === 0) {
    if (!is_dir('slike_pesmi')) {
        mkdir('slike_pesmi', 0777, true);
    }
    $filename = basename($_FILES['slika']['name']);
    $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
    $slika_path = 'slike_pesmi/' . $filename;
    move_uploaded_file($_FILES['slika']['tmp_name'], $slika_path);
}

    // Vstavi pesem v bazo z uporabo polj pod_do_pesmi in pot_do_slike
    $stmt = mysqli_prepare($conn, "
        INSERT INTO Pesmi (Ime, leto_izdaje, Dolzina, pod_do_pesmi, pot_do_slike)
        VALUES (?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, 'sssss', $ime, $leto, $dolzina, $audio_path, $slika_path);
    mysqli_stmt_execute($stmt);
    $pesem_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Poveži izvajalca
    if ($pesem_id && $izvajalec_id) {
        $stmt2 = mysqli_prepare($conn, "
            INSERT INTO pesem_izvajalci (pesem_id, izvajalec_id)
            VALUES (?, ?)
        ");
        mysqli_stmt_bind_param($stmt2, 'ii', $pesem_id, $izvajalec_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }

    $sporocilo = "Pesem uspešno dodana!";
}

// Pridobi seznam izvajalcev
$izvajalci = [];
$res = mysqli_query($conn, "SELECT id, Ime FROM Izvajalci ORDER BY Ime ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $izvajalci[] = $row;
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Dodaj pesem</title>
    <link rel="stylesheet" href="glavna.css">
</head>
<body>
<div id="dodaj_pesem">
    <h1>Dodaj novo pesem</h1>

    <?php if (!empty($sporocilo)): ?>
        <p style="color: green;"><?= htmlspecialchars($sporocilo) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Ime pesmi: <input type="text" name="ime" required></label><br><br>
        <label>Leto izdaje: <input type="text" name="leto_izdaje" required></label><br><br>
        <label>Dolžina (ure:minute:sekunde): <input type="text" name="dolzina" required></label><br><br>

        <label>MP3 datoteka: <input type="file" name="mp3" accept=".mp3" required></label><br><br>
        <label>Slika pesmi: <input type="file" name="slika" accept="image/*" required></label><br><br>

        <label>Izvajalec:
            <select name="izvajalec" required>
                <option value="">-- izberi izvajalca --</option>
                <?php foreach ($izvajalci as $iz): ?>
                    <option value="<?= $iz['id'] ?>"><?= htmlspecialchars($iz['Ime']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Dodaj pesem</button>
        <h2><a href="admin_glavna.php">Nazaj</a></h2>
    </form>
</div>
</body>
</html>
