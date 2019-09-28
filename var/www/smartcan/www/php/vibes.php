<?php
  //$titre = 'Gestion des Ambiances';

  //include '../class/DomoCAN3/class.envoiTrame.php5';
  //include '../class/DomoCAN3/class.gradateur.php5';

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'vibecall');

  // AJAX Debug
  //$xajax->configure('debug',true);

  /* FONCTIONS PHP AJAX */
  function vibecall($vibeid) {
    $reponse = new XajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
	$sql = "SELECT * FROM `ha_vibe_elements` WHERE `vibe_id`='".$vibeid."';";
    $retour = mysqli_query($DB,$sql);
	echo("TOP SQL=$sql".CRLF); //
	
	//CAN(0x60,80-4-1-12,0-00-0)
	//CAN(0x60,80-4-1-12,0-50-0)
	//CAN(0x60,80-4-1-12,0-50-0)
    while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
	  //echo("Mode=".$row['mode'].CRLF);
      // Get Element to actuate
	  if ($row['mode']=="OUT") {
	    // Gradator
	    $sql = "SELECT * FROM `lumieres` WHERE `id`='".$row['output_number']."';";
		echo("LAMP SQL=$sql".CRLF);
		$retour2 = mysqli_query($DB,$sql);
		$row2 = mysqli_fetch_array($retour2, MYSQLI_BOTH);
	    $grad_FullName = $row2['Manufacturer'];
	    include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
	    include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
	    $grad_FullName = $grad_FullName . "_gradateur";
        $gradateur = new $grad_FullName();
		if ($row2['valeur']>$row['output_value']) {
		  // Light DONW
		  //$sens => 0 : incrément & 2 : décrément
		  $gradateur->varier($row2['carte'], $row2['sortie'], 2, $row['output_value'], $row['delay']);
		  //echo("Lamp Eteindre:".$row2['carte'].",".$row2['sortie'].",".$row['delay'].",".$row['output_value']."<br>");
		} else {
		  // Light UP
		  $gradateur->varier($row2['carte'], $row2['sortie'], 0, $row['output_value'], $row['delay']);
		  //echo("Lamp Allumer:".$row2['carte'].",".$row2['sortie'].",".$row['delay'].",".$row['output_value']."<br>");
		}
		//$gradateur->inverser($row2['carte'], $row2['sortie'], $row['delay'], $row['output_value']);
		//echo("Lamp Invert:".$row2['carte'].",".$row2['sortie'].",".$row['delay'].",".$row['output_value']."<br>");
	  } // END IF
	  if ($row['mode']=="MEM") {
	    // Memory
	    $sql = "SELECT * FROM `ha_element` WHERE `id`='".$row['memory_number']."'";
		//echo("MEM SQL=$sql".CRLF);
		$retour2 = mysqli_query($DB,$sql);
		$row2 = mysqli_fetch_array($retour2, MYSQLI_BOTH);
	    $grad_FullName = $row2['Manufacturer'];
	    include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
	    include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
	    $grad_FullName = $grad_FullName . "_gradateur";
        $gradateur = new $grad_FullName();
	    $gradateur->restaurerMemoire($row2['card_id'], $row2['element_reference'], $row['delay']);
		//echo("MEM Restore:".$row2['card_id'].",".$row2['element_reference'].",".$row['delay']."<br>");
	  } // END IF
      sleep(1);
    } // END WHILE
    mysqli_close($DB);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  } // END FUNCTION vibecall

    /* CONNEXION BDD */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  /* AFFICHAGE DES PLANS ET DES Ambiances */
  $retour0 = mysqli_query($DB,"SELECT `page_name` FROM `ha_vibe_pages`;");
  $k=0;
  while( $row0 = mysqli_fetch_array($retour0, MYSQLI_BOTH) ) {

    if ( $k==0 ) {
      $_XTemplate->assign('CACHER', 'display: block;');
    }
    else {
      $_XTemplate->assign('CACHER', 'display: none;');
    }
	$k++;


    // $sql = "SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `localisation` = '" . $row0['lieu'] . "';";
	$sql = "SELECT * FROM `ha_vibes` WHERE `page`='".$row0['page_name']."';;";
	$retour = mysqli_query($DB,$sql);
    while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      $_XTemplate->assign('IMG_X',           $row['img_x']);
      $_XTemplate->assign('IMG_Y',           $row['img_y']);
	  $_XTemplate->assign('VIBE_ID',         $row['id']);
	  $_XTemplate->assign('VIBE_DESC',       $row['description']);
      $_XTemplate->parse('main.PLAN.VIBE');
    } // END WHILE
    $_XTemplate->assign('LOCALISATION', str_replace(" ","",$row0['page_name']));
	$_XTemplate->assign('PLANNAME', $row0['page_name']);
    $_XTemplate->parse('main.PLAN');
  } // END WHILE

  /* AFFICHAGE DES NIVEAUX */
  $retour = mysqli_query($DB,"SELECT `page_name` FROM `ha_vibe_pages`;");
  while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
    $_XTemplate->assign('LOCALISATION', str_replace(" ","", $row['page_name']));
	$_XTemplate->assign('PLANNAME', $row['page_name']);
    $_XTemplate->parse('main.NIVEAU');
  }

  /* FERMETURE BDD */
  mysqli_close($DB);

?>
