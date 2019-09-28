<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Includes
include_once('../www/conf/config.php');
include_once('../class/class.triggers.php5');


// Connect DB
$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_set_charset($DB,'utf8'); 
mysqli_select_db($DB,mysqli_DB);


// Decode DialogFlow JSON
header('Content-Type: application/json');
ob_start();
$outputtext = "";
$json = file_get_contents('php://input'); 
$request = json_decode($json, true);
$lang       = substr($request["queryResult"]["languageCode"],0,2);
$action = $request["queryResult"]["action"];
$userID = $request["originalDetectIntentRequest"]["payload"]["user"]["userId"];
$parameters = $request["queryResult"]["parameters"];

// Output in text file (debug)
$file = $_SERVER['DOCUMENT_ROOT'].'/smartcan/webhook/dialowflow.txt';
// Open the file to get existing content
//$current = file_get_contents($file);
$current = "Lang = ".$lang.", Action = ".$action.", UserID = ".$userID."/n<br>/n";

// Action = TRAIN
if ($action=="TRAIN") {
	include_once('../www/lang/www.trains.php');
	$output = "";
	// Determine Variables Values
	$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainShowStations';";
	$retour = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
	$trainShowStations = $row["value"];
	$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainSwitchAfterNoon';";
	$retour = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
	$trainSwitchAfterNoon = $row["value"];

	$url = "https://api.irail.be/connections/";
	$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainDeparture';";
	$retour = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
	if ((date("Hi")>"1200") && ($trainSwitchAfterNoon=="Y")) { $trainDestination = $row["value"]; } else { $trainDeparture = $row["value"]; }
	$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainDestination';";
	$retour = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
	// AUTO Return ?
	if ((date("Hi")>"1200") && ($trainSwitchAfterNoon=="Y")) { $trainDeparture = $row["value"]; } else { $trainDestination = $row["value"]; }
	// Build URL
	$url .= "?from=" . $trainDeparture . "&to=" . $trainDestination . "&timesel=departure&format=json&lang=fr&fast=false&typeOfTransport=trains&alerts=false&results=5";

	// Parses if values entered
	if ($trainDeparture!="" && $trainDestination!="") {
	  // Connect to SNCBNBMS API
	  // Open the file to get existing content
	  $curl = curl_init();
	  curl_setopt_array($curl, array(CURLOPT_URL => $url,CURLOPT_RETURNTRANSFER => true,CURLOPT_ENCODING => "",CURLOPT_MAXREDIRS => 10,CURLOPT_TIMEOUT => 30,CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,CURLOPT_CUSTOMREQUEST => "GET",CURLOPT_POSTFIELDS => "",CURLOPT_HTTPHEADER => array("cache-control: no-cache"),));
	  $dump = curl_exec($curl);
	  $err = curl_error($curl);
	  curl_close($curl);
	  if ($err) {
		$outputtext = "cURL Error #:" . $err;
	  }
      if (($dump!="") && ($outputtext=="")) {
            // Parses JSON
            $trains = json_decode($dump);
            $count=0;
            foreach ($trains->connection as $value) {
              if($count==0) {
                $count++; echo("Count" . $count . "<br>");
                $start = $value->departure;     $departure  = date('H:i',$start->time);
                $platform = $start->platform;
                $outputtext .= "Le train de " . $departure . ", " . $msg["trains"]["Platform"][$lang] . " " . $platform;
                $depDelay = $start->delay/60;
                if ($depDelay==0) { $outputtext .= " est prÃ©vu Ã  l'heure"; } else { $outputtext .= " sera en retard de " . $depDelay . " minutes!"; }
                $canceled = $start->canceled;
                if ($canceled>0) { $count--; $outputtext = "Le train de " . $departure . " est annulÃ©! Le suivant est "; }
              } // ENDIF count==0
            } // END FOREACH
          } // ENDIF
	} // ENDIF
//  $outputtext = "En retard, comme d'habitude!";
}

// 
// Action = TEMPERATURE
if ($action=="TEMPERATURE") {
  /* AFFICHAGE DE LA TEMPERATURE MOYENNE DE LA MAISON */
  $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1';");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $outputtext = "La tempÃ©rature ambiante est de " . round($row[0],1) . " degrÃ©s.";
//  $outputtext = "21 degrÃ©s";
}
// ACTION AUGMENTER TEMPERATURE
if ($action=="AUGMENTER") {
  $increment = $parameters["INCREMENT"];
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $temp    = $row[0];
  $newTemp = $temp + $increment;
  $retour = mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = " . $newTemp . " WHERE `clef` = 'temperature';");
  $current .= "/n AUGMENTER(".$temp."->".$newTemp."), Increment=" . $increment . " sql = UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = " . $newTemp . " WHERE `clef` = 'temperature';";
  $outputtext = "La nouvelle tempÃ©rature de confort est maintenant " . $newTemp . " degrÃ©s";
}
// ACTION DIMINUER TEMPERATURE
if ($action=="DIMINUER") {
  $increment = $parameters["INCREMENT"];
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $temp    = $row[0];
  $newTemp = $temp - $increment;
  $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = " . $newTemp . " WHERE `clef` = 'temperature';";
  $retour = mysqli_query($DB,$sql);
  $current .= "/n DIMINUER(".$temp."->".$newTemp."), Increment=" . $increment . " sql = " . $sql . "/n ";
  $outputtext = "La nouvelle tempÃ©rature de confort est maintenant " . $newTemp . " degrÃ©s";
}

// Action = ON
if ($action=="ON") {
  // Get Lamp Name
  $lamp = $parameters["LampName"];
  // Debug
  $current .=", LampNam = ".$lamp."\n";
  // Get Lamp parameters from within DB  
  //$sql = "SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `description` = '" . $lamp . "';";
  $sql = "SELECT L.*, LS.valeur FROM `" . TABLE_LUMIERES . "` AS L, `" . TABLE_LUMIERES_STATUS . "` AS LS where L.id=LS.id and `description`='" . $lamp . "'; ";
  $retour = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $outputtext = "I Can't find this lamp :-(";
  $current .=", Manufacturer = ".$row['Manufacturer'].", valeur = ".$row['valeur']."\n";
  if ($row['Manufacturer']!="") {
	if ($row['valeur']!=0) {
	  $outputtext = "Already ON";
	} else {
      $grad_FullName = $row['Manufacturer'];
      include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
      include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
      $grad_FullName = $grad_FullName . "_gradateur";
      $gradateur = new $grad_FullName();
      $gradateur->inverser($row['carte'], $row['sortie'], $row['delai'], hexdec($row['valeur_souhaitee']));
	  $outputtext = "OK, done!";
	} // END IF
  } // End IF
} // END IF

//Action = OFF
if ($action=="OFF") {
  // Get Lamp Name
  $lamp = $parameters["LampName"];
  // Debug
  $current .=", LampNam = ".$lamp."\n";
  // Get Lamp parameters from within DB  
  //$sql = "SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `description` = '" . $lamp . "';";
  $sql = "SELECT L.*, LS.valeur FROM `" . TABLE_LUMIERES . "` AS L, `" . TABLE_LUMIERES_STATUS . "` AS LS where L.id=LS.id and `description`='" . $lamp . "'; ";
  $retour = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $outputtext = "Cann't find this lamp :-(";
  $current .=", Manufacturer = ".$row['Manufacturer'].", valeur = ".$row['valeur']."\n";
  if ($row['Manufacturer']!="") {
	if ($row['valeur']==0) {
	  $outputtext = "Already OFF";
	} else {
      $grad_FullName = $row['Manufacturer'];
      include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
      include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
      $grad_FullName = $grad_FullName . "_gradateur";
      $gradateur = new $grad_FullName();
      $gradateur->inverser($row['carte'], $row['sortie'], $row['delai'], hexdec($row['valeur_souhaitee']));
	  $outputtext = "OK, done!";
	} // END IF
  } // End IF
} // END IF

//Action = STATUS
if ($action=="STATUS") {
	$msg = "";
	// Get Object Name
	$Object = $parameters["Object"];
	// Debug
	$current .=", Object = ".$Object;

	// HEATER STATUS
	$heater = "";
	$retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE 1;");
	while ($row = mysqli_fetch_array($retour, MYSQLI_BOTH)) {
	  if ($row['clef']=="boiler") {    if ($row['valeur'] == "0" ) { $heater = "OFF";   } 
		else if ( $row['valeur'] == "1" ) { $heater    = "BOILER";} } // END IF
	  if ($row['clef']=="chaudiere") { if ($row['valeur'] == "0" ) { $chaudiere = "OFF";} 
		else if ( $row['valeur'] == "1" ) { $chaudiere = "ON";} } // END IF
	  $msg = "Heater " . $chaudiere;
	  if ($chaudiere=="OFF" && $heater=="BOILER") { $chaudiere = "BOILER"; $msg = "Boiler ON"; } // ENDIF
	} // END WHILE
	//$objResponse->assign("divchaudiere","innerHTML", $chaudiere);
	//$_XTemplate->assign('CHAUDIERE', $chaudiere);
	$current .=", Chaudiere = ".$chaudiere."\n";
		
	/* PRERIODE DE CHAUFFE? */
	$Now    = date("H:i:00");
	$DayBit = date("N");
	$Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
	$sql    = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
	$retour = mysqli_query($DB,$sql);
	$row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
	//$_XTemplate->assign('PERIODECHAUFFE', $row[0]);
	$current .=", Periode Chauffe = ".$row[0];
	  
	/* AFFICHAGE DE FIN DE LA PERIODE DE CHAUFFE EN COURS */
	$Now    = date("H:i:00");
	$DayBit = date("N");
	$Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
	$sql    = "SELECT stop FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y' ORDER BY start DESC;";
	$retour = mysqli_query($DB,$sql);
	if ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
	  $heure = substr($row[0],0,2) . ":" . substr($row[0],3,2);
	  //$_XTemplate->assign('FINCHAUFFE', $heure);
	  $current .=", Fin Chauffe = ".$heure;
	  $msg .= ", \nEnds at " . $heure;
	} else {
	  //$_XTemplate->assign('FINCHAUFFE', "");
	  $current .=", Fin Chauffe = ";
	}

	/* AFFICHAGE DE LA PROCHAINE PERIODE DE CHAUFFE */
	$Now    = date("H:i:00");
	$DayBit = date("N");
	$Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
	$sql    = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND (`days` LIKE '" . $Today . "') AND (`start`>'" . $Now . "') AND `active`='Y' ORDER BY `start`;";
	  
	$retour = mysqli_query($DB,$sql);
	if ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
	  if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . ":" . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . ":" . substr($row[0],3,2);}
	  //$_XTemplate->assign('PROCHAINECHAUFFE', $heure);
	  $current .=", Prochaine Chauffe = ".$heure;
	  $msg .= ", \nNext Heating: ".$row[0];
	  //$_XTemplate->assign('DD'  , "8"); // Today;-)
	} else {
	  $DayBit   = date("N",mktime(1, 1, 1, date("m"), date("d")+1, date("y")));
	  $Tomorrow = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
	  $sql      = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE (`function`='HEATER'  AND (`days` LIKE '" . $Tomorrow . "') AND `active`='Y') ORDER BY `start`;";
	  $retour   = mysqli_query($DB,$sql);
	  $row=mysqli_fetch_array($retour, MYSQLI_BOTH);
	  if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . ":" . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . ":" . substr($row[0],3,2);}
	  //$_XTemplate->assign('PROCHAINECHAUFFE', $heure);
	  $current .=", Prochaine Chauffe = ".$heure;
	  //$_XTemplate->assign('DD'  , $DayBit);
	  $current .=", Day = ".$DayBit;
	  $msg .= ", \nNext Heating Tomorrow: ".$heure;
	} // ENDIF
	  
	// Hour & Day
	//$_XTemplate->assign('HOUR', date("H"));
	$current .=", Hour = ".date("H");
	//$_XTemplate->assign('DD'  , date("N"));
	 

	/* AFFICHAGE TEMPERATURE EXTERIEURE */
	$retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
	$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
	//$_XTemplate->assign('TEMPERATUREEXTERIEURE', round($row[0], 1));
	$current .=", Exterior Temperature = ".round($row[0], 1)."\n";
	$msg .= ", \nExterior temperature: " . round($row[0], 1) . "Â°C";


	/* AFFICHAGE DE LA TEMPERATURE VOULUE */
	$retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
	$row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
	//$_XTemplate->assign('TEMPERATURE', $row[0]);
	$msg = "Set to " . $row[0] . "Â°C\n" . $msg;
  
	/* AFFICHAGE DE LA TEMPERATURE MOYENNE DE LA MAISON */
	$retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1';");
	$row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
	//$_XTemplate->assign('MOYENNEMAISON', round($row[0],1));
	$msg = "Ambiant Temperature " . round($row[0],1) . "Â°C\n" . $msg;  
  
	// Outpur Message
	$outputtext = $msg; //"Test Status OK!";
} // END IF

// Detects a plane above house
if ($action=="WhichPlane") {
  include_once('./Plane-Track.php');
  $outputtext = detect_plane();
} // END IF

// No answer
if ($outputtext=="") {
  $outputtext = "DÃ©solÃ©, je ne suis pas en mesure de rÃ©pondre Ã  votre quetion!"; //$outputtext = " Sorry, I cannot answer your request!";
}

//$outputtext  = "Ok, done!";
$nextcontext = "";
$param1      = "";
$param2      = "";

//$output["fulfillmentMessages"] = array(array("name" => "$next-context", "parameters" =>array("param1" => $param1value, "param2" => $param2value)));
$output["fulfillmentText"] = $outputtext;
//$output["displayText"] = $outputtext;
$output["source"] = "DialogFlow-Webhook.php";
ob_end_clean();
echo json_encode($output);

  //$file = '/var/www/smartcan/webhook/dialowflow.txt';
  // Open the file to get existing content
  //$current = file_get_contents($file);
  // Append a new person to the file
  $current .= $json; //"Action =".$action.", parameters =".$parameters["lamp"]."\n";
  // Write the contents back to the file
  file_put_contents($file, $current);
?>