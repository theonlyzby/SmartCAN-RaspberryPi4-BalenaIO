<script language="javascript" src="./js/selobjcode.js"> </script>
<?PHP
function Update_Exterior_Sensor($Sensor) {
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.Therm.php";
  //
  // First Admin Use?
  //
  $sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
  $query            = mysqli_query($DB,$sql);
  $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
  $First_Use_Admin = $row['value'];
  // First Admin Use Done ??? ... Next
  if ($First_Use_Admin=="3") {
    // YES ... Increase Counter
	$sql = "UPDATE `".TABLE_VARIABLES."` SET `value` = '4' WHERE `variable` = 'first_use_admin';";
	$query = mysqli_query($DB,$sql);
	$First_Use_Admin = "4";
	echo("<table><tr><td width=\"40%\">&nbsp;</td><td>".CRLF);
	echo("<br>&nbsp;<br>".$msg["MAIN"]["ChangeSaved"][$Lang]."<br>".CRLF);
	echo("<span class=\"readmore_b\"><a class=\"readmore\" href=\"index.php?page=Variables\" style=\"color: white; align=middle;\" ;\">".$msg["MAIN"]["next"][$Lang].
			"</a></span><br>".CRLF);
	echo("<br><br>".CRLF);
	echo("</td><td width=\"40%\">&nbsp;</td></tr></table>".CRLF);
  } // END IF
  //
  $sql = "SELECT COUNT(*) AS count FROM `".TABLE_CHAUFFAGE_SONDE."` WHERE `id_sonde`='" . $Sensor . "';";
  //echo($sql);
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  $Count = $row['count'];
  if ((strlen($Sensor)>3) && ($Count==0)) {
	$sql = "UPDATE `".TABLE_CHAUFFAGE_SONDE."` SET `id_sonde` = '" . $Sensor . "' WHERE `id`='1';";
	//echo("<br>".$sql);
	$query = mysqli_query($DB,$sql);
  } else {
	if (($First_Use_Admin!="2") && (substr($Sensor,0,2)=="28")) { echo("<font color=\"red\">".$msg["THERM"]["InvalidSensor"][$Lang]."</font>"); }
  } // END IF
} // END FUNCTION
  
// Main Function Therm
function Therm() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.Therm.php";
  
  // Action Requested via Form?  
  $action = html_postget("action");

  // Thermostat Name
  $myFile  = PATHBASE."/www/html/nest/nest.php";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'              <p id="nestTitleSMALL" class="nestTitle">')!==false)  { $nestName = substr($line, 55, strrpos($line,"<")-55); }
  } // END WHILE
  fclose($reading);
  // Modify Thermostat Name?
  $Form_nestName = html_postget("nestName");
  if (($nestName!=$Form_nestName) &&($Form_nestName!="")) {
    //echo("Change nestName from =".$nestName."=, to =".$Form_nestName."=<br>");
	//file_put_contents($myFile,str_replace("              <p id=\"nestTitle\" class=\"nestTitle\">".$nestName."</p>","              <p id=\"nestTitle\" class=\"nestTitle\">".
	//					$Form_nestName."</p>",file_get_contents($myFile)));
	file_put_contents($myFile,str_replace('<p id="nestTitleSMALL" class="nestTitle">'.$nestName."</p>",'<p id="nestTitleSMALL" class="nestTitle">'.$Form_nestName."</p>",file_get_contents($myFile)));
	$nestName=$Form_nestName;
  } // END IF
  
  // Yahoo Wheater Config (Find woeID on )
  $myFile  = PATHBASE."/www/js/weather.js";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,"    weatherLocationCode: '")!==false)  { $woeID = substr($line, 26, strrpos($line,"'")-26); }
  } // END WHILE
  fclose($reading);
  $Form_woeID = html_postget("woeID");
  if (($woeID!=$Form_woeID) &&($Form_woeID!="")) {
    //echo("Change woeID from =".$woeID."=, to =".$Form_woeID."=<br>");
	file_put_contents($myFile,str_replace("    weatherLocationCode: '".$woeID."'","    weatherLocationCode: '".$Form_woeID."'",file_get_contents($myFile)));
	$woeID=$Form_woeID;
  } // END IF
  
  // 1 Wire Config (Raspberry Config file)
  $myFile  = "/boot/config.txt";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'dtoverlay=w1-gpio-pullup,gpiopin=')!==false)  { $GPIOpin = substr($line, 33,-1); }
  } // END WHILE
  fclose($reading);
  $GPIO_Names=array(3=>'2',5=>'3',7=>'4',8=>'14',10=>'15',11=>'17',12=>'18',13=>'27',15=>'22',16=>'23',18=>'24',19=>'10',21=>'9',22=>'25',23=>'11',24=>'8',26=>'7');
  // Change 1-Wire GPIO + Reboot
  if ($action=="ChangeOWGPIO") {
    $OWGPIO = html_postget("OWGPIO");
    //echo("Change GPIO to pin=$OWGPIO!<br>");
	if ($OWGPIO!=$GPIOpin) {
	  // Modify config.txt and reboot
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($_SERVER['DOCUMENT_ROOT']."/smartcan/dists/config.tmp","w");
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		if (strpos($line,'dtoverlay=w1-gpio-pullup,gpiopin=')!==false) {
		  fwrite($writing,"dtoverlay=w1-gpio-pullup,gpiopin=" . $OWGPIO .chr(13).chr(10));
		} else {
		  fwrite($writing,$line);
		} // END IF
	  } // END WHILE
	  fclose($reading); 
	  fclose($writing);
	  shell_exec("sudo cp -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/dists/config.tmp /boot/config.txt");
	  shell_exec("sudo rm -f ".$_SERVER['DOCUMENT_ROOT']."/smartcan/dists/config.tmp");
	  echo("<h2 class='title' align='middle'>System REBOOTing !<br><br>");
	  echo($msg["MAIN"]["BePatient"][$Lang]."<div style='display:inline' id=\"compterebours\"><noscript>".$msg["THERM"]["PleaseReload"][$Lang]."</noscript></div>".
			$msg["MAIN"]["Seconds"][$Lang]."<br><br>&nbsp;</h2>");
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
	  sleep(2);
	  shell_exec("sudo /sbin/shutdown -r now");
	} // END IF
	
  } // END IF
  
  // Update External Sensor or Source
  if ($action=="Ext-Temp") {
    $ExtSensor = html_post("ExtSensor");
	// 1 Wire?
	if ($ExtSensor=="1") {
	  $ExtOneWire = html_post("ExtOneWire");
	  Update_Exterior_Sensor($ExtOneWire);
	} else {
	  if ($ExtSensor!="0") {
	    Update_Exterior_Sensor($ExtSensor);
		} // END IF
	} // END IF
  } // END IF
  
  if (html_post("HeaterMode")!="") {
    $sql = "UPDATE `".TABLE_CHAUFFAGE_CLEF."` SET `valeur` = ".html_post("HeaterMode")." WHERE `clef` = 'circulateureauchaude';";
	$query = mysqli_query($DB,$sql);
  } // END IF
  
  if (html_post("HeaterOUT")!="") {
    $Out = html_post("HeaterOUT"); if ($Out=="EMPTY") { $Out=""; }
	$sql = "UPDATE `".TABLE_CHAUFFAGE_CLEF."` SET `valeur` = ".$Out." WHERE `clef` = 'HeaterOUT';";
	$query = mysqli_query($DB,$sql);
  } // END IF
  
  if (html_post("BoilerOUT")!="") {
    $Out = html_post("BoilerOUT"); if ($Out=="EMPTY") { $Out=""; }
	$sql = "UPDATE `".TABLE_CHAUFFAGE_CLEF."` SET `valeur` = ".$Out." WHERE `clef` = 'BoilerOUT';";
	$query = mysqli_query($DB,$sql);
  } // END IF

  // Get Active exterior Temp source
  $sql = "SELECT * FROM `chauffage_sonde` WHERE id=1;";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  $Active_Ext_temp = $row['id_sonde'];
  
  // Get Web Temperature Sources List
  $file = file_get_contents(WEB_TEMP_SOURCES);
  if($file === FALSE) die($msg["THERM"]["ErrorLoadFile"][$Lang]."<br />\n");
  $file = str_replace("\r", "\n", str_replace("\r\n", "\r", $file)); // convert to *nix line feeds

  $lines = explode("\n", $file);
 // now an array of lines
 $output = array();
 foreach($lines as $oneline) {
   //if(substr($oneline, -2) !== 'US') continue;
   //if($oneline[0] === '!') continue;
   if ((substr($oneline,0,1) === '!') || (!substr($oneline,81,1))) continue;
   $output[] = decode_line($oneline);
 } // END FOR

  
  echo("<h2 class='title'>" . $msg["THERM"]["ThermTitle"][$Lang] . "</h2>");
  
  
  echo("<form id=\"ExtSensors\" name=\"ExtSensors\" method='post' action='" . $_SERVER['PHP_SELF'] . "?page=Therm'>" . CRLF);
  
  // Config Weather Module (location)
  echo("<div class='post_info' style=\"height: 100px;\"><b>".$msg["THERM"]["WeatherModule"][$Lang]."</b><br>&nbsp;<br>" . CRLF);
  echo("woeID = <input type=\"text\" name=\"woeID\" value=\"".$woeID."\">&nbsp;&nbsp; ".$msg["THERM"]["FindItOn"][$Lang]);
  echo("<a href='http://woeid.rosselliot.co.nz/' target='_blank'>http://woeid.rosselliot.co.nz/</a>" . CRLF);
  echo("</div>" . CRLF);
  // Exterior Temperature source selection
  echo("<div class='post_info' style=\"height: 100px;\"><b>".$msg["THERM"]["OutsideTemp"][$Lang]."</b><br>&nbsp;<br>" . CRLF);
  echo("<select name='ExtSensor' id='ExtSensor' onchange='ActivateSearchOneWire();'>" . CRLF);
  echo("<option value='0'>".$msg["THERM"]["SelectSource"][$Lang]."</option>" . CRLF);
  $selected = ""; $sel_OW = "style='visibility:hidden'"; if (substr($Active_Ext_temp,0,2)=="28") { $selected = "selected"; $sel_OW = ""; }
  echo("<option value='1' " . $selected . ">".$msg["THERM"]["OWSensor"][$Lang]."</option>" . CRLF);
  
  $i=0;
  while ((isset($output[$i])) && ($action==""))  {
    if ($output[$i]['ICAO']!="") {
	  $selected = ""; if ($output[$i]['ICAO']==$Active_Ext_temp) { $selected = "selected"; }
      echo("<option value='" . $output[$i]['ICAO'] . "' " . $selected . ">" . $output[$i]['C'] . "/" . $output[$i]['STATION'] . "</option>" . CRLF);
    } // END IF
	$i++;
  } // END WHILE
  echo("</select>" . CRLF);
  // From Airport? ... Internet
  echo($msg["THERM"]["AirportNear"][$Lang]."<input type=\"text\" name=\"SearchStation\" value=\"".$msg["THERM"]["AirportSearchBase"][$Lang].
			"\" onKeyUp=\"javascript:obj1.bldUpdate();\">" . CRLF);
  echo("</div>" . CRLF);
  
  // 1 Wire GPIO Config
  echo("<div class='post_info' style=\"height: 350px;\"><b>1-Wire:</b><br>&nbsp;<br>" . CRLF);
  echo($msg["THERM"]["ConnectedOn"][$Lang]."<select name='OWGPIO' id='OWGPIO'  OnChange='ChangeOWGPIO();'>" . CRLF);
  $i=3;
  while ($i<=26) {
    if (isset($GPIO_Names[$i])) { 
	  echo("<option value='".$GPIO_Names[$i]."'");
	  if ($GPIOpin==$GPIO_Names[$i]) { echo(" selected"); }
	  echo(">GPIO ".$GPIO_Names[$i]." (pin ".$i.")");
	  if ($i==16) { echo(" [Default]");}
	  echo("</option>" . CRLF); 
	} // END IF
	$i++;
  } // END While
  
  echo("</select>");//$sel_OW
  
  // 1 Wire Exterior Probe
  echo("<div id=\"DivOneWire\" " . $sel_OW . ">".$msg["THERM"]["OWSensor"][$Lang]);
  echo(": <select2 name='ExtOneWire' id='ExtOneWire'>" . CRLF);
  if (ONEWIRE_MODE=="OWFS") {
    require "/usr/share/php/OWNet/ownet.php";
    $ow=new OWNet("tcp://127.0.0.1:" . ONEWIRE_OWSERVER_PORT);
    $content = $ow->get("/",OWNET_MSG_DIR,true);
    $i=0;
    while (isset($content[$i]["data"])) {
      $sensor = $content[$i]["data"];
      $sensor = substr($sensor,1);
	 // Already used for Indoor Temp?
	  $sql = "SELECT COUNT(*) AS count FROM `chauffage_sonde` WHERE `id_sonde`=\'" . $files[$i] . "\';";
	  $query = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($query, MYSQLI_BOTH);
      $Used = $row['count'];
      if ((substr($sensor,0,2)=="28") && ($Used==0)) {
        $selected = ""; if ($sensor==$Active_Ext_temp) { $selected = "selected"; }
        echo("<option value='" . $sensor . "' " . $selected . ">" . $sensor . "</option>" . CRLF);
      } // End IF
      $i++;
    } // End While
    closedir($handle);
  } // END IF
  $dir = "/sys/bus/w1/devices";
  if ((ONEWIRE_MODE=="RPI") && (file_exists($dir))) {
	echo("RPI2");
    $dh  = opendir($dir);
    while (false !== ($filename = readdir($dh))) {
      $files[] = $filename;
    } // END While
    sort($files);
    $i=2;
    while (isset($files[$i])) {
	  // Already used for Indoor Temp?
	  $sql = "SELECT COUNT(*) AS count FROM `chauffage_sonde` WHERE `id_sonde`='" . $files[$i] . "';";
	  $query = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($query, MYSQLI_BOTH);
      $Used = $row['count'];
      if ((substr($files[$i],0,2)=="28") && ($Used==0)) {
        $selected = ""; if ($files[$i]==$Active_Ext_temp) { $selected = "selected"; }
        echo("<option value='" . $files[$i] . "' " . $selected . ">" . $files[$i] . "</option>" . CRLF);
      } // End IF
      $i++;
    } // END WHILE
  } // END IF
  echo("</select></div>" . CRLF);
//  echo("<input type='hidden' name='page' value='Therm' />" . CRLF);
//  echo("<input type='hidden' name='action' value='Ext-Temp' />" . CRLF);
//  echo("<input type='submit' name='submit' value='Enregistrer' style=”display:hidden”/>" . CRLF);
//  echo("</form>" . CRLF);
  
  echo("<br><b>".$msg["THERM"]["1WUsedList"][$Lang]."</b>" . CRLF);
  
  echo("<br><table width='80%'>" . CRLF);
  $sql = "SELECT * FROM `chauffage_sonde` WHERE `id`<>'1';";
  $query = mysqli_query($DB,$sql);
  echo("<tr><td width='30%'><b>".$msg["THERM"]["Identifier"][$Lang]."</b></td><td width='30%'><b>".$msg["MAIN"]["Identifier"][$Lang].
		"</b></td><td width='40%'><b>".$msg["MAIN"]["Description"][$Lang]."</b></td></tr>" . CRLF);
  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    $ID = $row['id_sonde'];
	$Localisation = $row['localisation'];
	$Description = $row['description'];
    echo("<tr><td width='30%'>" . $ID . "</td><td width='30%'>" . $Localisation . "</td><td width='40%'>" . $Description . "</td></tr>" . CRLF);
  } // END WHILE
  echo("</table>" . CRLF);
  
  // Available 1 Wire Sensors?
    $j=0;
    if (ONEWIRE_MODE=="OWFS") {
    require "/usr/share/php/OWNet/ownet.php";
    $ow=new OWNet("tcp://127.0.0.1:" . ONEWIRE_OWSERVER_PORT);
    $content = $ow->get("/",OWNET_MSG_DIR,true);
    $i=0;
    while (isset($content[$i]["data"])) {
      $sensor = $content[$i]["data"];
      $sensor = substr($sensor,1);
	 // Already used?
	  $sql = "SELECT COUNT(*) AS count FROM `chauffage_sonde` WHERE `id_sonde`=\'" . $files[$i] . "\';";
	  $query = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($query, MYSQLI_BOTH);
      $Used = $row['count'];
      if ((substr($sensor,0,2)=="28") && ($Used==0)) {
        $j++;
		$OW_List[$j] = $sensor;
      } // End IF
      $i++;
    } // End While
    closedir($handle);
  } // END IF
  $dir = "/sys/bus/w1/devices";
  if ((ONEWIRE_MODE=="RPI") && (file_exists($dir))) {
    $dh  = opendir($dir);
    while (false !== ($filename = readdir($dh))) {
      $files[] = $filename;
    } // END While
    sort($files);
    $i=1;
    while (isset($files[$i])) {
	  // Already used for Indoor Temp?
	  $sql = "SELECT COUNT(*) AS count FROM `chauffage_sonde` WHERE `id_sonde`='" . $files[$i] . "';";
	  $query = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($query, MYSQLI_BOTH);
      $Used = $row['count'];
	  //echo("$i = " . $files[$i] ."<br>");
      if ((substr($files[$i],0,2)=="28") && ($Used==0) && ($files[$i]!=$files[$i-1])) {
        $j++;
		$OW_List[$j] = $files[$i];
      } // End IF
      $i++;
    } // END WHILE
  } // END IF
  if ($j!=0) {
    echo("<br><b>".$msg["THERM"]["Available1WList"][$Lang]."</b><br>" . CRLF);
	$i=1;
	while ($i<=$j) {
	  echo($OW_List[$i] . "<br>");
	  $i++;
	} // END WHILE
  } // END IF
  
  // Filter drop box ... Airports
  echo("<script language=\"javascript\">" . CRLF .
	"obj1 = new SelObj('ExtSensors','ExtSensor','SearchStation');" . CRLF .
	"obj1.bldInitial(); " . CRLF);
  echo("</script> " . CRLF);
  /// Activate External 1 Wire drop box
  echo("<script language=\"javascript\">" . CRLF);
  echo("  function ActivateSearchOneWire() { " . CRLF .
	"  var obj = document.getElementById('ExtSensor');" . CRLF .
	"  var OW  = document.getElementById('DivOneWire');" . CRLF .
	"  if (obj.selectedIndex == 1) OW.style.visibility = \"visible\";" . CRLF .
	"}" . CRLF);
  echo("</script> " . CRLF);	
	
  // Change 1 Wire GPIO?
  echo("<script language=\"javascript\">" . CRLF);
  echo("  function ChangeOWGPIO() {" . CRLF);
  echo("    var GPIO   = document.getElementById('OWGPIO');" . CRLF);
  echo("    var action = document.getElementById('action');" . CRLF);
  echo("    if (confirm('".$msg["MAIN"]["RuSure"][$Lang]."')) {" . CRLF);
  echo("      action.value='ChangeOWGPIO';" . CRLF);
  echo("      document.getElementById('ExtSensors').submit();" . CRLF); //ExtSensors
  echo("    }" . CRLF);
  echo("" . CRLF);
  echo("  }" . CRLF); 
  echo("</script>" . CRLF);
  
  echo("</div>" . CRLF);
    
  // Config Thermostat "Name"
  echo("<div class='post_info' style=\"height: 100px;\"><b>".$msg["THERM"]["ThermostatConfig"][$Lang]."</b><br>&nbsp;<br>" . CRLF);
  echo("<b>".$msg["MAIN"]["Name"][$Lang]."</b> <input type=\"text\" name=\"nestName\" value=\"".$nestName."\">");
  echo("</div>" . CRLF);
  
  // Heater System Configuration
  $Heater = ""; $Boiler = "";
  $sql   = "SELECT * FROM `".TABLE_CHAUFFAGE_CLEF."` WHERE 1;";
  $query = mysqli_query($DB,$sql);
  while ($row=mysqli_fetch_array($query, MYSQLI_BOTH)) {
    $key = $row['clef']; 
    if ($key=="circulateureauchaude") { $Mode   = $row['valeur']; }
	if ($key=="HeaterOUT")            { $Heater = $row['valeur']; }
	if ($key=="BoilerOUT")            { $Boiler = $row['valeur']; }
  } // END WHILE
  echo("<div class='post_info' style=\"height: 350px;\"><b>".$msg["THERM"]["HeaterConfig"][$Lang]."</b><br>&nbsp;<br>" . CRLF);
  // Heater Mode
  echo("<table><tr><td> ".$msg["THERM"]["HeaterMode"][$Lang]."&nbsp;&nbsp;</td>");
  echo("<td><select name='HeaterMode' id='HeaterMode'>" . CRLF);
  //echo("<option value=''>Selectionnez un Mode</option>" . CRLF);
  $selected="";if ($Mode=="0") { $selected="selected";}
  echo("<option value='0' " . $selected . " title='".$msg["THERM"]["WOModuleDesc"][$Lang]."'>".$msg["THERM"]["WOModule"][$Lang]."</option>" . CRLF);
  $selected="";if ($Mode=="1") { $selected="selected";}
  echo("<option value='1' " . $selected . " title='".$msg["THERM"]["WithModuleDesc"][$Lang]."'>".$msg["THERM"]["WithModule"][$Lang]."</option>" . CRLF);
  echo("</select></td></tr>".CRLF);
  // Heater Output
  echo("<tr><td> ".$msg["THERM"]["HeaterContact"][$Lang]."</td>");
  echo("<td><select name='HeaterOUT' id='HeaterOUT'>" . CRLF);
  echo("<option value='EMPTY'>".$msg["THERM"]["NothingORInactif"][$Lang]."</option>" . CRLF);
  $sql   = "SELECT * FROM `ha_element` WHERE (`element_type`=\"0x11\" OR `element_type`=\"0x12\")  ORDER BY `Manufacturer`,`card_id`,`element_reference`,`id` ASC;";
  $query = mysqli_query($DB,$sql);
  while ($row=mysqli_fetch_array($query, MYSQLI_BOTH)) {
    $id = $row['id']; $Name = $row['element_name']." (".$row['Manufacturer']."/".$msg["MAIN"]["Module"][$Lang]." ".$row['card_id'].
			"/".$msg["MAIN"]["Output"][$Lang]." ".$row['element_reference'].")";  
	$selected = ""; if ($id==$Heater) { $selected="selected"; }
	echo("<option value='".$id."' " . $selected . ">".$Name."</option>".CRLF);
  } // END WHILE
  
  // Boiler Output
  echo("<tr><td> Contact Boiler</td>");
  echo("<td><select name='BoilerOUT' id='BoilerOUT'>" . CRLF);
  echo("<option value='EMPTY'>".$msg["THERM"]["NothingORInactif"][$Lang]."</option>" . CRLF);
  $sql   = "SELECT * FROM `ha_element` WHERE (`element_type`=\"0x11\" OR `element_type`=\"0x12\")  ORDER BY `Manufacturer`,`card_id`,`element_reference`,`id` ASC;";
  $query = mysqli_query($DB,$sql);
  while ($row=mysqli_fetch_array($query, MYSQLI_BOTH)) {
    $id = $row['id']; $Name = $row['element_name']." (".$row['Manufacturer']."/".$msg["MAIN"]["Module"][$Lang]." ".$row['card_id'].
			"/".$msg["MAIN"]["Output"][$Lang]." ".$row['element_reference'].")";  
	$selected = ""; if ($id==$Boiler) { $selected="selected"; }
	echo("<option value='".$id."' " . $selected . ">".$Name."</option>".CRLF);
  } // END WHILE

  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>" . CRLF);
  echo("</table>");
  echo("<input type='hidden' name='page' value='Therm' />" . CRLF);
  echo("<input type='hidden' name='action' id='action' value='Ext-Temp' />" . CRLF);
  
  //echo("<input type='submit' name='Submit' id='Submit' value='Save' style='visibility:hidden;'>" . CRLF); //
  //echo("<input type='hidden' name='submitForm' id='submitForm' value='Enregistrer' />" . CRLF);
  //echo("<p align='center'><a href='javascript:SubmitForm();'><img src='./images/save.png' width='70px' heigth='70px' /></a></p>" . CRLF);
  echo("<p align='center'><input type='image' src='./images/save.png' width='70px' heigth='70px' alt='Save' /></p>");
  echo("</form>" . CRLF);
  echo("</div>" . CRLF);
  
  mysqli_close($DB);
} // End of Function Therm

// Weather List parsing
 
 function decode_line($line) {
   $result['STATION'] = substr($line, '3', '16');
   $result['ICAO'] = substr($line, '20', '4');
   $result['C'] = substr($line, '81', '2');
   return $result;
 } // END FUNCTION Update_Exterior_Sensor
 ?>
