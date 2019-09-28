<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Includes
include_once('./smartcan/www/conf/config.php');

/* CONNEXION */
$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_select_db($DB,mysqli_DB);

$sql = "SELECT * FROM `ha_settings` WHERE `variable`='dump_1090_srv';";
$retour = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
$server = $row["value"];

//echo("OK");
include($_SERVER['DOCUMENT_ROOT'].'/smartcan/bin/Plane-Track.php');
echo("<br>".detect_plane($server,1));

?>
