<?php
/*
Povezava na server
htmlspecialchar() je za pretvarjanje posebnih znakov v varne html entitete
*/

$host='localhost';
$user='root';
$password='root';
$database='database';

$link=mysqli_connect($host, $user, $password, $database)
or die("Povezovanje ni mogoče");
mysqli_set_charset($link, "utf8");
?>