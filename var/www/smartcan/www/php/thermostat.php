<?php

// Battery Level: https://benohead.com/blog/2014/10/04/html5-displaying-battery-level/

// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// SELECT * FROM `chauffage_sonde` JOIN `chauffage_temp` ON `chauffage_temp`.`id`=`chauffage_sonde`.`id`
  //$titre = 'Gestion du chauffage';

  //include '../class/class.envoiTrame.php5';

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'descendreTemperature');
  $xajax->register(XAJAX_FUNCTION, 'monterTemperature');
  $xajax->register(XAJAX_FUNCTION, 'moyenne');
  $xajax->register(XAJAX_FUNCTION, 'updateConsigne');
  $xajax->register(XAJAX_FUNCTION, 'updateConsigneMini');
  $xajax->register(XAJAX_FUNCTION, 'autoAway');
  $xajax->register(XAJAX_FUNCTION, 'autoBack');
  $xajax->register(XAJAX_FUNCTION, 'HeatNow');
 
   if (isset($_GET['zone'])) { $heatzone=$avg=$_GET['zone']; } else { $heatzone="0"; $avg="1"; }
   moyenne();
   $Actionval="";
  /* FONCTIONS PHP AJAX */
  function moyenne() {
	global $heatzone,$avg;
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    // If new value receved => Refresh Interface (DIV IDs)
    $objResponse = new xajaxResponse();
	// Calculate MAX value measurement interval
	if ($avg!=1) {
	  $sql = "SELECT * FROM `chauffage_clef` WHERE `clef`='zWaveDelay';";
	  $return = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($return, MYSQLI_BOTH);
	  $zWaveDelay = $row["valeur"]; if ($zWaveDelay=="") { $zWaveDelay = "5"; }
	  $MaxInterval = strval(intval($zWaveDelay)*2);
	} else {
	  $MaxInterval = "2";
	} // END IF
    $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE (`moyenne` = '".$avg."' AND `valeur`<>0 AND `update`>=DATE_SUB(now(), INTERVAL ".$MaxInterval." MINUTE));");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    $objResponse->assign("moyenne","innerHTML", round($row[0],1));
	//$objResponse->assign("moyenne","innerHTML", round(rand(0,25),1));
	
	// HEATER STATUS
	$heater = ""; $chaudiere = "";
    $retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE 1;");
    while ($row = mysqli_fetch_array($retour, MYSQLI_BOTH)) {
      if ($row['clef']=="boiler") {    if ($row['valeur'] == "0" ) { $heater = "OFF";   } 
	    else if ( $row['valeur'] == "1" ) { $heater    = "BOILER";} } // END IF
	  if ($row['clef']=="chaudiere") { if ($row['valeur'] == "0" ) { $chaudiere = "OFF";} 
	    else if ( $row['valeur'] == "1" ) { $chaudiere = "HEATER";} } // END IF
    } // END WHILE
	if ($chaudiere=="OFF" && $heater=="BOILER") { $chaudiere = "BOILER"; } // ENDIF
	$objResponse->assign("divchaudiere","innerHTML", $chaudiere);
	
	// ABSENCE STATUS
	$retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'absence';");
    $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
	$objResponse->assign("divabsence","innerHTML", $row[0]);
  
	// Close DB
	mysqli_close($DB);
	// Return Object
    return $objResponse;    
  }

  // Update consigne depuis Nest
   function updateConsigne($newTemp) {
	global $heatzone;
    $objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    mysqli_query($DB ,"UPDATE `" . TABLE_CHAUFFAGE_CLEF. "` SET `valeur` = '" . $newTemp . "' WHERE (`ZoneNber`='".$heatzone."'  AND `clef` = 'temperature');");
    mysqli_close($DB);
    $objResponse->assign("consigneconfort","innerHTML", $newTemp);
	//sleep(10);
	exec('php /data/www/smartcan/bin/chauffage.php');
    return $objResponse;    
  }
 
  // Update Temperature Minimum depuis Nest
   function updateConsigneMini($newTemp) {
    global $heatzone;
	$objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $newTemp . "' WHERE (`ZoneNber`='".$heatzone."'  AND `clef` = 'tempminimum');");
    mysqli_close($DB);
    $objResponse->assign("consigneminimum","innerHTML", $newTemp);
	//sleep(10);
	exec('php /data/www/smartcan/bin/chauffage.php');
    return $objResponse;    
  }
  
   // auto AWAY depuis Nest
   function autoAway() {
    $objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '1' WHERE `clef` = 'absence';");
	// Delete any Heat Now
    $sql = "DELETE FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `days` = '00000001';";
    mysqli_query($DB,$sql);
	mysqli_close($DB);
	exec('php /data/www/smartcan/bin/chauffage.php');
    return $objResponse;    
  }  
 
   // auto BACK depuis Nest
   function autoBack() {
     $objResponse = new xajaxResponse();
     $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
     mysqli_select_db($DB,mysqli_DB);
     mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '0' WHERE `clef` = 'absence';");
     mysqli_close($DB);
	 exec('php /data/www/smartcan/bin/chauffage.php');
     return $objResponse;    
   }
 
   // HEAT Now depuis Nest
   function HeatNow($Laps) {
    $objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $Now    = date("H:i:00");
    $End    = date("H:i:00",mktime(date("H")+$Laps, date("i"), 0, date("m"), date("d"), date("y")));
    $sql    = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` SET `days` = '00000001', `start`='" . $Now . "', `stop`='" . $End . "', `active`='Y';";
    mysqli_query($DB,$sql);
	exec('php /data/www/smartcan/bin/chauffage.php');
    return $objResponse;    
  }
  
  /* CONNEXION SQL */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);
  
  /* Search for Heating Zones */
  $return = mysqli_query($DB,"SELECT Count(*) AS County FROM `ha_thermostat_zones` WHERE `Name`<>'';");
  $row    = mysqli_fetch_array($return, MYSQLI_BOTH);
  $heatzone = "0";
  $heatzonecolor = "000000";
  if ($row["County"]!=0) {
    $Actionval    = '  window.open("./?page='.$_GET['page'].'&theme='.$_GET['theme'].'&zone="+document.getElementById("selZone").selectedIndex,"_self");';

	$val = '<form id="bottompage">';
	$val.= '<input type="hidden" id="DropSelectedZone" name="DropSelectedZone" value="';
	$defaultSel = '';
	if (isset($_GET['zone'])) { $val.=$_GET['zone']; if ($_GET['zone']=="1") { $defaultSel=" selected";} } else { $val.="1"; $defaultSel=" selected";}
	$val.= '">';
	$val.= '<select id="selZone" name="selZone"><option value="0">Select Other Zone:</option>';
	$val.= '<option value="1"'.$defaultSel.'>Zone Principale</option>';
	$return = mysqli_query($DB,"SELECT * FROM `ha_thermostat_zones`;");
    while ($row = mysqli_fetch_array($return, MYSQLI_BOTH)) {
	  $val .= '<option value="'.$row["ZoneNber"].'" ';
	  if (isset($_GET['zone'])) { if ($_GET['zone']==$row["ZoneNber"]) { $val.= 'selected'; $heatzone=$row["ZoneNber"]; $heatzonecolor=$row["Color_Code"]; }}
	  $val .= '>'.$row["Name"].'</option>';
	} // END WHILE
    $val .= '</select></form>' .CRLF;

    // Determine if Sensors are Battery Powered => Display level
	$heatZ = $heatzone; if ($heatZ==0) { $heatZ=1; }
	$sql = "SELECT COUNT(*) AS County FROM `chauffage_temp` WHERE `battery`<101 and `moyenne`='".$heatZ."';";
	$return = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($return, MYSQLI_BOTH);
	if ($row['County']>0) {
	  //$sql = "SELECT * FROM `chauffage_temp` WHERE `battery`<101 and `moyenne`='".$heatzone."';";
	  // SELECT `chauffage_temp`.`battery` AS Bat, `chauffage_sonde`.`description` AS Descrip FROM `chauffage_temp`,`chauffage_sonde` WHERE `chauffage_temp`.`id`=`chauffage_sonde`.`id` AND `chauffage_temp`.`battery`<101 and `chauffage_temp`.`moyenne`='3'
	  $sql = "SELECT `chauffage_temp`.`battery` AS Bat, `chauffage_sonde`.`description` AS Descrip FROM `chauffage_temp`,`chauffage_sonde` " .
				"WHERE `chauffage_temp`.`id`=`chauffage_sonde`.`id` AND `chauffage_temp`.`battery`<101 and `chauffage_temp`.`moyenne`='".$heatZ."';";
	  $return = mysqli_query($DB,$sql);
	  $val .= '</div><div><table><tr>';
	  while ($row = mysqli_fetch_array($return, MYSQLI_BOTH)) {
	    $bat  = $row['Bat'];
		$desc = $row['Descrip'];
		if ($bat>=60) { $batColor = '#53a600'; } else { if ($bat>=40) { $batColor = '#d18034'; } else { $batColor = '#FF3333'; } }

        //$bat = 10;
        //$batColor = '#FF3333'; // LOW bat = #FF3333 , Medium = #FCD116 , HIGH = #66CD00
        $val .= '<td width="50%"><div style="position: relative; text-align: center; color: white;" id="battery"><img src="images/battery-empty.png" width=51px height=28px/>' .
					'<div style="position: absolute; top: 50%; left: ' . strval(8+($bat*0.4)) . '%;  transform: translate(-50%, -50%); color:white;background-color: ' . $batColor . '; font-size: 15px; width: ' .
					strval(10+($bat*0.8)) . '%" id="battery-level"><a smartcan-title="' . $desc . '">' . $bat . '%</a></div></div></td>';

/*
        $bat = 100;
        $batColor = '#66CD00';
        $val .= '<td width="50%"><div style="position: relative; text-align: center; color: white;" id="battery"><img src="images/battery-empty.png" width=51px height=28px/>' .
					'<div style="position: absolute; top: 50%; left: '.strval(8+($bat*0.4)).'%;  transform: translate(-50%, -50%); color:white;background-color: red; font-size: 15px; width: ' .
					strval(10+($bat*0.8)).'%" id="battery-level"><a smartcan-title="Salon">'.$bat.'%</a></div></div></td>';
*/
	  } // END WHILE
      $val .= '</tr></table>' . CRLF;
	} // END IF
     	
  } else {
    $val = '';
  }

  $_XTemplate->assign('HEATZONES', $val);
  $_XTemplate->assign('ZONEACTION', $Actionval);
  $_XTemplate->assign('HEATZONE', $heatzone);
  $_XTemplate->assign('HEATZONECOLOR', $heatzonecolor);


  /* Display Average Temperature of House / Zone */
  // Calculate MAX value measurement interval
  if ($heatzone!="0") {
    $avg = $heatzone;
    $sql = "SELECT * FROM `chauffage_clef` WHERE `clef`='zWaveDelay';";
    $return = mysqli_query($DB,$sql);
    $row = mysqli_fetch_array($return, MYSQLI_BOTH);
    $zWaveDelay = $row["valeur"]; if ($zWaveDelay=="") { $zWaveDelay = "5"; }
    $MaxInterval = strval(intval($zWaveDelay)*2);
  } else {
    $avg="1";
    $MaxInterval = "2";
  } // END IF
  $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE (`moyenne` = '".$avg."' AND `valeur`<>0 AND `update`>=DATE_SUB(now(), INTERVAL ".$MaxInterval." MINUTE));");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('MOYENNEMAISON', round($row[0],1));

  /* AFFICHAGE DE LA TEMPERATURE VOULUE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature' AND `ZoneNber` = '".$heatzone."';");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('TEMPERATURE', $row[0]);

    /* AFFICHAGE DE LA TEMPERATURE MINIMUM */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'tempminimum' AND `ZoneNber` = '".$heatzone."';");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('TEMPMINIMUM', $row[0]);
  
  /* AFFICHAGE DE L'ABSENCE [PRESENCE-1] */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'absence';");
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('ABSENCE', $row[0]);
  
  // HEATER STATUS
  $heater = "";
  $retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE 1;");
  while ($row = mysqli_fetch_array($retour, MYSQLI_BOTH)) {
    if ($row['clef']=="boiler") {    if ($row['valeur'] == "0" ) { $heater = "OFF";   } 
	  else if ( $row['valeur'] == "1" ) { $heater    = "BOILER";} } // END IF
	if ($row['clef']=="chaudiere") { if ($row['valeur'] == "0" ) { $chaudiere = "OFF";} 
	  else if ( $row['valeur'] == "1" ) { $chaudiere = "ON";} } // END IF
	if ($chaudiere=="OFF" && $heater=="BOILER") { $chaudiere = "BOILER"; } // ENDIF
    } // END WHILE
	$_XTemplate->assign('CHAUDIERE', $chaudiere);
	
  /* PRERIODE DE CHAUFFE? */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $Zone   = str_pad(str_pad("1",intval($heatzone),"_",STR_PAD_LEFT),7,"_");
  $sql    = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND (`zones` LIKE '".$Zone."') AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour = mysqli_query($DB,$sql);
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('PERIODECHAUFFE', $row[0]); //
  
  /* AFFICHAGE DE FIN DE LA PERIODE DE CHAUFFE EN COURS */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT stop FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND (`zones` LIKE '".$Zone."')  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y' ORDER BY start DESC;";
  $retour = mysqli_query($DB,$sql);
  if ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
    $heure = substr($row[0],0,2) . substr($row[0],3,2);
    $_XTemplate->assign('FINCHAUFFE', $heure);
  } else {
    $_XTemplate->assign('FINCHAUFFE', "");
  }

  /* AFFICHAGE DE LA PROCHAINE PERIODE DE CHAUFFE */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND (`zones` LIKE '".$Zone."') AND (`days` LIKE '" . $Today . "') AND (`start`>'" . $Now . "') AND `active`='Y' ORDER BY `start`;";
  
  $retour = mysqli_query($DB,$sql);
  if ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
	if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . substr($row[0],3,2);}
    $_XTemplate->assign('PROCHAINECHAUFFE', $heure);
	$_XTemplate->assign('DD'  , "8"); // Today;-)
  } else {
    $DayBit   = date("N",mktime(1, 1, 1, date("m"), date("d")+1, date("y")));
    $Tomorrow = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
    $sql      = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE (`function`='HEATER' AND (`zones` LIKE '".$Zone."') AND (`days` LIKE '" . $Tomorrow . "') AND `active`='Y') ORDER BY `start`;";
    $retour   = mysqli_query($DB,$sql);
	$row=mysqli_fetch_array($retour, MYSQLI_BOTH);
	if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . substr($row[0],3,2);}
    $_XTemplate->assign('PROCHAINECHAUFFE', $heure);
	$_XTemplate->assign('DD'  , $DayBit);
  } // ENDIF
  
  // Hour & Day
  $_XTemplate->assign('HOUR', date("H"));
  //$_XTemplate->assign('DD'  , date("N"));
  

  /* AFFICHAGE TEMPERATURE EXTERIEURE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('TEMPERATUREEXTERIEURE', round($row[0], 1));

  /* FERMETURE SQL */
  mysqli_close($DB);

?>
