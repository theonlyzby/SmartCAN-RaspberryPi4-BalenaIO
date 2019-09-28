<?php

class URL_envoiTrame {

  /* ENVOI DE LA TRAME VERS LA CIBLE */
  function envoiTrame($funcURL,$var1,$var2,$intensite,$progression) {

	if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "URL Class", "!!! ERREUR Connection DB!!!"); }
	if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "URL class", "!!! ERREUR Selection DB!!!"); }
	$sql = "SELECT * FROM `ha-URLmod-vars` WHERE `variable`='".$funcURL."';";
	//echo(CRLF."class.envoiTrame.php5/Log, SQL=$sql".CRLF);
    $query = mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($query, MYSQLI_BOTH);
	// Build Specific URL
	$linkURL = $row['value'];
	$linkURL = str_replace("*#--ONE--#*", $var1, $linkURL);
	$linkURL = str_replace("*#--TWO--#*", $var2, $linkURL);
	$linkURL = str_replace("*#--INTENSITY--#*", $intensite, $linkURL);
	$linkURL = str_replace("*#--DELAY--#*", $progression, $linkURL);
    mysqli_close($DB);
	
	// Parse URL
	$handle = fopen($linkURL, "r");
	$result = fread($handle,8192);
	
	if (strpos($result,"OK")!==false) {
	  $trigger = new trigger();
	  $trigger->OUTtrigger("URL", $var1, $var2, $intensite);
	} // END IF
	
	
  } // END FUNCTION
}

?>
