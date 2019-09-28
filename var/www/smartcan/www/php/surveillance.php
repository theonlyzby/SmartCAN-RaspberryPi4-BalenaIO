<?php
  
  // AJAX Debug
  //$xajax->configure('debug',true);

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'launchurl');
  $xajax->register(XAJAX_FUNCTION, 'arm');
  $xajax->register(XAJAX_FUNCTION, 'rec');
  
  /* FONCTIONS PHP AJAX */
  function launchurl($url) {
    $reponse = new XajaxResponse();
	$handle = fopen($url, "r");
	$reponse->script("$('#traitement').css('display', 'none')");
	return $reponse;
  }
  
  function arm($id) {
    $reponse = new XajaxResponse();
	// send CMD to camera
	$sql = "SELECT * FROM `ha_cameras` WHERE `id`=".$id.";";
	$query=mysqli_query($DB,$sql);
	$url  = $row['Camera_URL'];
	$pos  = strpos($url, "/");
	$pos2 = strpos($url, "/", $pos + 1);
	$pos  = strpos($url, "/", $pos2 + 1);
	$url  = substr($url,0,$pos); // http://ip
	$url .= "/adm/get_group.cgi?group=EVENT&event_trigger=".$val2; // ! Trigger, not Privacy!!!
	$auth = $row['Authentication']; 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	$headers = array(
      'Authorization: Basic '. $auth
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	
	$reponse->script("$('#traitement').css('display', 'none')");
	return $reponse;
  }
  
  function record($id) {
    $reponse = new XajaxResponse();
	
	$reponse->script("$('#traitement').css('display', 'none')");
	return $reponse;
  }
  
  // PHP functions
  function html_postget($in) {
	$out = "";
	if (isset($_GET[$in])) {
	  $out= $_GET[$in]; 
	} else { if (isset($_POST[$in])) { $out= $_POST[$in]; }} // End If
	return $out;
  } // End Function html_postget
  

  // CONNEXION BDD
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);
  
  // HTML post
  $action = html_postget("action");
  $val1   = html_postget("val1");
  $val2   = html_postget("val2");
  $cmd="Action=".$action.", val1=".$val1.", val2=".$val2."<br>";  
  // Changes requests than required a page reload (Privacy & Privacy ALL)
  if (($action=="Privacy") && ($val1)) {
    // send CMD to camera
	$sql = "SELECT * FROM `ha_cameras` WHERE `id`=".$val1.";";
	$query=mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($query, MYSQLI_BOTH);
	if ($row['Camera_Profile']=="Sercom") {
	  $url  = $row['Camera_URL'];
	  $pos  = strpos($url, "/");
	  $pos2 = strpos($url, "/", $pos + 1);
	  $pos  = strpos($url, "/", $pos2 + 1);
	  $url  = substr($url,0,$pos); // http://ip
	  if ($val2) { $val="start"; } else { $val="stop"; }
	  $url .= "/adm/privacy_ctl.cgi?privacy=".$val; // ! Trigger, not Privacy!!!
	  $auth = $row['Authentication']; 
	  //echo("url=$url, Auth=$auth<br>");
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL,$url);
	  $headers = array(
        'Authorization: Basic '. $auth
	  );
	  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  $result = curl_exec($ch);
	  curl_close($ch);
	} // END IF
	if (($row['Camera_URL']!="Sercom") || (($row['Camera_URL']=="Sercom") && ($result=="OK"))) {
	  mysqli_query($DB,"UPDATE `ha_cameras` SET `Privacy_Status`= ".$val2." WHERE `id`=".$val1.";");
	} // END IF
  } // END IF 
  
  // Privacy ALL
  if ($action=="PrivacyALL") {
    // send CMD to camera
	$sql = "SELECT * FROM `ha_cameras`;";
	$query=mysqli_query($DB,$sql);
	while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
	  if ($row['Camera_Profile']=="Sercom") {
	    $url  = $row['Camera_URL'];
	    $pos  = strpos($url, "/");
	    $pos2 = strpos($url, "/", $pos + 1);
	    $pos  = strpos($url, "/", $pos2 + 1);
	    $url  = substr($url,0,$pos); // http://ip
	    if ($val2) { $val="start"; } else { $val="stop"; }
	    $url .= "/adm/privacy_ctl.cgi?privacy=".$val; // ! Trigger, not Privacy!!!
	    $auth = $row['Authentication']; 
	    //echo("url=$url, Auth=$auth<br>");
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    $headers = array(
          'Authorization: Basic '. $auth
	    );
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
	    curl_close($ch);
	  } // END IF
	  if (($row['Camera_Profile']!="Sercom") || (($row['Camera_Profile']=="Sercom") && ($result=="OK"))) {
	    mysqli_query($DB,"UPDATE `ha_cameras` SET `Privacy_Status`= ".$val2." WHERE `id`=".$row['id'].";");
	  } // END IF
	} // END WHILE
  } // END IF 
  
  // Form
  echo("<form name='Surveillance' id='Surveillance' action='./index.php?page=surveillance' method='post'>" . CRLF);
  echo("<input type='hidden' id='action' name='action' value=''>". CRLF);
  echo("<input type='hidden' id='val1' name='val1' value=''>". CRLF);
  echo("<input type='hidden' id='val2' name='val2' value=''>". CRLF);

  
  // Local or Remote URL?
  $client_ip = $_SERVER["REMOTE_ADDR"];
  $sql = "SELECT * FROM `ha_cameras` WHERE 1;";
  $retour0 = mysqli_query($DB,$sql);
  // AFFICHAGE DES SOURCES VIDEO
  
  $k=0;
  while( $row0 = mysqli_fetch_array($retour0, MYSQLI_BOTH) ) {
    if ($k==0) {
	  if ((($action=="Privacy") || ($action=="PrivacyALL")) && ($val1!=$row0["id"])) {
	    $_XTemplate->assign('CACHER', 'display: none;');
	  } else {
        $_XTemplate->assign('CACHER', 'display: block;');
	  }
    } else {
	  if ((($action=="Privacy") || ($action=="PrivacyALL")) && ($val1==$row0["id"])) {
	    $_XTemplate->assign('CACHER', 'display: block;');
	  } else {
        $_XTemplate->assign('CACHER', 'display: none;');
	  }
    }
	$k++;
    $_XTemplate->assign('LOCALISATION', str_replace(" ","",$row0['Camera_name']));
	$_XTemplate->assign('VIDEOSOURCENAME', $row0['Camera_name']);

    // Camera URL (if not Privacy mode and have right to view!)
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"])) { $pageURL .= "s"; }
	$pageURL .= "://";
	$pageURL .= $_SERVER["HTTP_HOST"];

	//echo("<br><br><br>User=".$User_ID."<br>");
	if (((!substr_count($row0['Restrict_Users'], '*=*'.$User_ID.'*=*')) && ($row0['Restrict_Users']!="")) || ($row0['Privacy_Status']==true)) {
	  $pageURL = "./images/PrivacyON.jpg";
	} else {
	  $camID = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(14/strlen($x)) )),1,14);
	  $sqlR = "INSERT INTO `ha_cameras_temp` (`id`, `Camera_URL`, `Temp_URL`, `Authentication`, `Create_Date`) VALUES (NULL, '".$row0['Camera_URL']."', '".$camID.
			  "', '".$row0['Authentication']."', CURRENT_TIMESTAMP);;";
	  $retourR = mysqli_query($DB,$sqlR);
	  $pageURL = "../../homectrl/camera?camID=".$camID;
	} // END IF
	$_XTemplate->assign('CAMERAURL', $pageURL);

	$_XTemplate->assign('ARMFUNC', "<img src=\"./images/armed-off.png\" onclick=\"traitement(); xajax_arm('".$row0['id']."');\" title='Arm'/>");
	
	$_XTemplate->assign('REC', "<img src=\"./images/REC-off.png\" onclick=\"traitement(); xajax_rec('".$row0['id']."');\" title='Record'/>");
	
	$Privacy_Status = ""; $PrivRequ = "1"; if ($row0['Privacy_Status']==true) { $Privacy_Status = "ON"; $PrivRequ = "0"; }
	$_XTemplate->assign('PRIVACY', "<a href=\"javascript:void();\" onclick=\"SubmitForm('Privacy',".$row0['id'].",".$PrivRequ.");\">".
									"<img src=\"./images/privacy".$Privacy_Status.".png\"</a>");
	$_XTemplate->assign('PRIVACY_ALL', "<a href=\"javascript:void();\" onclick=\"SubmitForm('PrivacyALL',".$row0['id'].",".$PrivRequ.");\">".
									"<img src=\"./images/privacy-all".$Privacy_Status.".png\"</a>");
	
	if ($row0['PTZ_LEFT']!="")  { $_XTemplate->assign('PTZLEFT', "<img src=\"./images/PTZ-Left.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_LEFT']."');\"/>"); } else { $_XTemplate->assign('PTZLEFT', ""); }
	if ($row0['PTZ_RIGHT']!="") { $_XTemplate->assign('PTZRIGHT', "<img src=\"./images/PTZ-Right.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_RIGHT']."');\"/>"); } else { $_XTemplate->assign('PTZRIGHT', ""); }
	if ($row0['PTZ_UP']!="")    { $_XTemplate->assign('PTZUP', "<img src=\"./images/PTZ-Up.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_UP']."');\"/>"); } else { $_XTemplate->assign('PTZUP', ""); }
	if ($row0['PTZ_DOWN']!="")  { $_XTemplate->assign('PTZDOWN', "<img src=\"./images/PTZ-Down.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_DOWN']."');\"/>"); } else { $_XTemplate->assign('PTZDOWN', ""); }
	if ($row0['PTZ_POS1']!="")  { $_XTemplate->assign('PTZPOS1', "<img src=\"./images/PTZ-One.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_POS1']."');\"/>"); } else { $_XTemplate->assign('PTZPOS1', ""); }
	if ($row0['PTZ_POS2']!="")  { $_XTemplate->assign('PTZPOS2', "<img src=\"./images/PTZ-Two.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_POS2']."');\"/>"); } else { $_XTemplate->assign('PTZPOS2', ""); }
	if ($row0['PTZ_POS3']!="")  { $_XTemplate->assign('PTZPOS3', "<img src=\"./images/PTZ-Three.png\" onclick=\"traitement(); xajax_launchurl('".$row0['PTZ_POS3']."');\"/>"); } else { $_XTemplate->assign('PTZPOS3', ""); }
    $_XTemplate->parse('main.VIDEOSOURCE');
	
  } // END WHILE

  // AFFICHAGE DES CAMERAS
  $retour = mysqli_query($DB,"SELECT * FROM `ha_cameras`;");
  while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
    $_XTemplate->assign('LOCALISATION', str_replace(" ","", $row['Camera_name']));
	$_XTemplate->assign('VIDEOSOURCENAME', $row['Camera_name']);
    $_XTemplate->parse('main.NIVEAU');
  }

  // FERMETURE BDD
  mysqli_close($DB);
  
  echo("</form>");
?>
