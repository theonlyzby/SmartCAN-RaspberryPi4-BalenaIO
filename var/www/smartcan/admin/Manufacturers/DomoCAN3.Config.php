<?PHP
// Includes
include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function ModConfig (Admin COnfig of the Module
function ModConfig() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  global $Linux_Mode;
  
  // Includes
  include_once "./lang/admin.manufacturer.DomoCAN3.php";

  // First Admin Use?
  $sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
  $query            = mysqli_query($DB,$sql);
  $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
  $First_Use_Admin = $row['value'];

  // Action Requested via Form?  
  $action = html_postget("action");

  // Table Reset Request?
  if ($action=="truncate") {
    // Truncate ha_subsystem Table
	$sql = "TRUNCATE TABLE `ha_subsystem`;";
	echo("TRNUCATE, SQL=$sql<br>");
	$query = mysqli_query($DB,$sql);
    // Truncate ha_element Table
	$sql = "TRUNCATE TABLE `ha_element`;";
	$query = mysqli_query($DB,$sql);
	
	echo("<SCRIPT LANGUAGE=\"JavaScript\">" . CRLF);
	echo("  document.location.href=\"index.php?page=Modules&SubMenu=".html_postget("SubMenu")."\" " . CRLF);
	echo("</SCRIPT>" . CRLF);
  } // END IF
  
  // Parse Files
  if ($action=="Refresh_Sysmap") {
    // Correct Posting?
    if ((($_FILES['Cartes_CFG']['error'] > 0) || ($_FILES['Cartes_CFG']['name']!="Cartes.cfg") || ($_FILES['Cartes_CFG']['size']==0)) 
        || (($_FILES['In16Name_CFG']['error'] > 0) || ($_FILES['In16Name_CFG']['name']!="In16Name.cfg") || ($_FILES['In16Name_CFG']['size']==0))
        || (($_FILES['GradNameS_CFG']['error'] > 0) || ($_FILES['GradNameS_CFG']['name']!="GradNameS.cfg") || ($_FILES['GradNameS_CFG']['size']==0))
        || (($_FILES['GradNameM_CFG']['error'] > 0) || ($_FILES['GradNameM_CFG']['name']!="GradNameM.cfg") || ($_FILES['GradNameM_CFG']['size']==0)) )	{
      echo("Files Transfer ERROR or Incorrect FileS!");
    } else {
	  if ($First_Use_Admin=="1") {
	    //
	    // Increase Admin First visit ... next step
	    //
	    $sql = "UPDATE `domotique`.`ha_settings` SET `value` = '2' WHERE `ha_settings`.`variable` = 'first_use_admin';";
	    $query = mysqli_query($DB,$sql);
	    $First_Use_Admin = "2";
	    echo("<table><tr><td width=\"40%\">&nbsp;</td><td>".CRLF);
	    echo("<br>&nbsp;<br>".$msg["DomoCAN3"]["InstallSuccess"][$Lang]."<br>".CRLF);
	    echo("<span class=\"readmore_b\"><a class=\"readmore\" href=\"index.php?page=Therm\" style=\"color: white; align=middle;\" ;\">".$msg["DomoCAN3"]["Next"][$Lang]."</a></span><br>".CRLF);
	    echo("<br><br>".CRLF);
	    echo("</td><td width=\"40%\">&nbsp;</td></tr></table>".CRLF);
	  } // END IF
	  //
      // Parse Cartes.cfg
	  //
	  $file_name = $_FILES['Cartes_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0;
      while (false !== ($char = fgetc($fp))) {
	    if (ord($char)==0) {
	      // New Card
		  if ($Card==0) {
	        // Horloge
		    // Card Name
		    $Card_Name[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Name[$Card] .= fgetc($fp);
		    } // END FOR
		    // Card Desc
		    $Card_Desc[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Desc[$Card] .= fgetc($fp);
		    } // END FOR
		    // Card Number
		    $Card_Number[$Card] = ord(fgetc($fp));
		    $char = fgetc($fp);
		    // Card Type
		    $Card_Type[$Card] = 20;
		    // Increase Card Count
		    $Card++;
	      } else {
	        // Other Cards
		    // Card Type
		    $Card_Type[$Card] = dechex(ord(fgetc($fp)));
		    // Card Number
		    $Card_Number[$Card] = dechex(ord(fgetc($fp)));
		    // Card Name
		    $Card_Name[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Name[$Card] .= fgetc($fp);
		    } // END FOR
		    // Card Desc
		    $Card_Desc[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Desc[$Card] .= fgetc($fp);
		    } // END FOR
		    $char = fgetc($fp);
		    $char = fgetc($fp);
		    // Increase Card Count
		    $Card++;
		  } // END IF
		  if ($Card_Name[$Card-1]) {
		    $Card_Number[$Card-1] = str_pad(dechex($Card_Number[$Card-1]),2, "0", STR_PAD_LEFT);
		    // Add or Modify Card in DB
		    $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_subsystem` WHERE Manufacturer='DomoCAN3' AND Type='0x".$Card_Type[$Card-1]."' AND Reference='0x".$Card_Number[$Card-1]."';"));
		    if ($count == 1) {
			  mysqli_query($DB,"UPDATE `ha_subsystem` SET Name='".utf8_encode($Card_Name[$Card-1])."' WHERE Manufacturer='DomoCAN3' AND Type='0x".$Card_Type[$Card-1]."' AND Reference='0x".$Card_Number[$Card-1]."';");
		    } else {
			  mysqli_query($DB,"INSERT INTO `ha_subsystem` (id,Manufacturer,Type,Reference,Name) VALUES ('','DomoCAN3','0x".$Card_Type[$Card-1]."','0x".$Card_Number[$Card-1]."','".utf8_encode($Card_Name[$Card-1])."');");
		    } // End IF
		    //echo("<b>New Card: </b>Name=".$Card_Name[$Card-1].", Type=".$Card_Type[$Card-1]." (".$Card_Desc[$Card-1]."), Number=".$Card_Number[$Card-1]."<br>");
		  } // END IF
	    } // END IF
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>");
      } // END WHILE

      //
      // Parse In16Name.cfg
      //
      //echo("<br><b>IN 16 Cards</b><br><br>");
      // Parse File
	  $file_name = $_FILES['In16Name_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0; $In_Num=0;
      while (false !== ($char = fgetc($fp))) {
	    //if ($In_Num==0) { echo("<b>Card 0x".dechex($Card)."?</b><br>"); }
	    $In_Desc[$Card][$In_Num]=$char; $In_FCS=ord($char);
	    for ($x=1; $x<=7; $x++) {
	      $char = fgetc($fp);
		  $In_Desc[$Card][$In_Num] .= $char;
		  $In_FCS = $In_FCS + ord($char);
	    } // END FOR
	    if ($In_FCS!=0) {
		  // Add or Modify Input in DB
		  $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_element` WHERE `Manufacturer`='DomoCAN3' AND`card_id` = '0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND " .
												"`element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x22' ;"));
		  if ($count == 1) {
		    // Update
			mysqli_query($DB,"UPDATE `ha_element` SET `element_name`='".utf8_encode($In_Desc[$Card][$In_Num])."' WHERE `Manufacturer`='DomoCAN3' AND`element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' " .
						"AND `card_id`='0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x22';");
		  } else {
		    // Create
			mysqli_query($DB,"INSERT INTO `ha_element` ( `id`,`Manufacturer`, `card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL, 'DomoCAN3', '0x" . str_pad(dechex($Card),2, "0", STR_PAD_LEFT) . "', '0x22', '0x" . str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT) .
			"', '" . utf8_encode($In_Desc[$Card][$In_Num]) . "');");
		  } // END IF
		  //echo("IN 0x".dechex($In_Num)."=".$In_Desc[$Card][$In_Num]."<br>");
		} // END IF
	    $In_Num++;
	    if ($In_Num==16) { $In_Num=0; $Card++; }	  
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>");	   
      } // END WHILE

      //
      // Parse GradNameS.cfg
      //
      //echo("<br><b>GRAD 16 Cards<br><br>GRAD16 - Outputs</b><br><br>");
      // Parse File
	  $file_name = $_FILES['GradNameS_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0; $In_Num=0;
      while (false !== ($char = fgetc($fp))) {
	    //if ($In_Num==0) { echo("<b>Card 0x".dechex($Card)."?</b><br>"); }
	    $In_Desc[$Card][$In_Num]=$char; $In_FCS=ord($char);
	    for ($x=1; $x<=7; $x++) {
	      $char = fgetc($fp);
		  $In_Desc[$Card][$In_Num] .= $char;
		  $In_FCS = $In_FCS + ord($char);
	    } // END FOR

	    if ($In_FCS!=0) {
		  // Add or Modify Output in DB
		  $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_element` WHERE `Manufacturer`='DomoCAN3' AND `card_id` = '0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND " .
												"`element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x11' ;"));
		  if ($count == 1) {
		    // Update
			mysqli_query($DB,"UPDATE `ha_element` SET `element_name`='".utf8_encode($In_Desc[$Card][$In_Num])."' WHERE `Manufacturer`='DomoCAN3' AND `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' " .
							"AND `card_id`='0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x11';");
		  } else {
		    // Create
			mysqli_query($DB,"INSERT INTO `ha_element` (`id`, `Manufacturer`, `card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL,'DomoCAN3', '0x" . str_pad(dechex($Card),2, "0", STR_PAD_LEFT) . "', '0x11', '0x" . str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT) .
			"', '" . utf8_encode($In_Desc[$Card][$In_Num]) . "');");
		  } // END IF
		  //echo("OUT 0x".dechex($In_Num)."=".$In_Desc[$Card][$In_Num]."<br>");
		} // END IF
	    $In_Num++;
	    if ($In_Num==16) { $In_Num=0; $Card++; }	  
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>");
      } // END WHILE

      //
      // Parse GradNameM.cfg
      //
      //echo("<br><b>GRAD16 - Memories</b><br><br>");
      // Parse File
	  $file_name = $_FILES['GradNameM_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0; $In_Num=0;
      while (false !== ($char = fgetc($fp))) {
	    //if ($In_Num==0) { echo("<b>Card 0x".dechex($Card)."?</b><br>"); }
	    $In_Desc[$Card][$In_Num]=$char; $In_FCS=ord($char);
	    for ($x=1; $x<=15; $x++) {
	      $char = fgetc($fp);
		  $In_Desc[$Card][$In_Num] .= $char;
		  $In_FCS = $In_FCS + ord($char);
	    } // END FOR
	    if ($In_FCS!=0) {
		  // Add or Modify Memory in DB
		  $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_element` WHERE `Manufacturer`='DomoCAN3' AND `card_id` = '0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND " .
												"`element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x16' ;"));
		  if ($count == 1) {
		    // Update
			mysqli_query($DB,"UPDATE `ha_element` SET `element_name`='".utf8_encode($In_Desc[$Card][$In_Num])."' WHERE `Manufacturer`='DomoCAN3' AND `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' " .
							"AND `card_id`='0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x16';");
		  } else {
		    // Create
			mysqli_query($DB,"INSERT INTO `ha_element` (`id`, `Manufacturer`,`card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL, 'DomoCAN3', '0x" . 
							str_pad(dechex($Card),2, "0", STR_PAD_LEFT) . "', '0x16', '0x" . str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT) .
			"', '" . utf8_encode($In_Desc[$Card][$In_Num]) . "');");
		  } // END IF
		  //echo("MEMORY 0x".dechex($In_Num)."=".$In_Desc[$Card][$In_Num]."<br>");
		} // END IF
	    $In_Num++;
	    if ($In_Num==15) { $In_Num=0; $Card++; }	  
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>"); 
      } // END WHILE
    } // END IF
  } // END IF
    
  // CAN Speed
  $DomoCANSpeed="UNKNOWN";
  $myFile  = "/etc/network/interfaces";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'pre-up ip link set $IFACE type can bitrate')!==false)  { $DomoCANSpeed = substr($line, 43,-1); }
  } // END WHILE
  fclose($reading);  

  // DomoCAN V3 Mode and IP
  $myFile  = $_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/init_config.php";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'DomoCANMode')!==false)  { $DomoCANMode = substr($line, 15,7); }
	if (strpos($line,'DomoCANBridge')!==false)  { $DomoCANIP = substr($line, 17,-1); }
	if (strpos($line,'DomoCANPort')!==false)  { $DomoCANPort = substr($line, 15,-1); }
  } // END WHILE
  fclose($reading);
	
  //echo("Action=$action<br>");
  // Action Request?	
  if ($action!="") {
    // DomoCAN V3 Server Mode & Bridge Config
	$NEW_DomoCANMode = html_postget("DomoCANMode");
	$NEW_DomoCANIP   = html_postget("DomoCANIP");
	$NEW_DomoCANPort = html_postget("DomoCANPort");
	$replaced  = false;
    // DomoCAn Server config in config.php if ADRESSE_INTERFACE or PORT_INTERFACE changed
	if (($NEW_DomoCANIP!=ADRESSE_INTERFACE) || ($NEW_DomoCANPort!=PORT_INTERFACE) || (($NEW_DomoCANMode=="BridgeA") && ($NEW_DomoCANMode!=$DomoCANMode))) {
	  //echo("Changes: DomoCANMode=$NEW_DomoCANMode, DomoCANIP=$NEW_DomoCANIP/$DomoCANIP, DomoCANPort=$NEW_DomoCANPort/$DomoCANPort,<br>");
	  $myFile = $_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php";
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($myFile.".tmp","w");
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		if (strpos($line,"define('ADRESSE_INTERFACE',")!==false) {
		  if ($NEW_DomoCANMode=="BridgeA") { $NEW_DomoCANIP="localhost"; }
		  fwrite($writing,"  define('ADRESSE_INTERFACE', '".$NEW_DomoCANIP."');" .chr(13).chr(10));
		} else {
		  if (strpos($line,"define('PORT_INTERFACE',")!==false) {
		    fwrite($writing,"  define('PORT_INTERFACE', '".$NEW_DomoCANPort."');" .chr(13).chr(10));
		  } else {
		    fwrite($writing,$line);
		  } // END IF
		} // END IF
	  } // END WHILE
	  shell_exec("sudo cp -f ".$myFile.".tmp ".$myFile);
	  //shell_exec("sudo rm -f ".$myFile.".tmp");
	} // END IF
	
    // CAN Speed Changed?
	if (($DomoCANSpeed!="ERROR") && ($DomoCANSpeed!=html_postget("DomoCANSpeed"))) {
	  $myFile = "/etc/network/interfaces";
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/interfaces.tmp","w");
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		if (strpos($line,'pre-up ip link set $IFACE type can bitrate')!==false) {
		  fwrite($writing,"pre-up ip link set \$IFACE type can bitrate " . html_postget("DomoCANSpeed") .chr(13).chr(10));
		  $DomoCANSpeed=html_postget("DomoCANSpeed");
		  $replaced  = true;
		} else {
		  fwrite($writing,$line);
		} // END IF
	  } // END WHILE
	  fclose($reading); 
	  fclose($writing);
	  shell_exec("sudo cp -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/interfaces.tmp /etc/network/interfaces");
	  shell_exec("sudo rm -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/interfaces.tmp");
	} // END IF
	
	if ( (($NEW_DomoCANMode!=$DomoCANMode) || ($NEW_DomoCANIP!=$DomoCANIP) || ($NEW_DomoCANPort!=$DomoCANPort))) {
	  //echo("Changes: DomoCANMode=$NEW_DomoCANMode/$DomoCANMode, DomoCANIP=$NEW_DomoCANIP/$DomoCANIP, DomoCANPort=$NEW_DomoCANPort/$DomoCANPort,<br>");
	  $myFile = $_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/init_config.php";
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($myFile.".tmp","w");
	  // Parse config file
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		$needle = substr($line,0,15);
		if (($needle=="// DomoCANMode=") && ($NEW_DomoCANMode!=$DomoCANMode) && ($NEW_DomoCANMode!="")) {
		  // Change Password in file
		  if ($NEW_DomoCANMode=="BridgeO") { fwrite($writing,"// DomoCANMode=BridgeONLY"   .chr(13).chr(10)); }
		  if ($NEW_DomoCANMode=="BridgeA") { fwrite($writing,"// DomoCANMode=BridgeAndWeb" .chr(13).chr(10)); }
		  if ($NEW_DomoCANMode=="WebONLY") { fwrite($writing,"// DomoCANMode=WebONLY"      .chr(13).chr(10)); }		  
		  $replaced = true;
		} else {
		  if (($needle=="// DomoCANBridg") && ($NEW_DomoCANIP!="")) {
		    // Change DomoCAN V3 bridge IP
			fwrite($writing,"// DomoCANBridge=" . $NEW_DomoCANIP .chr(13).chr(10));
			$DomoCANIP = $NEW_DomoCANIP;
		  } else {
		    if (($needle=="// DomoCANPort=") && ($NEW_DomoCANPort!="")) { 
			  // Change DomoCAN V3 Bridge Port
			  fwrite($writing,"// DomoCANPort=" . $NEW_DomoCANPort .chr(13).chr(10));
			  $DomoCANPort = $NEW_DomoCANPort;
			} else {
		      // write line
		      fwrite($writing,$line);
			} // END IF
		  } // END IF
		} // END IF
      } // END WHILE      
      fclose($reading); 
	  fclose($writing);
	  shell_exec("sudo cp -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/init_config.php.tmp ".$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/init_config.php");
	  //shell_exec("sudo rm -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/init_config.php.tmp");
	  // swap files and restart domocan-server
	  
	  //$replaced  = false;
	  
	  if ($replaced) {
        //copy($myFile.".tmp", $myFile);
		shell_exec("sudo ps aux | grep -ie domocan-server | awk '{print $2}' | xargs kill -9");
		if ($NEW_DomoCANMode=="BridgeO") { $EXE_File = "domocan-bridge"; }
		if ($NEW_DomoCANMode=="BridgeA") { $EXE_File = "domocan-bridge-and-web"; }
		if ($NEW_DomoCANMode=="WebONLY") { $EXE_File = "server_udp"; }
		shell_exec("sudo cp -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/bin/" . $EXE_File . " ".$_SERVER['DOCUMENT_ROOT']."/smartcan/bin/domocan-server");
		//shell_exec("sudo /etc/init.d/domocan-init start");
		
		echo("<h2 class='title' align='middle'>".$msg["MAIN"]["Rebooting"][$Lang]." !<br><br>");
	    echo($msg["MAIN"]["bepatient"][$Lang]." ... <div style='display:inline' id=\"compterebours\"><noscript>".$msg["MAIN"]["reload"][$Lang] ."<br><br>&nbsp;</h2>");
	    echo("<script type=\"text/javascript\">" . CRLF);
	    echo("var decompte = 120;" . CRLF);
	    echo("setTimeout(\"compte_a_rebours();\", 1000);" . CRLF);
	    echo("function compte_a_rebours() {" . CRLF);
	    echo("  document.getElementById(\"compterebours\").innerHTML = decompte;" . CRLF);
	    echo("  decompte--;" . CRLF);
	    echo("  var actualisation = setTimeout(\"compte_a_rebours();\", 1000);" . CRLF);
	    echo("}" . CRLF);
	    echo("setTimeout(function () { window.location.href = \"/smartcan/admin\"; }, 120000);setTimeout(function () { window.location.reload(); }, 122000);" . CRLF);
	    echo("</script>" . CRLF);
		
		if ($Linux_Mode=="balena.io") { 
		   shell_exec('curl -X POST --header "Content-Type:application/json" --data \'{"force": true}\' "$BALENA_SUPERVISOR_ADDRESS/v1/update?apikey=$BALENA_SUPERVISOR_API_KEY"');
	    } else {
	       shell_exec("sleep 3 && sudo /sbin/shutdown -h now");
	    } // END IF
	    //shell_exec("( sleep 3 ; sudo /sbin/shutdown -r now )");		
      } // END IF
	  $DomoCANMode = $NEW_DomoCANMode;
	} // END IF
	  } // END IF

  // Start Build Page ...
  echo("<h2 class='title'> ".$msg["DomoCAN3"]["Title"][$Lang]." </h2>");
  echo("<div class='post_info'><b>".$msg["DomoCAN3"]["DomoCANserver"][$Lang].":</b>&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

  <?PHP
    // First Admin Use?
  if ($First_Use_Admin=="1") {
    echo("<div id='text-11' class='block widget_text'>");
	echo("<h2><font color=\"#33A5FF\"><b>".$msg["DomoCAN3"]["FirstUSe"][$Lang].":<br><br>");
	echo($msg["DomoCAN3"]["FUDesc1"][$Lang]." <br><br>");
	echo($msg["DomoCAN3"]["FUDesc2"][$Lang]." <br><br>&nbsp;&nbsp;&nbsp;&nbsp; ");
	echo($msg["DomoCAN3"]["FUDesc3"][$Lang]);
	echo("<br><br>&nbsp;&nbsp;&nbsp; ".$msg["DomoCAN3"]["FUDesc4"][$Lang]."<br>");
	echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
			"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
			"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
			"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
			"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2");
	echo("<br>&nbsp;<img src='./images/ArrowRightBlueGloss.gif' style=\"float:right;vertical-align:middle\" />");
	echo("&nbsp;<img src='./images/ArrowDownBlueGloss.gif' style=\"float:right;vertical-align:middle\" /></b></font></h2></div><br><br><br>");
  } // END IF
  
  echo("<form name='pos' id='pos' action='./index.php?page=Modules' method='post' enctype='multipart/form-data'>" . CRLF);
  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  echo("<input type='hidden' name='SubMenu' value='".html_postget("SubMenu")."'/>" . CRLF);
  echo("<table>" . CRLF);

  // Is DomoCAN V3 Server running?
  $u=shell_exec("sudo ps -eo etime,args | grep -ie domocan-server | grep -v grep | awk '{print $1}'");
  echo("<tr><td><b>".$msg["MAIN"]["Status"][$Lang]."</b></td><td>");
  if ($u!="") {
	echo("<font color=green><b>".$msg["DomoCAN3"]["RunningSince"][$Lang]." $u ;-)</b></font>");
  } else {
	echo("<font color=red><b>".$msg["DomoCAN3"]["Stopped"][$Lang]."!!! 8-[</b></font>");
  } // END IF
  echo("</td></tr>");
  
  // SmartCAN Mode
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");
  echo("<tr><td><b>".$msg["DomoCAN3"]["WorkingMode"][$Lang]."</b></td><td><select name='DomoCANMode' id='DomoCANMode' OnChange='ModeToggle();'>");
  echo("<option value='' "); echo(">".$msg["DomoCAN3"]["NoneSelected"][$Lang]."!</option>" . CRLF);
  echo("<option value='BridgeO' "); if ($DomoCANMode=="BridgeO") { echo("selected"); } echo(">Bridge ONLY</option>" . CRLF);
  echo("<option value='BridgeA' "); if ($DomoCANMode=="BridgeA") { echo("selected"); } echo(">Bridge AND Web</option>" . CRLF);
  echo("<option value='WebONLY' "); if ($DomoCANMode=="WebONLY") { echo("selected"); } echo(">Web ONLY</option>" . CRLF);
  echo("</select></td></tr>");
  
  // DomoCAN V3 Bridge IP
  echo("<tr id='BridgeIP' style='visibility: hidden;'><td><b>IP Addresse Bridge CAN</b><br>(EZL, LAN Tiger ou RaZbyBridge)&nbsp;&nbsp;&nbsp;</td>" . CRLF);
  echo("<td><input type='text' name='DomoCANIP' id='DomoCANIP' value='".$DomoCANIP."'/></td></tr>" . CRLF);
  // DomoCAN V3 Bridge Port
  echo("<tr id='BridgePort' style='visibility: hidden;'><td><b>Port UDP Bridge</b></td>" . CRLF);
  echo("<td><input type='text' name='DomoCANPort' id='DomoCANPort' value='".$DomoCANPort."'/></td></tr>" . CRLF);
  // DomoCAN V3 Bridge Speed
  echo("<tr id='BridgeSpeed' style='visibility: hidden;'><td><b>CAN Bus Speed</b></td>" . CRLF);
  echo("<td><input type='text' name='DomoCANSpeed' id='DomoCANSpeed' value='".$DomoCANSpeed."'/></td></tr>" . CRLF);

  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>" . CRLF);
  
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"Modify\",1);'><img src='./images/save.png' width='72px' heigth='72px' /></a></td></tr>");
  
  echo("</table>" . CRLF);
  //echo("</form>" . CRLF);
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

?>



<div id="rss-3" class="block widget_rss">
<ul>
<?PHP

  
  // First Admin Use?
  if ($First_Use_Admin=="1") {
  } else {
  
  echo("</ul>" . CRLF);
  echo("</div>" . CRLF);
    // DomoCAN v3 System Components
  echo("<div class='post_info'><b>".$msg["DomoCAN3"]["SystComponents"][$Lang].":</b>&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
  // Lists Cards
  $sql = "SELECT `ha_subsystem`.`Manufacturer` AS Manufacturer, `ha_subsystem_types`.`Description` AS Card_Subsys_Name, `ha_subsystem`.`Type` AS Card_Type, `ha_subsystem`.`Reference` AS Card_Reference, " .
			"`ha_subsystem`.`Name` AS Card_Name FROM `ha_subsystem`, `ha_subsystem_types` WHERE `ha_subsystem`.`Manufacturer`='DomoCAN3' AND `ha_subsystem`.`Manufacturer`=`ha_subsystem_types`.`Manufacturer` AND ".
			"`ha_subsystem`.`Type` = `ha_subsystem_types`.`Type` LIMIT 25;";
  //$sql = mysqli_real_escape_string("SELECT * FROM `ha_subsystem` WHERE 1;");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysqli_query($DB,$sql);
  echo("<table width=\"100%\">" . CRLF);
  echo("<tr><td width=\"40%\">".$msg["DomoCAN3"]["SubSystem"][$Lang]."</td>" . CRLF);
  echo("<td width=\"15%\">".$msg["DomoCAN3"]["Reference"][$Lang]."</td>" . CRLF);
  echo("<td width=\"15%\">".$msg["MAIN"]["Name"][$Lang]."</td>" . CRLF);
  echo("<td width=\"30%\">".$msg["DomoCAN3"]["Reference"][$Lang]."</td></tr>" . CRLF);
  $border_style = "";
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $Manufacturer = $row['Manufacturer'];
	$Subsys_Name  = $row['Card_Subsys_Name'];
    $Type         = $row['Card_Type'];
    $Reference    = $row['Card_Reference'];
    $Name         = $row['Card_Name'];
    // Display on page
	//echo("<input type='hidden' name='page' id ='page' value='Outputs'/>" . CRLF);
	$border_style = " style='border-top-style: groove; border-top-color: silver; border-top-width: medium;'";
	echo("<tr><td width=\"40%\" " . $border_style . ">" . $Manufacturer . " / <b>" . $Subsys_Name . "</b></td>" . CRLF);
	echo("<td width=\"15%\" " . $border_style . ">" . $Reference . "</td>" . CRLF);
	echo("<td width=\"15%\" " . $border_style . ">" . $Name . "</td>" . CRLF);
	//echo("<td width=\"30%\">&nbsp;</td></tr>" . CRLF);
	
	// Elements
	$sql = "SELECT HE.`element_name` AS El_Name, HE.`element_reference` AS El_Ref FROM `ha_element_types` AS HET LEFT JOIN   `ha_element` AS HE ON (HET.`Type` = HE.`element_type` ) " .
			"WHERE HET.`Manufacturer`= '" . $Manufacturer . "' AND HET.`subsystem_type`='" . $Type . "' AND HE.`card_id`='" . $Reference . "';";
	$el_query = mysqli_query($DB,$sql);
	$i=0;
	while ( $el_row = mysqli_fetch_array($el_query, MYSQLI_BOTH) ) {
	  $El_Name = $el_row['El_Name'];
	  $El_Ref  = $el_row['El_Ref'];
	  if ($i!=0) {
	    echo("<tr><td width=\"40%\" " . $border_style . ">&nbsp;</td>" . CRLF);
	    echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
	    echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
	  } // END IF
	  $i++;
	  echo("<td width=\"30%\" " . $border_style . ">" . $El_Name. " (" . $El_Ref . ")</td></tr>" . CRLF);
	  $border_style = "";
	} // END WHILE
	if ($i==0) { echo("<td width=\"30%\" " . $border_style . ">&nbsp;</td></tr>" . CRLF); }
	    $i++;
  } // End While
  echo("<tr><td width=\"40%\" " . $border_style . ">&nbsp;</td>" . CRLF);
  echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
  echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
  echo("<td width=\"30%\" " . $border_style . ">&nbsp;</td></tr>" . CRLF);
  echo("</table>" . CRLF);

  } // END IF
  
  //echo("</ul>" . CRLF);
  //echo("</div>" . CRLF);
  
  echo("</div>" . CRLF);
  echo("</div></div>" . CRLF);

  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . $msg["DomoCAN3"]["SideTitle"][$Lang] . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  echo("<br><br><br>". $msg["DomoCAN3"]["NotUptoDate"][$Lang] . CRLF);

  
  //echo("<form name='pos' method='post' id='ChangeLamp'".
  //     " action='" . htmlentities($_SERVER['PHP_SELF']) ."' enctype='multipart/form-data'>" . CRLF);
  //echo("<input type='hidden' name='page' value='Modules'/>" . CRLF);
  //echo("<input type='hidden' name='SubMenu' value='".html_postget("SubMenu")."'/>" . CRLF);
?>
  </ul></div>


<div class="postcontent">
  <br>&nbsp;<br><?php echo($msg["DomoCAN3"]["EmptyIncomplete"][$Lang]); ?><br>
  <span class="readmore_b"><a class="readmore" href="javascript:void(1);" style="color: white; align=middle;" onClick='showOverlay("NewSysMap","");';"><?php
	echo($msg["DomoCAN3"]["LoadDB"][$Lang]); ?></a></span><br>
<?php
if ($First_Use_Admin=="1") {
  echo("<span class=\"readmore_b\"><a class=\"readmore\" href=\"javascript:void(1);\" style=\"color: white; align=middle;\" onClick='submitform(\"Pass_Sysmap\",0);';\">".
		$msg["MAIN"]["Pass"][$Lang]."</a></span><br>");
}// END IF
?>
  <br><br> 
  <?php
  if ($First_Use_Admin!="1") { ?>
  Totalement incorrecte ?
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" style="color: white; align=middle;" onclick="submitform('truncate',1);"><?php echo($msg["DomoCAN3"]["Reset"][$Lang]); ?></a></span>
  <?php } // END IF ?>
	  <div class="clear"></div>
</div>

</div>
<script type="text/javascript">
ModeToggle();

function ModeToggle() {
  mode=document.getElementById("DomoCANMode").value;
  if (mode==="") {
    document.getElementById("BridgeIP").style.visibility="hidden";
	document.getElementById("BridgePort").style.visibility="hidden";
	document.getElementById("BridgeSpeed").style.visibility="hidden";
  }
  if (mode==="BridgeO") {
    document.getElementById("BridgeIP").style.visibility="hidden";
	document.getElementById("BridgePort").style.visibility="hidden";
	document.getElementById("BridgeSpeed").style.visibility="visible";
  }
  if (mode==="BridgeA") {
    document.getElementById("BridgeIP").style.visibility="hidden";
	document.getElementById("BridgePort").style.visibility="hidden";
	document.getElementById("BridgeSpeed").style.visibility="visible";
  }
  if (mode==="WebONLY") {
    document.getElementById("BridgeIP").style.visibility="visible";
	document.getElementById("BridgePort").style.visibility="visible";
	document.getElementById("BridgeSpeed").style.visibility="hidden";
  }
}
		

function submitform(action,ACK) {
  //alert("submit + Action="+action);  
  if ((ACK==0) || (confirm("<?php echo($msg["MAIN"]["RuSure"][$Lang]); ?>") && (ACK==1))) {
    document.getElementById('action').value = action;
    document.pos.submit();
  }
}

 var op = 0;
 
 function showOverlay(divID,Ifocus) {
 var o = document.getElementById(divID);
 SurImpose('main',divID);
 o.style.visibility = 'visible';
 o.style.opacity = 0.05;
 op=op+5;
 fadein(op,divID);
 document.getElementById(Ifocus).focus();
 }

function SurImpose(Ref,Obj) {
  oElement = document.getElementById(Ref);
  ToMove =  document.getElementById(Obj);
  var iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetTop;
    oElement = oElement.offsetParent;
  }
  ToMove.style.top = (iReturnValue+5)+"px";
  oElement = document.getElementById('header');
  iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetLeft;
    oElement = oElement.offsetParent;
  }
  ToMove.style.left = (iReturnValue+5)+"px";
  return true;
}
 
function fadein(op,divID) {

 var o = document.getElementById(divID);
 opa = op/100;
 
 o.style.opacity = opa;
 op=op+5;

 if(op>=105) { return; }
 var cmd = "fadein(" + op.toString() + ",'" + divID.toString() + "')";
 setTimeout(cmd,50);
}

 function hideOverlay(lID) {
 var o = document.getElementById(lID);
 o.style.visibility = 'hidden';
 }
 
</script>

<div id="NewSysMap" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('NewSysMap');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;Fermer</a>

<p align=center><h1 align=center><?php echo($msg["DomoCAN3"]["RefreshComponents"][$Lang]); ?></h1></p>
<br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo($msg["DomoCAN3"]["SubmitConfig"][$Lang]); ?><br><br><br>

     <table style="width: 80%; margin:auto">

	 <tr><td align="right"><label for="Cartes_CFG">Cartes.cfg :</label></td>
     <td><input type="file" name="Cartes_CFG" id="Cartes_CFG" /></td></tr>
	 
     <tr><td align="right"><label for="In16Name_CFG">In16Name.cfg :</label></td>
     <td><input type="file" name="In16Name_CFG" id="In16Name_CFG" /></td></tr>

     <tr><td align="right"><label for="GradNameS_CFG">GradNameS.cfg :</label></td>
     <td><input type="file" name="GradNameS_CFG" id="GradNameS_CFG" /></td></tr>
	 
     <tr><td align="right"><label for="GradNameM_CFG">GradNameM.cfg :</label></td>
     <td><input type="file" name="GradNameM_CFG" id="GradNameM_CFG" /></td></tr>
	 
     <tr><td>&nbsp;</td><td></td></tr>
	 </table>
	 
<div class="postcontent">
	 <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" style="color: white; align=middle;" onclick="submitform('Refresh_Sysmap',0);"><p><?php echo($msg["DomoCAN3"]["Submit"][$Lang]); ?></p></span>
	</div>

<br>

</div>
</form>

<?php
  mysqli_close($DB);
} // End of Function SysMap
