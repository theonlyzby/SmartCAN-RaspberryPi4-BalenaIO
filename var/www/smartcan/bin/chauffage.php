<?php

  /*
    SCRIPT POUR ALLUMAGE OU EXTINCTION DE LA CHAUDIERE
    SELON TEMPERATURE DE LA MAISON
    DOIT ETRE LANCE EN CRON
  */

  // Includes
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
  include_once($base_URI.'/www/smartcan/www/conf/config.php');
  include_once($base_URI.'/www/smartcan/class/class.triggers.php5');
  
  // Connect DB
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  // Mean value House Temperature
  $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE (`moyenne` = '1' AND `valeur`<>0 AND `update`>=DATE_SUB(now(), INTERVAL 2 MINUTE));");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $moyenne_actuelle= round($row[0],1); 
  $HeaterOUT=""; $HeaterOEM="";
  $BoilerOUT=""; $BoilerOEM="";

  /* TEMPERATURE VOULUE, TEMPRETAURE MINIMUM, PRESENCE(ABSENCE-1), CIRCULATEUREAUCHAUDE (Boiler sur Circulateur séparé)  */
  $sql="SELECT * FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `ZoneNber`=0;";
  $retour = mysqli_query($DB,$sql);
  while ($row = mysqli_fetch_array($retour, MYSQLI_BOTH)) {
    $clef = $row['clef'];
    if ($clef == "temperature" ) {
      $voulu = round($row['valeur'],1);
      $voulu2 = $voulu - 0.2;
      $voulu3 = $voulu + 0.5;
    } // ENDIF
	if ($clef == "tempminimum" ) {
	  $tempmini = round($row['valeur'],1);
	} // ENDIF
    if ($clef == "absence" ) {
      $absence = $row['valeur'];
    } // ENDIF
    if ($clef == "circulateureauchaude" ) {
	  $circulateureauchaude = $row['valeur'];
    } // ENDIF
	if ($clef == "HeaterOUT" ) {
	  $HeaterOUT = $row['valeur'];
	  if ($HeaterOUT!="") {
	    $sql = "SELECT * FROM `".TABLE_ELEMENTS."` WHERE `id`=".$HeaterOUT.";"; 
		$retour2        = mysqli_query($DB,$sql);
		$row2           = mysqli_fetch_array($retour2, MYSQLI_BOTH);
		$HeaterOEM     = $row2['Manufacturer'];
		$HeaterCard    = $row2['card_id'];
		$HeaterOUT     = $row2['element_reference'];
		$grad_FullName = $HeaterOEM . "_gradateur";
	    include_once(PATHCLASS.$row2['Manufacturer'].'/'.'class.envoiTrame.php5');
        include_once(PATHCLASS.$row2['Manufacturer'].'/'.'class.gradateur.php5');
		//$gradateur     = new $grad_FullName();
	  } // END IF
    } // ENDIF
	if ($clef == "BoilerOUT" ) {
	  $BoilerOUT = $row['valeur'];
	  if ($BoilerOUT!="") {
	    $sql = "SELECT * FROM `".TABLE_ELEMENTS."` WHERE `id`=".$BoilerOUT.";"; 
		$retour2        = mysqli_query($DB,$sql);
		$row2           = mysqli_fetch_array($retour2, MYSQLI_BOTH);
		$BoilerOEM     = $row2['Manufacturer'];
		$BoilerCard    = $row2['card_id'];
		$BoilerOUT     = $row2['element_reference'];
		$grad_FullName = $BoilerOEM . "_gradateur";
		include_once(PATHCLASS.$row2['Manufacturer'].'/'.'class.envoiTrame.php5');
        include_once(PATHCLASS.$row2['Manufacturer'].'/'.'class.gradateur.php5');
		//$grad_boiler   = new $grad_FullName();
	  } // END IF
    } // ENDIF
  } // END WHILE
   
  /* TEMP EXT */
  $retour      = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
  $row         = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $ext_temp    = $row[0];
  
  /* ETAT DE LA CHAUDIERE */
  $retour      = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'chaudiere';");
  $row         = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $chaudiere   = $row[0];
  /* ETAT DU BOILER */
  $retour      = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'boiler';");
  $row         = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $etat_boiler = $row[0];
  /* Status Warm Water probe into Boiler (if present) */
  if ($circulateureauchaude!=0) {
    $retour      = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'warm_water';");
    $row         = mysqli_fetch_array($retour, MYSQLI_BOTH);
    $warm_water  = $row[0];
  } // END IF
  /* HEATER TIMESLOT ACTIF */
  $Now             = date("H:i:00");
  $DayBit          = date("N");
  $Today           = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql             = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour          = mysqli_query($DB,$sql);
  $row             = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $periode_chauffe = $row[0];

  // Boiler
  $sql             = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='BOILER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour          = mysqli_query($DB,$sql);
  $row             = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $periode_boiler  = $row[0];
  
  //echo("PUSH: HeaterOEM = " . $HeaterOEM . ", BoilerOEM = " . $BoilerOEM . ", Status Heater = " . $chaudiere . ", Status Boiler = " . $etat_boiler . 
  //	", Heater Timeslot = " . $periode_chauffe . ", Boiler Timeslot = " . $periode_boiler . CRLF);
  
  if ($circulateureauchaude==0) {
	 /////////////////////////////////////////////////////////////////////////////////////////////
	// Mode 1 Circulateur (1er Contact = Chaudiere+Boiler), 2eme contact = Circulater Chauffage) //
	 /////////////////////////////////////////////////////////////////////////////////////////////

    // Si Moyenne<Temp Min OU (Présent ET (Periode Chauffage OU Periode Boiler)) => Chaudiere (et Boiler) ON 
	if (($absence==0 && ($periode_chauffe>=1 || $periode_boiler>=1)) || (($moyenne_actuelle!='0') && ($moyenne_actuelle<($tempmini+($voulu2-$voulu))))) {  
	  if ($BoilerOEM!="") {
		$grad_FullName = $BoilerOEM . "_gradateur";
	    $grad_boiler   = new $grad_FullName();
		$NOK=0;
		if ($etat_boiler==0) {
		  // Test reachability Boiler Module
		  $NOK = $grad_boiler->test_reachability($BoilerOEM, $BoilerCard, $BoilerOUT, "SmartCAN-ALERT", "Boiler-Module-UNREACHABLE");
		  //echo("Unreachable = " . $NOK . CRLF);
		  // SET Boiler Indication on Nest
		  mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'boiler';");
		} // END IF
		
		
		if ($NOK==0) {
		  $grad_boiler->allumer($BoilerCard, $BoilerOUT, 0, 0x32);
		  //echo("Boiler ON, absence=$absence, periode_chauffe=$periode_chauffe, periode_boiler=$periode_boiler, moyenne_actuelle=$moyenne_actuelle, Mini=".($tempmini+($voulu3-$voulu)) .CRLF);
	      // Si Moyenne<=Temperature Confort ET pas d'erreur de sonde OU Moyenne<Temp Min
	      if ((($moyenne_actuelle<=$voulu2) || ($moyenne_actuelle<($tempmini+($voulu2-$voulu)))) && $moyenne_actuelle != '0') {
		    if ($HeaterOEM!="") {
	          $grad_FullName = $HeaterOEM . "_gradateur";
		      $gradateur     = new $grad_FullName();
			  $NOK=0;
			  if ($chaudiere==0) {
			    $NOK = $gradateur->test_reachability($HeaterOEM, $HeaterCard, $HeaterOUT, "SmartCAN-ALERT", "Heater-Module-UNREACHABLE");
			    //echo("Unreachable = " . $NOK . CRLF);
				// SET Heating Indication on Nest
				mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'chaudiere';");
			  } // END IF
			  if ($NOK==0) {
	            $gradateur->allumer($HeaterCard, $HeaterOUT, 0, 0x32);
		        //echo("Circulateur ON" .CRLF);
			  } // END IF ($NOK==0)
		    } // END IF
	      } // END IF
		} // END IF ($NOK==0)
	  } // END IF
	} // END IF
		 
	// Si (Absent OU PAS Periode Chauffage OU PAS Periode Boiler) ET Moyenne>Temp Min => Chaudiere (et Boiler) OFF
	if (($absence==1 || ($periode_chauffe==0 && $periode_boiler==0)) && $moyenne_actuelle>$tempmini+($voulu3-$voulu)) { 
	  if ($BoilerOEM!="") {
	    $grad_FullName = $BoilerOEM . "_gradateur";
		$grad_boiler    = new $grad_FullName();
		$NOK=0;
		if ($etat_boiler==1) {
		  // Test reachability Boiler Module
		  $NOK = $grad_boiler->test_reachability($BoilerOEM , $BoilerCard, $BoilerOUT, "SmartCAN-ALERT", "Boiler-Module-UNREACHABLE");
		  //echo("Unreachable = " . $NOK . CRLF);
		  // Set NO Boiler Indication on Nest
		  mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'boiler';");
		} // END IF
		
		if ($NOK==0) {
		  $grad_boiler->eteindre($BoilerCard, $BoilerOUT, 0);
		  //echo("Boiler OFF" .CRLF);
		} // END IF ($NOK==0)
	  } // END IF	
	  
	} // END IF
		 
	// Si ((Absent OU PAS Periode Chauffage OU PAS Periode Boiler) ET Moyenne>Temp Min) OU Moyenne>=Temperature Confort (ou Erreur Sonde)
	if ((($absence==1 || ($periode_chauffe==0 && $periode_boiler==0)) && $moyenne_actuelle>$tempmini+($voulu3-$voulu)) || ($moyenne_actuelle>=$voulu3 || $moyenne_actuelle=='0' )) {
	  if ($HeaterOEM!="") {
	    $grad_FullName = $HeaterOEM . "_gradateur";
	    $gradateur     = new $grad_FullName();
		$NOK=0; if ($chaudiere==1) {
		  $NOK = $gradateur->test_reachability($HeaterOEM, $HeaterCard, $HeaterOUT, "SmartCAN-ALERT", "Heater-Module-UNREACHABLE");
		  //echo("Heater Unreachable = " . $NOK . CRLF);
		  // Set NO Heating Indication on Nest
	      mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'chaudiere';");
		} // END IF
		if ($NOK==0) {
	      $gradateur->eteindre($HeaterCard, $HeaterOUT, 0);
	      //echo("Circulateur OFF" .CRLF);
		  // Clear HEAT Now when done
	      mysqli_query($DB,"DELETE FROM `". TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER' AND `days`='00000001' AND `stop`<='" . $Now . "';");
		} // END IF ($NOK==0)
	  } // END IF
	} // END IF
		 
  } else {
		 /////////////////////////////////////////////////////////////////////////////////////////////////
		// Mode 2 Circulateurs (1er Contact = Circulateur Chauffage), 2eme contact = Circulateir Boiler) //
		 /////////////////////////////////////////////////////////////////////////////////////////////////  

		  /* SI LA TEMPERATURE VOULUE EST SUPERIEUR A CELLE DE LA MAISON */
		  if (((( $voulu2 >= $moyenne_actuelle && $moyenne_actuelle != '0' ) && ($absence==0 && $periode_chauffe>=1)) || ($tempmini>$moyenne_actuelle && $moyenne_actuelle!='0')) 
				&& ($periode_boiler==0)) {
			//echo("\nHEATER on(HeaterOEM=$HeaterOEM)");
			if ($HeaterOEM!="") {
	          $grad_FullName = $HeaterOEM . "_gradateur";
		      $gradateur     = new $grad_FullName();
			  $NOK=0;
			  if ($chaudiere==0) {
			    $NOK = $gradateur->test_reachability($HeaterOEM, $HeaterCard, $HeaterOUT, "SmartCAN-ALERT", "Heater-Module-UNREACHABLE");
			    //echo("Heater Unreachable = " . $NOK . CRLF);
				// SET Heating Indication on Nest
			    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'chaudiere';");
			  } // END IF
			  if ($NOK==0) {
	            $gradateur->allumer($HeaterCard, $HeaterOUT, 0, 0x32);
			    //echo("\nHEATER on(2)");
			  } // END IF ($NOK==0)
			  
			} // END IF
			if ( $chaudiere == '0' ) {
			  if (DEBUG_AJAX) { 
			    mysqli_query($DB,"INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_CHAUFFAGE . "','" . SORTIE_CHAUFFAGE . "', CURRENT_TIMESTAMP, '32');");
				mysqli_query($DB,"INSERT INTO `" . TABLE_MEASURE . "` (`measure_type`, `start_time`, `start_value`, `extra_measure`) VALUES ('HEATER', CURRENT_TIMESTAMP,'" . $moyenne_actuelle . "','" . $ext_temp . "');");
			  } // END IF DEBUG_AJAX
			} // ENDIF
		  } // ENDIF
		  /* SI LA TEMPERATURE MOYENNE EST SUPERIEUR A LA VOULUE ou absence ou pas de periode chauffe ou ... priorité eau chaude*/
		  if (( ($moyenne_actuelle >= $voulu3 && $tempmini<$moyenne_actuelle+1) || $moyenne_actuelle == '0' ) || ($absence==1 || $periode_chauffe==0) || ($periode_boiler>=1)) {
		    //echo("\nHEATER Off(HeaterOEM=$HeaterOEM)");
			if ($HeaterOEM!="") {
	          $grad_FullName = $HeaterOEM . "_gradateur";
		      $gradateur     = new $grad_FullName();
			  $NOK=0;
			  if ($chaudiere==1) {
			    $NOK = $gradateur->test_reachability($HeaterOEM, $HeaterCard, $HeaterOUT, "SmartCAN-ALERT", "Heater-Module-UNREACHABLE");
				//echo("Heater Unreachable = " . $NOK . CRLF);
				// Set NO Heating Indication on Nest
			    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'chaudiere';");
			  } // END IF
			  if ($NOK==0) {
		        $gradateur->eteindre($HeaterCard, $HeaterOUT, 0);
			    //echo("\nHEATER Off\n");
			  } // END IF ($NOK==0)
		    } // END IF
			//$gradateur->eteindre(CARTE_CHAUFFAGE, SORTIE_CHAUFFAGE, 0);
			// Clear HEAT Now when done
			mysqli_query($DB,"DELETE FROM `". TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER' AND `days`='00000001' AND `stop`<='" . $Now . "';");
			mysqli_query($DB,$sql);	
			if ($chaudiere==1) {
			  if (DEBUG_AJAX) { mysqli_query($DB,"INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_CHAUFFAGE . "','" . SORTIE_CHAUFFAGE . "', CURRENT_TIMESTAMP, '00');"); }
			  mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'chaudiere';");
			  // Determine Stop Value & Update DB
			  if (DEBUG_AJAX) { 
				$sql         = "SELECT * FROM `" . TABLE_MEASURE . "` WHERE `measure_type`='HEATER' ORDER BY EXTRACT(YEAR_MONTH FROM `start_time`) DESC , EXTRACT(DAY_MINUTE FROM `start_time`) DESC limit 1;";
				$retour      = mysqli_query($DB,$sql);
				$row         = mysqli_fetch_array($retour, MYSQLI_BOTH);
				$measure_id   = $row["id"];
				$stop_reason = "UNKNOWN";
				if (($periode_boiler>=1)) { $stop_reason = "BOILER"; } else { if ($moyenne_actuelle >= $voulu3) { $stop_reason = "TEMP"; } else { if ($periode_chauffe==0) { $stop_reason = "TIME"; } else { if ($absence==1) { $stop_reason = "OUT"; }}}}
				mysqli_query($DB,"UPDATE `" . TABLE_MEASURE . "`SET `stop_time` = CURRENT_TIMESTAMP, `stop_value` = '" . $moyenne_actuelle . "', `stop_reason` = '" . $stop_reason . "', `extra_measure2` = '" . $ext_temp . "' WHERE `id` = '" . $measure_id . "';");
			  } // END IF
			} // END IF
		  }  // END IF
		  

			/* Si Periode BOILER & Presence */
			if ($absence==0 && $periode_boiler>=1) { // && $warm_water==0) {
			  if ($BoilerOEM!="") {
				$grad_FullName = $BoilerOEM . "_gradateur";
				$grad_boiler    = new $grad_FullName();
				$NOK=0;
				if ($etat_boiler==0) {
			      $NOK = $grad_boiler->test_reachability($BoilerOEM, $BoilerCard, $BoilerOUT, "SmartCAN-ALERT", "Boiler-Module-UNREACHABLE");
				  //echo("Boiler Unreachable = " . $NOK . CRLF);
				  // SET Boiler Indication on Nest
			      mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'boiler';");
			    } // END IF
			    if ($NOK==0) {
				  $grad_boiler->allumer($BoilerCard, $BoilerOUT, 0, 0x32);
				  //echo("\nBOILER on\n");
		        } // END IF
			  } // END IF
			  if (($etat_boiler == '0') && (DEBUG_AJAX)) { 
				mysqli_query($DB,"INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_BOILER . "','" . SORTIE_BOILER . "', CURRENT_TIMESTAMP, '32');");
				mysqli_query($DB,"INSERT INTO `" . TABLE_MEASURE . "` (`measure_type`, `start_time`, `start_value`) VALUES ('BOILER',CURRENT_TIMESTAMP, '0');");
			  } // ENDIF
			  
			} // ENDIF
			
			/* Si FIN periode BOILER */
			if ($absence==1 || $periode_boiler==0) { // || $warm_water==1) {
			  if ($BoilerOEM!="") {
			    $grad_FullName = $BoilerOEM . "_gradateur";
			    $grad_boiler    = new $grad_FullName();
				$NOK=0;
				if ($etat_boiler==1) {
			      $NOK = $grad_boiler->test_reachability($BoilerOEM, $BoilerCard, $BoilerOUT, "SmartCAN-ALERT", "Boiler-Module-UNREACHABLE");
				  //echo("HBoiler Unreachable = " . $NOK . CRLF);
				  // Set NO Boiler Indication on Nest
			      mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'boiler';");
			    } // END IF
			    if ($NOK==0) {
			      $grad_boiler->eteindre($BoilerCard, $BoilerOUT, 0);
				  //echo("\nBOILER off\n");
				} // END IF
		      } // END IF			  
			  if ($etat_boiler == '1') {
				if (DEBUG_AJAX) { 
				  mysqli_query($DB,"INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_BOILER . "','" . SORTIE_BOILER . "', CURRENT_TIMESTAMP, '00');");
				  // Determine Stop Value + reason & Update DB 
				  $sql        = "SELECT * FROM `" . TABLE_MEASURE . "` WHERE `measure_type`='BOILER' ORDER BY EXTRACT(YEAR_MONTH FROM `start_time`) DESC , EXTRACT(DAY_MINUTE FROM `start_time`) DESC limit 1;";
				  $retour     = mysqli_query($DB,$sql);
				  $row        = mysqli_fetch_array($retour, MYSQLI_BOTH);
				  $measure_id  = $row["id"];
				  if ($warm_water == "0") { $stop_reason = "TEMP"; } else { if ($periode_boiler==0) { $stop_reason = "TIME"; } else { if ($absence==1) { $stop_reason = "OUT"; }}}
				  mysqli_query($DB,"UPDATE `" . TABLE_MEASURE . "` SET `stop_time` = CURRENT_TIMESTAMP, `stop_reason` = '" . $stop_reason . "' WHERE `id` = '" . $measure_id . "';");
				} // END IF
			  
				// Less than 1 Hour before Heater start => Early Start
				$next_hour = date("H:i:00",mktime(date("H")+1, date("i"), 0, 1, 1, 1));
				$sql              = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $next_hour . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
				$retour           = mysqli_query($DB,$sql);
				$row              = mysqli_fetch_array($retour, MYSQLI_BOTH);
				$enchaine_chauffe = $row[0];
				if ($enchaine_chauffe>=1) {
				  $sql    = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` SET `days` = '00000001', `start`='" . $Now . "', `stop`='" . $next_hour . "', `active`='Y';";
				  mysqli_query($DB,$sql);
				} //ENDIF
			  } //ENDIF

			  /* Efface HEAT Now quand fini */
			  $sql = "DELETE FROM ". TABLE_HEATING_TIMSESLOTS . " WHERE `function`='HEATER' AND `days`='00000001' AND `stop`<='" . $Now . "';";
			  mysqli_query($DB,$sql);
			} //ENDIF
	 } // END IF ($circulateureauchaude)
  
  mysqli_close($DB);

?>
