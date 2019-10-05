<?php
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
include_once($base_URI.'/www/smartcan/www/conf/config.php');

class DomoCAN3_envoiTrame {

  /* PREPARATION DU CHECKSUM */
  function checksum() {
    $check=0;
    //echo(CRLF."Calculating FCS: ");
	for ($i = 0; $i <= 14; $i++) {
	  //echo("Frame[".$i."]=".$this->trame[$i].", ");
      $check = (int)$this->trame[$i] + $check;
    }
    $this->trame[15] = $check % 256;  
	//echo("FCS=".$this->trame[15]);
	
	/* Tests Frame content
	echo(CRLF."Frame, with Chesum="); $i=0;
	while (isset($this->trame[$i])) {
	  echo(dechex($this->trame[$i]));
	  $i++;
	}
	echo(", length=".$i.CRLF.CRLF);
	*/
	
  }

  /* CONVERSION DE LA TRAME AVEC PACK() */
  function conversion() {
    $trame="";
	$this->trame_ok="";
    for ($i = 0; $i <= 15; $i++) {
      $this->trame_ok .= pack("c", $this->trame[$i]);
      $trame .= $this->trame[$i];
    }
  }

  /* ENVOI DE LA TRAME SUR L'INTERFACE */
  function envoiTrame() {
	global $base_URI;
    if (isset($this->trame_ok)) {
      $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
	  if (ADRESSE_INTERFACE!="localhost") {
	    $ifconfig = shell_exec('/sbin/ifconfig eth0');
	    if ($base_URI=="/data") {
	      // Balena
	      preg_match('/inet ([\d\.]+)/', $ifconfig, $match);
	    } else {
	      preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
	    } // END IF
	    $server_IP=$match[1];
		//echo("Server IP=".$server_IP.", Addr Int=". ADRESSE_INTERFACE ."<br>");
	    socket_bind($socket, $server_IP, 1470);
	  } // END IF
      $longueur = strlen($this->trame_ok);
	  //echo(CRLF."Frame sent=".$this->trame_ok.",length=".$longueur.CRLF);
      socket_sendto($socket, $this->trame_ok, $longueur, 0, ADRESSE_INTERFACE, 1470);
	  //socket_sendto($socket, $this->trame_ok, $longueur, 0, "172.27.10.247", 1470);
      socket_close($socket);
	}

  }

  /* PREPARE UNE TRAME CAN */
  function CAN($entete, $IDCAN = array(), $donnees = array()) {
	global $base_URI;
	//echo("CAN Frame: ".$entete.", IDCAN=".implode("-",$IDCAN).", Data=".implode("-",$donnees)."<br>".CRLF);
    $this->trame[0] = $entete; // ENVOI D'UNE TRAME CAN
	$ifconfig = shell_exec('/sbin/ifconfig eth0');
    //echo("ifconfig = " . $ifconfig . CRLF);
	if ($base_URI=="/data") {
	  // Balena
	  preg_match('/inet ([\d\.]+)/', $ifconfig, $match);
	} else {
	  preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
	} // END IF
	$server_IP=$match[1];
	//echo (CRLF."Server IP=".$match[1] . CRLF);
    $this->trame[1] = str_pad(dechex(substr($server_IP,strrpos($server_IP,".")+1)),2,"0", STR_PAD_LEFT); // ID DU PC QUI ENVOI
    $this->trame[2] = dechex(count($IDCAN) + count($donnees)); // NOMBRE D'OCTETS DE DATA

    if ( isset($IDCAN['DEST']) ) {
      $this->trame[3] = $IDCAN['DEST']; // TYPE DE CARTE (CAN)
    }
    else {
      $this->trame[3] = 0x00;
    }
 
   if ( isset($IDCAN['COMM']) ) {
      $this->trame[4] = $IDCAN['COMM']; // COMMANDE (CAN)
    }
    else { 
      $this->trame[4] = 0x00; 
    }

    if ( isset($IDCAN['CIBL']) ) {
      $this->trame[5] = $IDCAN['CIBL']; // CIBLE (CAN)
    }
    else { 
      $this->trame[5] = 0x00; 
    }

    if ( isset($IDCAN['PARA']) ) {
      $this->trame[6] = $IDCAN['PARA']; // PARAMETRE (CAN)
    }
    else { 
      $this->trame[6] = 0x00; 
    }

    $i = '7';
    foreach ($donnees as $valeur) {
	  //echo("Compose Frame[".($i+7)."] = ".$valeur.CRLF);
      $this->trame[$i] = $valeur;
      $i++;
    }

    while ( $i <= 14 ) {
	  //echo("i= $i, Adding 0x00".CRLF);
      $this->trame[$i] = 0x00;
      $i++;
    }

  }

  /* ARCHIVE VERS SQL */
  function logs($id_gradateur, $id_sortie, $valeur) {
    if (DEBUG_AJAX) {
      if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Connection DB!!!"); }
	  if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Selection DB!!!"); }
	  $sql = "INSERT INTO `logs` (id_gradateur,id_sortie,valeur) VALUES ('$id_gradateur', '$id_sortie', '$valeur');";
	  //echo(CRLF."class.envoiTrame.php5/Log, SQL=$sql".CRLF);
      mysqli_query($DB,$sql);
      mysqli_close($DB);
	}

  }

}

?>
