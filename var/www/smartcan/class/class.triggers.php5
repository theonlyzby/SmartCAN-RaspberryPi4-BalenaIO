<?php

class trigger {

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