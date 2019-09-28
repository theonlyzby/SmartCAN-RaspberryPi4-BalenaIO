<?php

$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
//include_once('class.debug.php5');
include_once($base_URI.'/www/smartcan/www/conf/config.php');
include_once('class.envoiTrame.php5');	
include_once($base_URI.'/www/smartcan/class/class.triggers.php5');
include_once('class.gradateur.php5');	
include_once('class.in16.php5');	
include_once('class.erreur.php5');	
	

class receptionTrame {

  function __construct() {
    $this->debug = new debug();
    $this->gradateur = new DomoCAN3_gradateur();
    $this->erreur = new erreur();
    $this->in16 = new DomoCAN3_in16();
    $this->DomoCAN3_envoiTrame = new DomoCAN3_envoiTrame();
	$this->trigger = new trigger();
  }

  /*
    RECEPTION D'UNE TRAME DONC TRAITEMENT
  */
  function traiter($trame) {

    /* CONVERTIR EN TABLEAU */
    $this->trame_txt = substr($trame, 0, 32);
    $this->trame = str_split(substr($trame, 0, 32), 2);
    unset($trame);

    /* VERIFICATION CHECKSUM */
    $check = 0;
    for($i=0;$i<=14;$i++) {
      $check = hexdec($this->trame[$i]) + $check;
    }
    
    $check = str_pad(dechex($check % 256), 2, "0", STR_PAD_LEFT);
    $COMMANDE = $this->trame[4];
	
	//echo("Class RECEPTION TRAME : " . $this->trame_txt . ", CheckSum(".$this->trame[15]."=".($this->trame[15] == $check). "=".$check."), CMD=".$COMMANDE . CRLF);
	
	if ($this->trame[15] == $check) {	

      /* DEBUG */
      $this->debug->envoyer(2, "Class RECEPTION TRAME", $this->trame_txt);

      /* REPARTITION SELON ENTETE */
      switch ($this->entete()) {

        case '50' :
          /* ACCUSE DE RECEPTION D'ENVOI DE TRAME BRUTE */
          $PCID   = $this->trame[1];
          $STATUT = $this->trame[3];
          $this->debug->envoyer(1, "ACCUSE DE RECEPTION","DE " . $PCID . " | ERREUR : " . $STATUT);
          break;

        case '70' :
          /* RECEPTION DE TRAME CAN DEPUIS UNE CARTE FILLE */
          $DESTINATAIRE = $this->trame[3];
          //$COMMANDE = $this->trame[4];
          $CIBLE = str_pad(strtolower($this->trame[5]),2,"0",STR_PAD_LEFT);
          $PARAMETRE = str_pad(strtolower($this->trame[6]),2,"0",STR_PAD_LEFT);
          $D0 = $this->trame[7];
          $D1 = $this->trame[8];
          $D2 = $this->trame[9];
          $D3 = $this->trame[10];
          $D4 = $this->trame[11];
          $D5 = $this->trame[12];
          $D6 = $this->trame[13];
          $D7 = $this->trame[14];
          $this->debug->envoyer(1, "Class RECEPTION D'UNE TRAME","DESTINATAIRE " . $DESTINATAIRE . " | COMMANDE : " . $COMMANDE . " | CIBLE : " . $CIBLE . " | PARAMETRE : " . $PARAMETRE . " | D0 : "
          . $D0 . " | D1 : " . $D1 . " | D2 : " . $D2 . " | D3 : " . $D3 . " | D4 : " . $D4 . " | D5 : " . $D5 . " | D6 : " . $D6 . " | D7 : " . $D7);

		  // Voir Tableau p.42, Présentation_r7.pdf
		  
		  /* 7.3.22 Cmd_NameC
		  Commande : 0x00 (CMD_COMM)
		  Paramètre : 0x69
		  Sens : Emission
		  Mode : Normal
		  Rôle : Envoi des 8 octets du nom de la carte
		  Data : 8 octets
		             D0/D7 : octets du nom de la carte
		  Comme son nom l’indique, cette commande permet de fournir le nom de la carte interrogée.
		  */
		  if ($PARAMETRE=="69") { 
		  
			// "Card Name Response" received
			// Connects to DB
			if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Connection DB!!!"); }
			if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Selection DB!!!"); }
			
			$Card_Name = chr(hexdec($D0)).chr(hexdec($D1)).chr(hexdec($D2)).chr(hexdec($D3)).chr(hexdec($D4)).chr(hexdec($D5)).chr(hexdec($D6)).chr(hexdec($D7));
		    $this->debug->envoyer(1, "Reception Nom Carte","Type Carte=" . $DESTINATAIRE . ", Numero=" . $CIBLE. ", Nom=".$Card_Name);
			echo("Reception Nom Carte Type Carte=" . $DESTINATAIRE . ", Numero=" . $CIBLE. ", Nom=".$Card_Name.CRLF);

			// Add or Modify Card in DB
		    $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_subsystem` WHERE Manufacturer='DomoCAN3' AND Type='0x".$DESTINATAIRE."' AND Reference='0x".$CIBLE."';"));
		    if ($count == 1) {
			  mysqli_query($DB,"UPDATE `ha_subsystem` SET Name='".$Card_Name."' WHERE Manufacturer='DomoCAN3' AND Type='0x".$DESTINATAIRE."' AND Reference='0x".$CIBLE."';");
		    } else {
			  mysqli_query($DB,"INSERT INTO `ha_subsystem_TEMP` (id,Manufacturer,Type,Reference,Name) VALUES ('','DomoCAN3','0x".$DESTINATAIRE."','0x".$CIBLE."','".$Card_Name."');");
		    } // End IF
			mysqli_close($DB);
			
		  } //End IF
		  
          switch ($DESTINATAIRE) {

            /* RECEPTION DEPUIS UNE CARTE DE TYPE IN16 */
            case '60' :
              //$this->in16->reception($COMMANDE, $CIBLE, $PARAMETRE, $D0, $D1, $D2, $D3, $D4, $D5, $D6, $D7);
			  if ($COMMANDE=="18") {
			    $this->trigger->INtrigger("DomoCAN3", "0x".$CIBLE, "0x".$PARAMETRE, "0x".$D0);
			  } // END IF
              break;

            /* RECEPTION DEPUIS UNE CARTE DE TYPE IN16 */
			case '50' :
			  // Commande : 0x18 (Cmd_24)
			  // RECEPTION D'UN ACCUSE DEPUIS CARTE GRADATEUR (RETOUR ALLUMAGE)
			  if ($COMMANDE=="18") {
			    /* ENVOI VERS SQL POUR ACCUSE + PUSH Web*/
                $this->trigger->OUTtrigger("DomoCAN3", "0x".$CIBLE, "0x".$PARAMETRE, "0x".$D0);
			  } // ENDIF
			  
			  /*3.2.23 Cmd_NameLum
			  Commande : 0x19 (Cmd_25)
			  Paramètre : 0x00 à 0x0F : numéro de la sortie
			  D0/D7 : 8 caractères du nom de la sortie
			  Cette commande renvoie simplement le nom de la sortie précisée.
			  */
			  if ($COMMANDE=="19") {

			    if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Reception Accusé Gradateur", "!!! ERREUR Connection DB!!!"); }
				if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Reception Gradateur OUT Name", "!!! ERREUR Selection DB!!!"); }
				// CREATE in TEMP Table
				$sql = "INSERT INTO `ha_element_TEMP` (`id`, `card_id`, `element_type`, `element_reference`, `sequence`, `element_name`) VALUES (NULL, '0x" . $CIBLE . "', '0x11', '0x" . $PARAMETRE . "', 1, '" . 
							chr(hexdec($D0)) . chr(hexdec($D1)) . chr(hexdec($D2)) . chr(hexdec($D3)) . chr(hexdec($D4)) . chr(hexdec($D5)) . chr(hexdec($D6)) . chr(hexdec($D7)) . "');";
				echo(CRLF."Cmd_NameLum[0x19 (Cmd_25)], SQL=$sql".CRLF);
				$row = mysqli_query($DB,$sql);
				mysqli_close($DB);

			  } // END IF
			  
			  /*3.2.26 Cmd_NameMLum1
				Commande : 0x1C (Cmd_28)
				Paramètre : 0x00 à 0x0E : numéro de la mémoire
				Sens : Emission
				Rôle : Renvoie les 8 premiers caractères du nom de la mémoire
				Data : 8 octets
				D0/D7 : 8 premiers caractères du nom de la mémoire
				Cette commande renvoie simplement le début du nom de la mémoire précisée, sous forme de 8 caractères ascii.
			  */
			  if ($COMMANDE=="1c") {

			    if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Reception Cmd_NameMLum1", "!!! ERREUR Connection DB!!!"); }
				if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Reception Cmd_NameMLum1", "!!! ERREUR Selection DB!!!"); }
				// CREATE in TEMP Table
				$sql = "INSERT INTO `ha_element_TEMP` (`id`, `card_id`, `element_type`, `element_reference`, `sequence`, `element_name`) VALUES (NULL, '0x" . $CIBLE . "', '0x16', '0x" . $PARAMETRE . "', 1, '" . 
							chr(hexdec($D0)) . chr(hexdec($D1)) . chr(hexdec($D2)) . chr(hexdec($D3)) . chr(hexdec($D4)) . chr(hexdec($D5)) . chr(hexdec($D6)) . chr(hexdec($D7)) . "');";
				echo(CRLF."Cmd_NameMLum1[0x1C (Cmd_28)], SQL=$sql".CRLF);
				$row = mysqli_query($DB,$sql);
				mysqli_close($DB);

				} // END IF
			  
			  /*3.2.27 Cmd_NameMLum2
				Commande : 0x1D (Cmd_29)
				Paramètre : 0x00 à 0x0E : numéro de la mémoire
				Sens : Emission
				Rôle : Renvoie les 8 derniers caractères du nom de la mémoire
				Data : 8 octets
				D0/D7 : 8 derniers caractères du nom de la mémoire
				Cette commande complète la précédente, en fournissant le reste du nom de la mémoire.
			  */
			  if ($COMMANDE=="1d") {

			    if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Reception Cmd_NameMLum2", "!!! ERREUR Connection DB!!!"); }
				if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Reception Cmd_NameMLum2", "!!! ERREUR Selection DB!!!"); }
			    // CREATE in TEMP Table
				$sql = "INSERT INTO `ha_element_TEMP` (`id`, `card_id`, `element_type`, `element_reference`, `sequence`, `element_name`) VALUES (NULL, '0x" . $CIBLE . "', '0x16', '0x" . $PARAMETRE . "', 2, '" . 
							chr(hexdec($D0)) . chr(hexdec($D1)) . chr(hexdec($D2)) . chr(hexdec($D3)) . chr(hexdec($D4)) . chr(hexdec($D5)) . chr(hexdec($D6)) . chr(hexdec($D7)) . "');";
				echo(CRLF."Cmd_NameMLum2[0x1D (Cmd_29)], SQL=$sql".CRLF);
				$row = mysqli_query($DB,$sql);
				mysqli_close($DB);

				} // END IF			  


              /* LANCEMENT DU PROCESSUS DE FERMETURE AUTOMATIQUE SUR TIMER PROGRAMMABLE PAR LAMPE */
              echo(CRLF."CMD=".$COMMANDE.CRLF);
			  //if (isset($CIBLE) && isset($PARAMETRE) && ($COMMANDE!="1d") && ($COMMANDE!="1c") && ($COMMANDE!="19") && ($COMMANDE!="18")) {
			  if ( ($COMMANDE=="01") ) {

			    if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Connection DB!!!"); }
				if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Selection DB!!!"); }
			    $sql = "SELECT LUM.`id` AS id,LUM.`timer` AS timer,STAT.`timer_pid` AS timer_pid FROM `lumieres` AS LUM, `lumieres_status` AS STAT WHERE `carte` = '" . $CIBLE . "' AND `sortie` = '0x" . $PARAMETRE . "' AND LUM.`id`=STAT.`id`;";
                echo(CRLF."TIMER Close, SQL=$sql".CRLF);
				$row = mysqli_query($DB,$sql);
				//echo("SQL=".$sql."//END".CRLF."CMD=".$COMMANDE.CRLF);
                $retour = mysqli_fetch_array($row, MYSQLI_BOTH);
				
				if ( $D0 != '00' && $retour['id'] != '' ) {
                  if ( $retour['timer'] != '0' ) {
                    if ( $retour['timer_pid'] != '0' ) {
                      $a = "kill -9 " . $retour['timer_pid'];
                      exec($a);
                    } // END IF
		            $cmd = "sleep " . $retour['timer'] . " && php /var/www/smartcan/bin/eteindre.php " . $CIBLE . " " . $PARAMETRE . " " .  $retour['id'] . "&";
		            $ds = array(array('pipe', 'r'));
		            $cat = proc_open($cmd,$ds,$pipes);
		            $tmp = proc_get_status($cat);
		            $pid = $tmp['pid'] + 2;
					$sql = "UPDATE `" . TABLE_LUMIERES_STATUS . "` SET `timer_pid` = '" . $pid . "' WHERE `id` = '" . $retour['id'] . "';";
					echo(CRLF."TIMER Close, SQL=$sql".CRLF);
                    mysqli_query($DB,$sql);
                  } // END IF
                } // END IF
				mysqli_close($DB);

				} // END IF


       
              break;


            case 'fd' :
              $this->erreur->reception($PARAMETRE);
              break;

          }

          break;

        case '51' :
          /* RECEPTION DES STATUTS DE COMMUNICATION CAN */
          $PCID = $this->trame[1];
          $TXB0CON = $this->trame[3];
          $COMSTAT = $this->trame[4];
          $STATUT = $this->trame[5];
          $this->debug->envoyer(1, "RECEPTION STATUS COMM. CAN","DE " . $PCID . " | TXB0CON : " . $TXB0CON . " | COMSTAT : " . $COMSTAT . " | ERREUR : " . $STATUT);
          break;

        case '52' :
          /* RECEPTION DU MASQUE ET DU FILTRE */
          $PCID = $this->trame[1];
          $FILTRE_DEST = $this->trame[3];
          $FILTRE_COMM = $this->trame[4];
          $FILTRE_CIBL = $this->trame[5];
          $FILTRE_PARA = $this->trame[6];
          $MASQUE_DEST = $this->trame[7];
          $MASQUE_COMM = $this->trame[8];
          $MASQUE_CIBLE = $this->trame[9];
          $MASQUE_PARA = $this->trame[10];
          $STATUT = $this->trame[11];
          $this->debug->envoyer(1, "RECEPTION MASQUE / FILTRE","DE " . $PCID . " | FILTRE_DEST : " . $FILTRE_DEST . " | FILTRE_COMM : " . $FILTRE_COMM . " | FILTRE_CIBL : " . $FILTRE_CIBL . " | FILTRE_PARA : "
          . $FILTRE_PARA . " | MASQUE_DEST : " . $MASQUE_DEST . " | MASQUE_COMM : " . $MASQUE_COMM . " | MASQUE_CIBLE : " . $MASQUE_CIBLE . " | MASQUE_PARA : " . $MASQUE_PARA . " | ERREUR : " . $STATUT);
          break;

        case '54' :
          /* RECEPTION DES PARAMETRES CAN ACTUELS AVEC EEPROM CONCERNE */
          $PCID = $this->trame[1];
          $TQ = $this->trame[3];
          $TP = $this->trame[4];
          $PS1 = $this->trame[5];
          $PS2 = $this->trame[6];
          $SJW = $this->trame[7];
          $SAMPLE = $this->trame[8];
          $STATUT = $this->trame[9];
          $this->debug->envoyer(1, "RECEPTION PARAM. CAN","DE " . $PCID . " | TQ : " . $TQ . " | TP : " . $TP . " | PS1 : " . $PS1 . " | PS2 : "
          . $PS2 . " | SJW : " . $SJW . " | SAMPLE : " . $SAMPLE . " | ERREUR : " . $STATUT);
          break;

	  }

    }

  }

  /* RECUPERATION DE L'ENTETE */
  function entete() {
    return $this->trame[0];
  }

}

?>

