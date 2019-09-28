<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);

// Includes
include_once($base_URI.'/www/smartcan/www/conf/config.php');
include_once(PATHCLASS.'class.triggers.php5');

// Parameters Passed within URL
$GPIOpin = html_get("GPIOpin");
$Value = html_get("Value");

// Connect DB
$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_set_charset($DB,'utf8'); 
mysqli_select_db($DB,mysqli_DB);

// GPIO Defined as Output?
$sql = "SELECT count(*) AS County FROM `ha_element` WHERE `Manufacturer`='wiringPI' AND `element_type`='0x12' AND `element_reference`='".$GPIOpin."';";
$query=mysqli_query($DB,$sql);
$row = mysqli_fetch_array($query, MYSQLI_BOTH);
if ($row["County"]==0) { echo("ERROR: Incorrect GPIO!"); exit(); }

// Correct Value?
if (($Value!="ON") && ($Value!="OFF")) { echo("ERROR: Incorrect Value!"); exit(); }

$intensity="0x0"; if ($Value=="ON") { $intensity="0x32"; }

// Change GPIO Value
//echo("GPIO: $GPIOpin, Value = $Value");
$retstatus = exec('sudo gpio -1 write $GPIOpin $Value', $Output, $retval);
// Return Status if OK
if ($retstatus=="") {
  echo("OK");
  $trigger = new trigger();
  $trigger->OUTtrigger("wiringPI", "RaspBerryPI", $GPIOpin, $intensity);
} // END IF


function html_get($in) {
$out = "";
if (isset($_GET[$in])) { $out = $_GET[$in]; }
return $out;
} // End Function html_post
?>
