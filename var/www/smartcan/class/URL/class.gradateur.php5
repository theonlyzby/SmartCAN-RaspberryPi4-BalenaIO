<?php

class URL_gradateur extends URL_envoiTrame {

  /*
    TEST REACHABILITY OUTPUT
    $var1
    $var2
  */
  function test_reachability($OEM, $var1, $var2, $Notif_Title, $Notif_Body) {
	global $DB;
	$Trig = new trigger();
	$sql = "SELECT * FROM `ha-URLmod-vars` WHERE `variable`='onURL';";
	//echo(CRLF."class.gradateur.php5/Log, SQL=$sql".CRLF);
    $query = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($query, MYSQLI_BOTH);
	// Build Specific URL
	$linkURL = $row['value'];
	$linkURL = str_replace("*#--ONE--#*", $var1, $linkURL);
	$linkURL = str_replace("*#--TWO--#*", $var2, $linkURL);
	$ip      = parse_url($linkURL, PHP_URL_HOST);
	$pingresult = exec("/bin/ping -c2 -w2 $ip", $outcome, $status);  
    if ($status!=0) {
	  //echo "The IP address, $ip, is UNREACHABLE!\n";
	  $Trig->PWA_notify($OEM, $Notif_Title, $Notif_Body, " (URL IP: ".$ip.")");
	} else {
	  //$Trig->PWA_notify($OEM, "SmartCAN-INFO", "Module-Reachable", " (URL IP: ".$ip.")");
	} // END IF
	return $status;
  } // END FUNCTION


  /*
    ALLUMER UNE SORTIE
    $progression => TEMPS ENTRE L'ETAT FERME ET L'ETAT OUVERT (0 - 2550 ms)

  */
  function allumer($var1, $var2, $progression = 0, $intensite = 0x32) {
	$this->envoiTrame("onURL",$var1,$var2,$intensite,$progression);
  }


  /*
    ETEINDRE UNE SORTIE
    $progression => TEMPS ENTRE L'ETAT OUVERT ET L'ETAT FERME (0 - 2550 ms)
  */
  function eteindre($var1, $var2, $progression = "0x00") {
    $this->envoiTrame("offURL",$var1,$var2,0,$progression);
  }


  /*
    INVERSER UNE SORTIE
  */
  function inverser($var1, $var2, $intensite = "0x32", $progression = "0x00") {
	$this->envoiTrame("InvertURL",$var1,$var2,$intensite,$progression);
  }

  
  
} // END CLASS URL_gradateur
?>
