<?PHP
// Includes
include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function Variables
function Variables() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  global $Linux_Mode;
  $Sudo = ""; if($Linux_Mode != "balena.io") { $Sudo = "sudo "; }
  
  // Includes
  include_once "./lang/admin.module.Variables.php";

  // First Admin Use?
  $sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
  $query            = mysqli_query($DB,$sql);
  $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
  $First_Use_Admin = $row['value'];

  // Action Requested via Form?  
  $action = html_postget("action");
  
  // SmartCAN Mode
  $myFile  = $_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/init_config.php";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'DomoCANMode')!==false)  { $DomoCANMode = substr($line, 15,7); }
  } // END WHILE
  fclose($reading);
	
  // Action Request?	
  if ($action!="") {
    if ($First_Use_Admin=="4") {
	    //
	    // Increase Admin First visit ... next step
	    //
	    $sql = "UPDATE `domotique`.`ha_settings` SET `value` = '0' WHERE `ha_settings`.`variable` = 'first_use_admin';";
	    $query = mysqli_query($DB,$sql);
	    $First_Use_Admin = "0";
	    echo("<table><tr><td width=\"40%\">&nbsp;</td><td>".CRLF);
	    echo("<br>&nbsp;<br>".$msg["VARIABLES"]["AdminFirstUse"][$Lang]."<br>".CRLF);
	    echo("<span class=\"readmore_b\"><a class=\"readmore\" href=\"index.php?page=Lamps\" style=\"color: white; align=middle;\" ;\">".$msg["MAIN"]["next"][$Lang].
				"</a></span><br>".CRLF);
	    echo("<br><br>".CRLF);
	    echo("</td><td width=\"40%\">&nbsp;</td></tr></table>".CRLF);
	} // END IF
	
	// Linux root Password
	$LINUXPasswd = html_postget("LINUXPasswd");
	$LINUXVerif  = html_postget("LINUXVerif");
	if (($LINUXPasswd!="") && ($LINUXPasswd==$LINUXVerif) && ($Linux_Mode!="balena.io")) {
	  exec("echo root:".$LINUXPasswd." | sudo /usr/sbin/chpasswd");
	  exec("echo pi:".$LINUXPasswd." | sudo /usr/sbin/chpasswd");
	} // END IF
	
    // SAMBA root Password
	$OLDSAMBAPasswd = html_postget("OLDSAMBAPasswd");
	$SAMBAPasswd    = html_postget("SAMBAPasswd");
	$SAMBAVerif     = html_postget("SAMBAVerif");
	if (($SAMBAPasswd!="") && ($SAMBAPasswd==$SAMBAVerif)) {
	  // $OLDSAMBAPasswd."\n".
	  //echo(exec("printf \"".$SAMBAPasswd."\n".$SAMBAPasswd."\n\" | sudo -u root bash -c \"/usr/bin/smbpasswd -s\"");
	  exec("printf \"".$SAMBAPasswd."\n".$SAMBAPasswd."\n\" | " . $Sudo . "/usr/bin/smbpasswd -s root");
	  //<!--#exec cmd="sudo smbpasswd -s -a `cat /full/path/htdocs/user.dat` < /full/path/htdocs/pass.dat" -->

	} // END IF
	
	// MySQL root Password
	$ROOTPasswd  = html_postget("ROOTPasswd");
	$ROOTVerif   = html_postget("ROOTVerif");
	if (($ROOTPasswd!="") && ($ROOTPasswd==$ROOTVerif)) {
	  //echo("<b>MySQL Password change Request!</b><br>");
	  $myFile    = $_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php";
	  $output    = shell_exec("sudo /bin/touch ".$myFile.".tmp");
	  $output    = shell_exec("sudo /bin/chmod 777 ".$myFile.".tmp");
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($myFile.".tmp","w");
	  $replaced  = false;
	  $OldPassdw = mysqli_PWD;
	  // Parse config file
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		if (substr_count($line,"define('mysqli_PWD'")>0) {
		  // Change Password in file
		  //echo("MySQL, change Password to $ROOTPasswd<br>");
		  fwrite($writing,"  define('mysqli_PWD', '" . $ROOTPasswd . "');".chr(13).chr(10));
		  $replaced = true;
		} else {
		  // write line
		  fwrite($writing,$line);
		} // END IF
      } // END WHILE      

      fclose($reading); 
	  fclose($writing);
	  // swap files
	  if ($replaced) {
	    //echo("Replaced!<br>");
        //copy($myFile.".tmp", $myFile);
		$output = shell_exec('sudo /bin/cp '.$myFile.'.tmp '.$myFile);
		exec("mysqladmin -u root -p'" . $OldPassdw . "' password '" . $ROOTPasswd . "'");
      } // END IF
	  
	  // Modify also MySQL Password in NGINX config => LUA MySQL
	  $myFile    = "/usr/local/nginx/conf/nginx.conf";
	  $output    = shell_exec("sudo /bin/touch ".$myFile.".tmp");
	  $output    = shell_exec("sudo /bin/chmod 777 ".$myFile.".tmp");
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($myFile.".tmp","w");
	  $OldPassdw = mysqli_PWD;
	  // Parse config file
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		if (substr_count($line,"password =")>0) {
		  // Change Password in file
		  //echo("MySQL, change Password to $ROOTPasswd<br>");
		  fwrite($writing,"                    password = \"" . $ROOTPasswd . "\",".chr(13).chr(10));
		} else {
		  // write line
		  fwrite($writing,$line);
		} // END IF
      } // END WHILE      

      fclose($reading); 
	  fclose($writing);
	  $output = shell_exec('sudo /bin/cp '.$myFile.'.tmp '.$myFile);
	  //copy($myFile.".tmp", $myFile);
	  
	} // END IF
	
	// Admin Account and Password
    $AdminAccount = html_postget("AdminAccount");
	$Admin_ID     = html_postget("Admin_ID");
	$AdminPasswd  = html_postget("AdminPasswd");
	$AdminVerif   = html_postget("AdminVerif");
	$AdminLang    = html_postget("AdminLang");
	if (($AdminAccount!="") && ($AdminPasswd!="") && ($AdminPasswd==$AdminVerif)) {
	  $sql = "UPDATE `users` SET `Alias` = '" . $AdminAccount . "', `Password` = PASSWORD('" . $AdminPasswd . "') WHERE `users`.`ID` = " . $Admin_ID . ";";
	  $query = mysqli_query($DB,$sql);
	} // END IF 
	// Change Admin Lang
	$sql = "UPDATE `users` SET `Lang` = '" . $AdminLang . "' WHERE `users`.`ID` = " . $Admin_ID . ";";
	$Admin_Lang = $Lang = $AdminLang;
	if (!mysqli_query($DB,$sql)) {
	  $sql2 = "ALTER TABLE `users` ADD `Lang` VARCHAR(7) NOT NULL AFTER `Last_Name`;";
	  $query = mysqli_query($DB,$sql2);
	  $query = mysqli_query($DB,$sql);
	} // END IF
	
	$UserAccount  = html_postget("UserAccount");
	$User_ID      = html_postget("User_ID");
	$UserPasswd   = html_postget("UserPasswd");
	$UserVerif    = html_postget("UserVerif");
	$UserLang    = html_postget("UserLang");
	$UserLocAuth  = html_postget("UserLocAuth");
	if (($UserAccount!="") && ($UserPasswd!="") && ($UserPasswd==$UserVerif)) {
	  $sql = "UPDATE `users` SET `Alias` = '" . $UserAccount . "', `Password` = PASSWORD('" . $UserPasswd . "') WHERE `users`.`ID` = " . $User_ID . ";";
	  $query = mysqli_query($DB,$sql);
	} // END IF
	// Change User Lang
	$sql = "UPDATE `users` SET `Lang` = '" . $UserLang . "' WHERE `users`.`ID` = " . $User_ID . ";";
	$query = mysqli_query($DB,$sql);
	
	// User Local Authentication?
	if ($UserLocAuth=="on") { $UserLocAuth="Y"; } else { $UserLocAuth="N";}
	$sql = "UPDATE `ha_settings` SET `value` = '" . $UserLocAuth . "' WHERE `ha_settings`.`variable` = 'local_user_auth';";
	$query = mysqli_query($DB,$sql);
	
	// Landing Page? 
	$LandingPage  = html_postget("LandingPage");
	if ($LandingPage=="on") { $LandingPage="1"; } else { $LandingPage="0";}
	$sql = "UPDATE `ha_settings` SET `value` = '" . $LandingPage . "' WHERE `ha_settings`.`variable` = 'landing_page';";
	$query = mysqli_query($DB,$sql);
	
	// Default User Page
	$DefaultPage  = html_postget("DefaultPage");
	$sql = "UPDATE `ha_settings` SET `value` = '" . $DefaultPage . "' WHERE `ha_settings`.`variable` = 'default_page';";
	$query = mysqli_query($DB,$sql);
	
	// Dump1090 IP Address
	$Dump1090IP   = html_postget("Dump1090IP");
	if ($Dump1090IP!="") {
	  $url = "http://".$Dump1090IP."/data.json";
	  $array = @get_headers($url);
	  $string = $array[0];
	  if(strpos($string,"200")) {
	    $sql = "UPDATE `ha_settings` SET `value` = '" . $Dump1090IP . "' WHERE `ha_settings`.`variable` = 'dump_1090_srv';";
	    $query = mysqli_query($DB,$sql);
	  } else {
	    echo("<H2><font color='red'><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $msg["VARIABLES"]["Dump1090NotFound"][$Lang] . "</b></font></H2><br>");
	  } // END IF
    } else {
	  $sql = "UPDATE `ha_settings` SET `value` = '" . $Dump1090IP . "' WHERE `ha_settings`.`variable` = 'dump_1090_srv';";
	  $query = mysqli_query($DB,$sql);
	} // END IF
	
	// Train Tabletable
	$trainDeparture       = html_postget("trainDeparture");
	$sql = "UPDATE `ha_settings` SET `value` = '" . $trainDeparture . "' WHERE `ha_settings`.`variable` = 'trainDeparture';";
	$query = mysqli_query($DB,$sql);
	$trainDestination         = html_postget("trainDestination");
	$sql = "UPDATE `ha_settings` SET `value` = '" . $trainDestination . "' WHERE `ha_settings`.`variable` = 'trainDestination';";
	$query = mysqli_query($DB,$sql);
	$trainShowStations    = html_postget("trainShowStations");
	if ($trainShowStations=="on") { $trainShowStations="Y"; } else { $trainShowStations="N";}
	$sql = "UPDATE `ha_settings` SET `value` = '" . $trainShowStations . "' WHERE `ha_settings`.`variable` = 'trainShowStations';";
	$query = mysqli_query($DB,$sql);
	$trainSwitchAfterNoon = html_postget("trainSwitchAfterNoon");
	if ($trainSwitchAfterNoon=="on") { $trainSwitchAfterNoon="Y"; } else { $trainSwitchAfterNoon="N";}
	$sql = "UPDATE `ha_settings` SET `value` = '" . $trainSwitchAfterNoon . "' WHERE `ha_settings`.`variable` = 'trainSwitchAfterNoon';";
	$query = mysqli_query($DB,$sql);

  } // END IF

  // Start Build Page ...
  echo("<h2 class='title'>" . $msg["VARIABLES"]["PageName"][$Lang] . "</h2>");
  echo("<div class='post_info'>&nbsp;</div>" . CRLF);

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

<script type="text/javascript">

  function checkPasswd(User,Passwd,Verif,NOK) {
	lUser=document.getElementById(User);
	lPasswd=document.getElementById(Passwd);
	lVerif=document.getElementById(Verif);
	lMsgNOK=document.getElementById(NOK);
	
    if(lUser.value == "") {
      alert($msg["VARIABLES"]["ERROREmptyAlias"][$Lang]);
	  lMsgNOK.style.visibility='visible';
      lUser.focus();
      return false;
    }
    re = /^\w+$/;
    if(!re.test(lUser.value)) {
      alert($msg["VARIABLES"]["ERRORbadFormatAlias"][$Lang]);
	  lMsgNOK.style.visibility='visible';
	  lUser.value="";
      lUser.focus();
      return false;
    }

    if(lPasswd.value != "" && lPasswd.value == lVerif.value) {
      if(lPasswd.value.length < 6) {
        alert($msg["VARIABLES"]["ERRORpasswdTooShort"][$Lang]);
		lMsgNOK.style.visibility='visible';
	    lPasswd.value="";
	    lVerif.value="";
        lPasswd.focus();
        return false;
      }
      if(lPasswd.value == lUsername.value) {
        alert($msg["VARIABLES"]["ERRORpasswdUserDiff"][$Lang]);
		lMsgNOK.style.visibility='visible';
	    lPasswd.value="";
	    lVerif.value="";
        lPasswd.focus();
        return false;
      }
      re = /[0-9]/;
      if(!re.test(lPasswd.value)) {
        alert($msg["VARIABLES"]["ERRORpasswdOneNumber"][$Lang]);
		lMsgNOK.style.visibility='visible';
	    lPasswd.value="";
	    lVerif.value="";
        lPasswd.focus();
        return false;
      }
      re = /[a-z]/;
      if(!re.test(lPasswd.value)) {
        alert($msg["VARIABLES"]["ERRORpasswdOneLower"][$Lang]);
		lMsgNOK.style.visibility='visible';
	    lPasswd.value="";
	    lVerif.value="";
        lPasswd.focus();
        return false;
      }
      re = /[A-Z]/;
      if(!re.test(lPasswd.value)) {
        alert($msg["VARIABLES"]["ERRORpasswdOneUpper"][$Lang]);
		lMsgNOK.style.visibility='visible';
		lPasswd.value="";
	    lVerif.value="";
        lPasswd.focus();
        return false;
      }
    } else {
      alert($msg["VARIABLES"]["ERRORpasswdEmpty"][$Lang]);
	  lMsgNOK.style.visibility='visible';
	  lPasswd.value="";
	  lVerif.value="";
      lPasswd.focus();
      return false;
    }

	lMsgNOK.style.visibility='hidden';
    return true;
  }
</script>

  <?PHP
  echo("<form name='ChangeVariables' id='ChangeVariables' action='./index.php?page=Variables' method='post'>" . CRLF);
  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  echo("<table>" . CRLF);
  echo("<tr><td colspan=2 align=middle><b>".$msg["VARIABLES"]["ServerSecu"][$Lang]."</b></td></tr>" . CRLF);
  // User Accounts (Admin & User)
  $sql = "SELECT * FROM `users`;";
  $query = mysqli_query($DB,$sql);
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    if ($row['ID']==1) { 
	  $Admin_Account = $row['Alias']; $Admin_ID = $row['ID'];
	  if (isset($row['Lang'])) { $Admin_Lang = $row['Lang']; } else { $Admin_Lang = ""; }
	} // END IF
	if ($row['ID']==2) {
	  $User_Account = $row['Alias']; $User_ID = $row['ID'];
	  if (isset($row['Lang'])) { $User_Lang = $row['Lang']; } else { $User_Lang = ""; }
	} // END IF
  } // End While
  
  // LINUX root Password
  if($Linux_Mode != "balena.io") {
    echo("<input type='hidden' name='LINUXAccount' id ='LINUXAccount' value='root'/>" . CRLF);
    echo("<tr><td><b>".$msg["VARIABLES"]["ConsoleAccess"][$Lang]."</b>&nbsp;&nbsp;&nbsp;</td><td>&nbsp;</td></tr>" . CRLF);
    echo("<tr><td>".$msg["VARIABLES"]["RootPasswd"][$Lang]."</td><td><input type='password' name='LINUXPasswd' id='LINUXPasswd' required pattern='(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])\\w{6,}' />" .
		"<div style='display:inline;visibility:hidden;' id=\"LinuxNOK\"><font color=red><b>".$msg["MAIN"]["Incorrect"][$Lang] ."</b></font></div></td></tr>" . CRLF);
    echo("<tr><td> (".$msg["MAIN"]["Check"][$Lang].")</td><td><input type='password' name='LINUXVerif' id='LINUXVerif' onblur='checkPasswd(\"LINUXAccount\",\"LINUXPasswd\",\"LINUXVerif\",\"LinuxNOK\");'/></td></tr>" . CRLF);
    echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
  } // ENDIF
  
  // SAMBA root Password
  echo("<input type='hidden' name='LINUXAccount' id ='OLDSAMBAPasswd' value='1'/>" . CRLF);
  echo("<tr><td><b>".$msg["VARIABLES"]["SMBAccess"][$Lang]."</b>&nbsp;&nbsp;&nbsp;</td><td>&nbsp;</td></tr>" . CRLF);
  echo("<tr><td>".$msg["VARIABLES"]["RootPasswd"][$Lang]."</td><td><input type='password' name='SAMBAPasswd' id='SAMBAPasswd' required pattern='(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])\\w{6,}' />" .
		"<div style='display:inline;visibility:hidden;' id=\"SAMBANOK\"><font color=red><b>".$msg["MAIN"]["Incorrect"][$Lang]."</b></font></div></td></tr>" . CRLF);
  echo("<tr><td> (".$msg["MAIN"]["Check"][$Lang].")</td><td><input type='password' name='SAMBAVerif' id='SAMBAVerif' onblur='checkPasswd(\"OLDSAMBAPasswd\",\"SAMBAPasswd\",\"SAMBAVerif\",\"SAMBANOK\");'/></td></tr>" . CRLF);
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
      
  // MySQL root Password
  echo("<input type='hidden' name='RootAccount' id ='RootAccount' value='BIGroot'/>" . CRLF);
  echo("<tr><td>".$msg["VARIABLES"]["SQLAccess"][$Lang]."</td><td><input type='password' name='ROOTPasswd' id='ROOTPasswd' onchange='TrackFlag()' required pattern='(?=.*\\d)(?!\!)(?=.*[a-z])(?=.*[A-Z])\\w{6,}' />" .
		"<div style='display:inline;visibility:hidden;' id=\"RootNOK\"><font color=red><b>".$msg["MAIN"]["Incorrect"][$Lang]."</b></font></div></td></tr>" . CRLF);
  echo("<tr><td> (".$msg["MAIN"]["Check"][$Lang].")</td><td><input type='password' name='ROOTVerif' id='ROOTVerif' onblur='checkPasswd(\"RootAccount\",\"ROOTPasswd\",\"ROOTVerif\",\"RootNOK\");'/></td></tr>" . CRLF);
  echo("<input type='hidden' name='Root_ID' id ='Root_ID' value='OLDroot'/>" . CRLF);
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
  
  echo("<tr><td colspan=2 align=middle><b>SmartCAN:</b></td></tr>" . CRLF);
  // Administrator Account and Password
  echo("<tr><td>".$msg["VARIABLES"]["AdminAccount"][$Lang]."&nbsp;&nbsp;&nbsp;</td><td><input type='text' name='AdminAccount' id='AdminAccount' value='" . $Admin_Account . "' required/></td></tr>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Password"][$Lang]."</td><td><input type='password' name='AdminPasswd' id='AdminPasswd' required pattern='(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])\\w{6,}' />" .
		"<div style='display:inline;visibility:hidden;' id=\"AdminNOK\"><font color=red><b>INCORRECT!</b></font></div></td></tr>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Password"][$Lang]." (".$msg["MAIN"]["Check"][$Lang].")</td><td><input type='password' name='AdminVerif' id='AdminVerif' onblur='checkPasswd(\"AdminAccount\",\"AdminPasswd\",\"AdminVerif\",\"AdminNOK\");'/></td></tr>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Language"][$Lang]."</td><td><select id='AdminLang' name='AdminLang'>");
  $k=0;
  while (isset(array_keys($msg["MAIN"]["lang"])[$k])) {
    echo("<option value='".array_keys($msg["MAIN"]["lang"])[$k]."' ");
	if (array_keys($msg["MAIN"]["lang"])[$k]==$Admin_Lang) { echo("selected"); }
	echo(">".$msg["MAIN"]["lang"][array_keys($msg["MAIN"]["lang"])[$k]]."</option>");
	$k++;
  } // END WHILE
  echo("</td></tr>");
  echo("<input type='hidden' name='Admin_ID' id ='Admin_ID' value='" . $Admin_ID . "'/>" . CRLF);
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");
  
  // User Account and Password
  echo("<tr><td>".$msg["VARIABLES"]["UserAccount"][$Lang]."&nbsp;&nbsp;&nbsp;</td><td><input type='text' name='UserAccount' id='UserAccount' value='" . $User_Account . "' required/></td></tr>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Password"][$Lang]."</td><td><input type='password' name='UserPasswd' id='UserPasswd' />" .
		"<div style='display:inline;visibility:hidden;' id=\"UserNOK\"><font color=red><b>INCORRECT!</b></font></div></td></tr>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Password"][$Lang]." (".$msg["MAIN"]["Check"][$Lang].")</td><td><input type='password' name='UserVerif' id='UserVerif' onblur='checkPasswd(\"UserAccount\",\"UserPasswd\",\"UserVerif\",\"UserNOK\");'/></td></tr>" . CRLF);
  echo("<input type='hidden' name='User_ID' id ='User_ID' value='" . $User_ID . "'/>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Language"][$Lang]."</td><td><select id='UserLang' name='UserLang'>");
  $k=0;
  while (isset(array_keys($msg["MAIN"]["lang"])[$k])) {
    echo("<option value='".array_keys($msg["MAIN"]["lang"])[$k]."' ");
	if (array_keys($msg["MAIN"]["lang"])[$k]==$User_Lang) { echo("selected"); }
	echo(">".$msg["MAIN"]["lang"][array_keys($msg["MAIN"]["lang"])[$k]]."</option>");
	$k++;
  } // END WHILE
  echo("</td></tr>");
  echo("<tr><td>".$msg["VARIABLES"]["LocalAuthentication"][$Lang]."</td><td><input type='checkbox' name='UserLocAuth' id='UserLocAuth' ");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='local_user_auth';";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  if ($row['value']=="Y") { echo("checked");}
  echo("/></td></tr>");
  
  // Landing Page?
  echo("<tr><td>".$msg["VARIABLES"]["redirectPage"][$Lang]."</td><td><input type='checkbox' name='LandingPage' id='LandingPage' ");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='landing_page';";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  if ($row['value']=="1") { echo("checked");}
  echo("/></td></tr>");
  
  // Interface
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");
  echo("<tr><td colspan=2 align=middle><b>".$msg["VARIABLES"]["LocalInterface"][$Lang]."</b></td></tr>");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='default_page'";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  $default_page = $row['value'];
  // 4 Backward compatibility Rules
  if ($default_page=="lumieres")  { $default_page="lights"; }
  if ($default_page=="meteo")     { $default_page="weather"; }
  if ($default_page=="ambiances") { $default_page="vibes"; }
  if ($default_page=="divers")    { $default_page="misc"; }
  echo("<tr><td>".$msg["VARIABLES"]["DefaultPage"][$Lang]."</td><td><select name='DefaultPage' id='DefaultPage'>"); 
  echo("<option value='lights' "); if ($default_page=="lights") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceLight"][$Lang]."</option>" . CRLF);
  echo("<option value='thermostat' "); if ($default_page=="thermostat") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceThermostat"][$Lang]."</option>" . CRLF);
  echo("<option value='vibes' "); if ($default_page=="vibes") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceVibes"][$Lang]."</option>" . CRLF);
  echo("<option value='weather' "); if ($default_page=="weather") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceWeather"][$Lang]."</option>" . CRLF);
  echo("<option value='surveillance' "); if ($default_page=="surveillance") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceSurveillance"][$Lang]."</option>" . CRLF);
  echo("<option value='trains' "); if ($default_page=="trains") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceTrains"][$Lang]."</option>" . CRLF);
  //echo("<option value='volets' "); if ($default_page=="volets") { echo("selected"); } echo(">Volets</option>" . CRLF);
  echo("<option value='misc' "); if ($default_page=="misc") { echo("selected"); } echo(">".$msg["VARIABLES"]["InterfaceMisc"][$Lang]."</option>" . CRLF);
  echo("</select></td></tr>");
  
  // Dump1090 Server?
  echo("<tr><td>".$msg["VARIABLES"]["Dump1090IP"][$Lang]."</td><td>");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='dump_1090_srv';";
  $retour = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $Dump1090IP = $row["value"];
  if (($Linux_Mode == "balena.io") && ($Dump1090IP=="")) { $Dump1090IP = "localhost:90"; }
  echo("<input type='text' name='Dump1090IP' id='Dump1090IP' value='" . $Dump1090IP . "' /></td></tr>");
  
  // Trains
  echo("<tr><td><br><b>".$msg["VARIABLES"]["TrainTimetable"][$Lang].":</b></td><td>&nbsp;</td></tr>");
  echo("<tr><td>".$msg["VARIABLES"]["TrainDeparture"][$Lang]."</td><td>");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainDeparture';";
  $retour = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  echo("<input type='text' name='trainDeparture' id='trainDeparture' value='" . $row["value"] . "' /> <a href='https://github.com/iRail/stations/blob/master/stations.csv' " .
		"target='_Blank' title='".$msg["VARIABLES"]["trainShowList"][$Lang]."'><img src='./images/QuestionMark.png' width='30pt' height='30pt' style='vertical-align:middle'/></a></td></tr>");
  echo("<tr><td>".$msg["VARIABLES"]["TrainDestination"][$Lang]."</td><td>");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainDestination';";
  $retour = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  echo("<input type='text' name='trainDestination' id='trainDestination' value='" . $row["value"] . "' /> <a href='https://github.com/iRail/stations/blob/master/stations.csv' " .
		"target='_Blank' title='".$msg["VARIABLES"]["trainShowList"][$Lang]."'><img src='./images/QuestionMark.png' width='30pt' height='30pt' style='vertical-align:middle'/></a></td></tr>");
  echo("<tr><td>".$msg["VARIABLES"]["trainShowStations"][$Lang]."</td><td><input type='checkbox' name='trainShowStations' id='trainShowStations' ");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainShowStations';";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  if ($row['value']=="Y") { echo("checked");}
  echo("/></td></tr>");
  echo("<tr><td>".$msg["VARIABLES"]["trainSwitchAfterNoon"][$Lang]."</td><td><input type='checkbox' name='trainSwitchAfterNoon' id='trainSwitchAfterNoon' ");
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainSwitchAfterNoon';";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  if ($row['value']=="Y") { echo("checked");}
  echo("/></td></tr>");
  
  // Submit
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"Modify\")'><img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a></td></tr>");
  
  echo("</table>" . CRLF);
  echo("</form>" . CRLF);
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

?>

    <body>
        <div id="data"></div>
    </body>

<div id="rss-3" class="block widget_rss">
<ul>
<?PHP

  echo("</ul>" . CRLF);
  echo("</div>" . CRLF);
?>


 
</div>
</div>

<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'>&nbsp;" . CRLF);

  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  

?>
  </ul></div>


<div class="postcontent">

	
		<div class="clear"></div>
	</div>



  <input type="hidden" name="action" value="" />    

</div>
<script type="text/javascript">
function submitform(action) {
  //alert("submit + Action="+action);
  document.ChangeVariables.action.value = action;
  document.ChangeVariables.submit();
}

function TrackFlag() {
  var newPassword = document.getElementById("ROOTPasswd").value;
  if(newPassword.length<6) {
	alert("<?php echo($msg["VARIABLES"]["ERRORpasswdTooShort"][$Lang]);?>");
	document.getElementById("ROOTPasswd").value="";
	document.getElementById("ROOTPasswd").focus();
    return false;    
  }
  if(newPassword.indexOf("!")>-1) {
	alert("<?php echo($msg["VARIABLES"]["ERRORpasswdSpecials"][$Lang]);?>");
	document.getElementById("ROOTPasswd").value="";
	document.getElementById("ROOTPasswd").focus();
    return false;
  }  
}
</script>


<?php
  mysqli_close($DB);
} // End of Function Variables
