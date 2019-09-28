<?php
// Tasks run evry 5 minutes

// Includes
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
include_once $base_URI.'/www/smartcan/www/conf/config.php';

// Connect DB
$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_set_charset($DB,'utf8'); 
mysqli_select_db($DB,mysqli_DB);

// Remove UnDeleted One Time Heating Requests
// date("d-m-Y H:i:s", time()+3600) '".date("Y-m-d H:i:s", time()+3600)."'
$sql = "DELETE FROM `ha_cameras_temp` WHERE `Create_Date` < (NOW() - INTERVAL 5 MINUTE);";
$query = mysqli_query($DB,$sql);
//echo("Camera Reverse Proxy Configs deleted! " . $query.chr(10));

/* Delete “HEAT Now” when done */
$sql   = "DELETE FROM ". TABLE_HEATING_TIMSESLOTS . " WHERE `function`='HEATER' AND `days`='00000001' AND `stop`< (NOW() - INTERVAL 5 MINUTE);";
$query = mysqli_query($DB,$sql);
 
$sql   = "DELETE FROM ". TABLE_HEATING_TIMSESLOTS . " WHERE `function`='HEATER' AND `days`='00000001' AND `start`> (NOW() + INTERVAL 5 MINUTE);";
$query = mysqli_query($DB,$sql);

?>
