<?php
// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');


// Load Dependencies
include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

/* CONNEXION SQL */
$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_select_db($DB,mysqli_DB);

// Get var values:
$request = print_r( $_REQUEST, true );
	
if ($request!="") {
  //$file = $_SERVER['DOCUMENT_ROOT'].'/smartcan/webhook/test-ESP.txt';
  // Open the file to get existing content
  //$current = file_get_contents($file);
  
  $Next_Temp = 0;
  $Temperature = 0;
  $Sensor = "";
  foreach ($_GET as $key=>$value) {
    //$current .= "$key = " . urldecode($value) . "\n";
	$val = urldecode($value);
	if ($key=="task") { $Sensor = $val; }
	if (($Next_Temp==1) && ($key=="value"))  {
		$Temperature = $val;
		//$current .= "\n" . date("H:i:s.u") . " - Sensor=" . $Sensor . ", Temperature=" . $Temperature . "\n";
		// Updates DB with Temperature Value
		$sql = "UPDATE `chauffage_temp` AS Temp, `chauffage_sonde`AS Sensor SET Temp.`valeur` = '".$Temperature."', Temp.`update` = NOW() WHERE (Temp.`id` = Sensor.`id` AND Sensor.`description`='".$Sensor."');";
		$query=mysqli_query($DB,$sql);
		//$current .= $sql . "\n\n";
		$Next_Temp = 0;
    }
    if ((urldecode($value)=="Temperature") && ($key=="valuename")) { $Next_Temp = 1; }
  } 
  
  //$current .= $request;
  

  // Write the contents back to the file
  //file_put_contents($file, $current);
} // END IF
?>