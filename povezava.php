<?php

/*
povezava na streznik
*/

$host='localhost';
$user='root';
$password='root';
$database='projekt';

$link=mysqli_connect($host, $user, $password, $database)
or die("Povezovanje ni mogoče");
mysqli_set_charset($link, "utf8");
?>