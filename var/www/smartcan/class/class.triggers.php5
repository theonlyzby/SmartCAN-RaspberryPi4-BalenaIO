<?php

// Includes $msg["heater"]["AlertTitle"]["en"]


class trigger {

  // Send Notification on Progressive Web App application (User screen)
  function PWA_notify($OEM, $Title, $Body, $Body2) {
	global $DB;
	global $base_URI;
	include_once($base_URI . '/www/smartcan/class/lang.triggers.php');
	// Output in text file (debug)
	$file = './PWAnotifications.txt';
	// Open the file to get existing content
	$current = file_get_contents($file);
	
	// Parse DB to find active users, their lang & Firebase Token
	$sql = "SELECT * FROM `users_notification`;";
	$return = mysqli_query($DB, $sql);
	$base_curl = "curl -X POST -H \"Authorization: key=AAAAGAKq-Y4:APA91bH9gphJptTwGpiQ32cHpldseJMsRWCV6jdyAB-ESHX4Vxs3XEmABzwz7Im7QD0SBCVvQeJRxgdbmsm3KGZwRaLnA8vzBIkNz3wbFO4L55x2KTFTdO6O03UwIv1RowqKVY36dTuO\" " .
					"-H \"Content-Type: application/json\" -d '{\"data\": {\"notification\": {";
	while ($row = mysqli_fetch_array($return, MYSQLI_BOTH)) {
	  // Send ALERT Notification
	  $curl = $base_curl . "\"title\": \"" . $msg["PWAnotification"][$Title][$row["Lang"]] . "\", " .
					"\"body\": \"" . $msg["PWAnotification"][$Body][$row["Lang"]] . $Body2 . "\", " .
					"\"icon\": \"/smartcan/www/images/icons/icon-192x192.png\" } }," .
					"\"to\": \"".$row["Token"]."\" }' https://fcm.googleapis.com/fcm/send";
	  //echo("curl: " . $curl . CRLF);
	  $current .= "curl: " . $curl . "\n";
	  $feedback = exec($curl);
	  $current .= "Feedback=" . $feedback . "\n";
	  $dec = json_decode($feedback);
	  if (!is_numeric(substr($dec->{"results"}[0]->{"message_id"},0,1))) {
		//echo("NOK");
		$current .= "BAD Feedback, will remove from DB \n";
		$sql2 = "DELETE FROM `users_notification` WHERE `Alias`='".$row["Alias"]."' AND `Lang`='".$row["Lang"]."' AND `User_Agent`='".$row["User_Agent"]."' AND `Token`='".$row["Token"]."';";
		$current .= "SQL=" . $sql2 . "\n";
		mysqli_query($DB, $sql2);
	  } // END IF
	  
	} // END WHILE
	
	// Write the contents back to the file
	$current .=  "\n";
	file_put_contents($file, $current);
  } // END FUNCTION

  /* feedback & Trigger from Output Element*/
  function OUTtrigger($Manufacturer, $Subsystem, $Element, $Value) {
    //echo("class.triggers.php5/OUTtrigger/Func Call: Manufacturer=$Manufacturer, Subsystem=$Subsystem, Element=$Element, Value=$Value||".CRLF);
    if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Triggers: ", "!!! ERREUR Connection DB!!!"); }
	if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Triggers: ", "!!! ERREUR Selection DB!!!"); }

	// BOILER & HEATER Outs?
	$Thermics = array();
	$Thermics["Boiler"]["manufacturer"]="";$Thermics["Boiler"]["subsystem"]="";$Thermics["Boiler"]["element"]="";
	$Thermics["Heater"]["manufacturer"]="";$Thermics["Heater"]["subsystem"]="";$Thermics["Heater"]["element"]="";
	$sql = "SELECT * FROM `chauffage_clef` AS `CC`, `ha_element` AS `HE` WHERE `CC`.`valeur`=`HE`.`id` AND `CC`.`clef` Like '%erOUT' ORDER BY `CC`.`clef`;";
	$retour = mysqli_query($DB,$sql);
	while ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
	  $name = substr($row['clef'],0,-3);
	  $Thermics[$name] = array( 'id' => $row['valeur'], 'manufacturer' => $row['Manufacturer'], 'subsystem' => $row['card_id'], 'element' => $row['element_reference'] );
	} // END WHILE
	//echo("Boiler=".$Thermics["Boiler"]["manufacturer"]."/".$Thermics["Boiler"]["subsystem"].",".$Thermics["Boiler"]["element"]. CRLF);
	//echo("Heater=".$Thermics["Heater"]["manufacturer"]."/".$Thermics["Heater"]["subsystem"].",".$Thermics["Heater"]["element"]. CRLF);
	
	// Heater?
	if ( ($Manufacturer==$Thermics["Heater"]["manufacturer"]) && ($Subsystem==$Thermics["Heater"]["subsystem"]) && ($Element==$Thermics["Heater"]["element"])) {
	  $val=0;$mesg="OFF"; if ($Value!="0x00") { $val=1; $mesg="ON";}
	  $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '".$val."' WHERE `clef` = 'chaudiere';";
	  echo(CRLF."class.triggers.php5/OUTtrigger: Chaudiere ON/OFF(Value=$Value), SQL=$sql Value=$Value".CRLF);
      mysqli_query($DB,$sql);
      $ch = curl_init(URIPUSH);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, "heater;".$mesg);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $ret = curl_exec($ch);
      curl_close($ch);
	} else {
	  // Boiler?
	  if ( ($Manufacturer==$Thermics["Boiler"]["manufacturer"]) && ($Subsystem==$Thermics["Boiler"]["subsystem"]) && ($Element==$Thermics["Boiler"]["element"])) {
	    $val=0;$mesg="OFF"; if ($Value!="0x00") { $val=1; $mesg="ON";}
	    $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '".$val."' WHERE `clef` = 'boiler';";
	    echo(CRLF."class.triggers.php5/OUTtrigger: Boiler ON/OFF, SQL=$sql Value=$Value".CRLF);
        mysqli_query($DB,$sql);
        $ch = curl_init(URIPUSH);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "boiler;".$mesg);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        curl_close($ch);
	  } else {
	    // Light Status in DB
	    $sql    = "SELECT * FROM `lumieres` WHERE `Manufacturer` = '" . $Manufacturer . "' AND `carte` = '" . $Subsystem . "' AND `sortie` = '" . $Element . "';";
	    //echo(CRLF."class.triggers.php5/OUTtrigger/LightStatus, SQL=$sql<br>".CRLF);
		$retour = mysqli_query($DB,$sql);
		$i=0;
	    while ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
			$id = $row['id'];
			if (substr($Value,0,2)=="0x") { $Value=dechex(hexdec($Value)); } else { $Value=dechex($Value);}
			$LDB[$i] = "UPDATE `" . TABLE_LUMIERES_STATUS . "` SET `valeur` = '" . $Value . "' WHERE `id` = '" . $id . "';";
		    $msg[$i] = "LAMP;" . $id . "," . $row['icon'] . "," . $Value;
			$i++;
		} // END WHILE
		if ($i==0) { 
		  // NO light on plan ... PUSH 
		  $LDB[0] = "";
		  $msg[0] = "OUT;".$Manufacturer."," . $Subsystem . "," . $Element . "," . $Value;
		} else {
		  $i--;
		} // END IF
		$j=0;
		while ($j<=$i) {
		  $ch = curl_init(URIPUSH);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $msg[$j]);
		  //echo("PUSH Message (j=$j): ".$msg[$j]."<br>");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $ret = curl_exec($ch);
          curl_close($ch);
		  if ($LDB[$j]!="") { mysqli_query($DB,$LDB[$j]); }
		  $j++;
		} // END WHILE
	  } // END IF
	} // END IF

	//mysqli_close();
  } // END FUNCTION
 
  /* feedback & Trigger from Output Element*/
  function INtrigger($Manufacturer, $Subsystem, $Element, $Sequence) {
    //echo("class.triggers.php5/INtrigger/Func Call: Manufacturer=$Manufacturer, Subsystem=$Subsystem, Element=$Element, Sequence=$Sequence||".CRLF);
	$ch = curl_init(URIPUSH);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "IN;".$Manufacturer."," . $Subsystem . "," . $Element . "," . $Sequence);
	//echo("PUSH Message (j=$j): ".$msg[$j]."<br>");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    curl_close($ch);
	
	//if ($Sequence=="8a") { $new_key = "1"; }
	//if ($Sequence=="52") { $new_key = "0"; }
  } // END FUNCTION  
  

} // END CLASS
?>
