<?PHP
// Includes
//include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Function to modify config values
Function ChangeInFile($ReadFile,$WriteFile,$needle,$arg) {
  $reading   = fopen($ReadFile,'r');
  $writing   = fopen($WriteFile,"w");
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,$needle)!==false) {
	  fwrite($writing,$needle . $arg .chr(13).chr(10));
	} else {
	  fwrite($writing,$line);
	} // END IF
  } // END WHILE
  fclose($reading); 
  fclose($writing);
  shell_exec('sudo cp '.$WriteFile.' '.$ReadFile);
  shell_exec('sudo rm '.$WriteFile);
}

// Main Function TempGraphs
function TempGraphs() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  // Includes
  include_once "./lang/admin.module.TempGraph.php";
  
  $LocalRRDPATH           = RRDPATH;
  $LocalEXCELTEMPLOGSPATH = EXCELTEMPLOGSPATH;
  $URI_Check              = "none";

  // Action Requested via Form?  
  $action                = html_postget("action");
  
  // Setting variables and Restore Config Settings
  $fileOK=""; 
  $URI_Check = "none";
  
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='graph_uri';";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  $Val = $row['value'];
  if ($Val=="") { $GraphDir = $_SERVER['DOCUMENT_ROOT']."/smartcan/rrdtool/"; $GraphDirDisplay = "";} else { $GraphDir = $GraphDirDisplay = $Val; }

  // RRDTool Running?
  $RRD_Status=shell_exec('sudo dpkg -l rrdtool');
  //echo("Running? >$RRD_Status< <br>");

  //echo("Action=$action<br>");
  
  // Move to ftp or sftp ... Check?
  if (($action=="CheckURI") || ($action=="SaveURLs")) {
    $GraphDest = html_postget("GraphDest");
    //echo("dest=$GraphDest=-<br>");
	if ($GraphDest != $GraphDirDisplay) {
	  // Backup Dest changed => Check & Update DB if OK
	  $URIChecked = "N";
	  if ($GraphDest=="") {
	    // Empty path => back to DEFAULT
		$URIChecked = "Y"; $BackupDir = $_SERVER['DOCUMENT_ROOT']."/backups/"; $GraphDirDisplay = "";
		echo("<b>Destination back to DEFAULT</b><br>");
	  } else {
	    $choppedURL = parse_url($GraphDest);
	    if ($choppedURL["scheme"]=="ftp") {
		  $user = $choppedURL["user"];
		  $pass = $choppedURL["pass"];
		  $host = $choppedURL["host"];
		  if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	      //echo($choppedURL["scheme"]."=ftp? ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		  $conn_id = ftp_connect($host); 
		  // login with username and password 
		  $login_result = ftp_login($conn_id, $user, $pass); 
		  // upload a file 
		  if (ftp_put($conn_id, $path."/SmartCAN-Test.txt", $_SERVER['DOCUMENT_ROOT']."/test-ftp.txt", FTP_BINARY)) { 
		    echo("<b>".$msg["BACKUPRESTORE"]["ftptested"][$Lang]."</b><br>");
		    $URIChecked = "Y";
		    $BackupDir = $GraphDirDisplay = $GraphDest;
		    ftp_delete($conn_id, $path."/SmartCAN-Test.txt");
		  } else {
		    echo("<font color=red<b>".$msg["BACKUPRESTORE"]["ftpNOK"][$Lang]."</b></font> <br>");
		  } // END IF
	    } else {
	      if ($choppedURL["scheme"]=="sftp") {
		    $user = $choppedURL["user"];
		    $pass = $choppedURL["pass"];
		    $host = $choppedURL["host"];
		    if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	        //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
			$connection = ssh2_connect($host, 22);
			ssh2_auth_password($connection, $user, $pass);
			$sftp = ssh2_sftp($connection);
			$stream = fopen("ssh2.sftp://$sftp/".$path."/SmartCAN-Test.txt", 'w');
			if ($stream) {
			  echo("<b>".$msg["BACKUPRESTORE"]["sftptested"][$Lang]."</b><br>");
			  ssh2_sftp_unlink($sftp, "/".$path."/SmartCAN-Test.txt");
			  $URIChecked = "Y";
			  $BackupDir = $GraphDirDisplay = $GraphDest;
			} else {
			  echo("<font color=red<b>".$msg["BACKUPRESTORE"]["sftpNOK"][$Lang]."</b></font> <br>");
			} // END IF
		  } else {
		    // Other Local PATH
			//$URIChecked = "Y";
		  } // END IF SFTP
	    } // END IF FTP
	  } // END IF Empty PATH
	  
	  // URI Checked? => Update DB   
      if ($URIChecked=="Y") {
	    $sql = "UPDATE `ha_settings` SET `value` = '".$GraphDest."' WHERE `ha_settings`.`variable` = 'graph_uri';";
		$query = mysqli_query($DB,$sql);
      } // END IF	  
	} // END IF URI Changed
  } // END IF
  
  // Action Request?	
  if ($action!="") {
    $RRDTool               = html_postget("RRDTool");
	$FormEXCELTEMPLOGSPATH = html_postget("FormEXCELTEMPLOGSPATH");
	if ($FormEXCELTEMPLOGSPATH=="") { $LocalExcelLogs="N"; } else { $LocalExcelLogs="Y"; }
	$ExcelLogs             = html_postget("ExcelLogs");
	//echo("RRDTool: Status=$RRD_Status, Form=$RRDTool<br>");
	//echo("ExcelLogs: Status= $LocalExcelLogs, Form=$ExcelLogs, Form_URL=$FormEXCELTEMPLOGSPATH<br>");
    if ( (($RRD_Status!="") && ($RRDTool=="N")) || (($RRD_Status=="") && ($RRDTool=="Y")) ) {
	  //echo("Change Mode to " .$RRDTool."<br>");
	  if ($RRDTool=="Y") { 
	    ChangeInFile($_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php",$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php.tmp",
						"  define('RRDPATH',"," '".$_SERVER['DOCUMENT_ROOT']."/smartcan/rrdtool/'); // If RRDTool Installed: '".$_SERVER['DOCUMENT_ROOT']."/smartcan/rrdtool/'");
		$LocalRRDPATH=$_SERVER['DOCUMENT_ROOT'].'/smartcan/rrdtool/'; 
		shell_exec('sudo apt-get install -y rrdtool');
	  } // ENDIF
	  if ($RRDTool=="N") {
	    ChangeInFile($_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php",$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php.tmp",
						"  define('RRDPATH',"," ''); // If RRDTool Installed: '".$_SERVER['DOCUMENT_ROOT']."/smartcan/rrdtool/'");
		$LocalRRDPATH=''; 
		shell_exec('sudo apt-get remove -y rrdtool');
	  } // ENDIF
	  sleep(30);
	} // END IF
	// Excell Path Changed?
	if (($LocalEXCELTEMPLOGSPATH!=$FormEXCELTEMPLOGSPATH) || ($LocalExcelLogs!=$ExcelLogs)) {
	  if ($ExcelLogs=="Y") {
	    ChangeInFile($_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php",$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php.tmp",
						"  define('EXCELTEMPLOGSPATH',"," '".$_SERVER['DOCUMENT_ROOT']."/smartcan/bin/tests/'); // if Excell Temp Logs Active: '".$_SERVER['DOCUMENT_ROOT']."/smartcan/bin/tests/'");
		$LocalEXCELTEMPLOGSPATH=$_SERVER['DOCUMENT_ROOT'].'/smartcan/bin/tests/';
	  } else {
	    ChangeInFile($_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php",$_SERVER['DOCUMENT_ROOT']."/smartcan/www/conf/config.php.tmp",
						"  define('EXCELTEMPLOGSPATH',"," ''); // if Excell Temp Logs Active: '".$_SERVER['DOCUMENT_ROOT']."/smartcan/bin/tests/'");
		$LocalEXCELTEMPLOGSPATH='';
	  } // END IF
	} // END IF
  
    $RRD_Status="Y";
  
    // sudo apt-get install -y rrdtool
	// sudo apt-get remove -y rrdtool
		
  } // END IF
  
  // No action Request => Parse Variables
  
  
  // Start Build Page ...
  echo("<h2 class='title'>".$msg["TEMPGRAPH"]["Title"][$Lang]."</h2>");
  // Open Form
  echo("<form name='ChangeVariables' id='ChangeVariables' action='".$_SERVER['PHP_SELF']."?page=TempGraphs' method='post'>" . CRLF);
  
  // URL Management
  echo("<div class='post_info'>".$msg["TEMPGRAPH"]["ManageRRDTool"][$Lang]." &nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);

  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  echo("<table>" . CRLF);
  echo("<tr><td colspan=2 align=middle><b>".$msg["TEMPGRAPH"]["RRDToolMode"][$Lang].":</b></td></tr>" . CRLF);

  // RRDTool Running?
  echo("<tr><td>Status :</td><td><select name='RRDTool' id='RRDTool'> . CRLF");
  echo("<option value='N'"); if ($RRD_Status=="") { echo(" selected");}
  echo(">".$msg["MAIN"]["NotInstalled"][$Lang]."!</option>" . CRLF);
  echo("<option value='Y'"); if ($RRD_Status!="") { echo(" selected");}
  echo(">".$msg["MAIN"]["Installed"]["en"]."</option>" . CRLF);
  echo("</select></td></tr>" . CRLF); 
  if ($RRD_Status!="") {
    //echo("<tr><td>Path: </td><td>".$LocalRRDPATH."</td></tr>" . CRLF); 
    echo("<tr><td><b>".$msg["TEMPGRAPH"]["GraphTarget"][$Lang]." </b></td><td> &nbsp;<input type='text' name='GraphDest' id='GraphDest' value='".$GraphDirDisplay."' size=35>" .CRLF);
	echo("<a href='javascript:submitform(\"CheckURI\")'>" . CRLF);
	echo("<img id='VerifyButton' src='" . CRLF);
	if ($URI_Check=="none") { echo("./images/verify.png" . CRLF); }
	echo("' width=36 height=36 style='visibility:" . CRLF);
    if ($URI_Check=="none") { echo("hidden" . CRLF); }
    echo("'></a><tr><td colspan=2><font color=red>".$msg["TEMPGRAPH"]["GraphTargetDesc1"][$Lang].$LocalRRDPATH);
    echo("<br>".$msg["TEMPGRAPH"]["GraphTargetDesc2"][$Lang]."</td></tr>");
  } // END IF
  
  echo("</table>" . CRLF);
  
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

   //////////////////
  // Fichiers Excel //
  //////////////////
  echo("<div class='post_info'>".$msg["TEMPGRAPH"]["ManageExcel"][$Lang]."</div>" . CRLF);
  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF); 
	
  // Table, to Add or Modify Elements
  echo("<table>" . CRLF);
  echo("<tr><td>".$msg["MAIN"]["Status"][$Lang]." :</td><td><select name='ExcelLogs' id='ExcelLogs'> . CRLF");
  echo("<option value='N'"); if ($LocalEXCELTEMPLOGSPATH=="") { echo(" selected");}
  echo(">".$msg["MAIN"]["Stopped"][$Lang]."!</option>" . CRLF);
  echo("<option value='Y'"); if ($LocalEXCELTEMPLOGSPATH!="") { echo(" selected");}
  echo(">".$msg["MAIN"]["Actif"][$Lang]."</option>" . CRLF);
  echo("</select></td></tr>" . CRLF); 
  if ($LocalEXCELTEMPLOGSPATH!="") { echo("<tr><td>".$msg["MAIN"]["Path"][$Lang].": </td><td><input type='text' name='FormEXCELTEMPLOGSPATH' id='FormEXCELTEMPLOGSPATH' size=30 " .
											"value='".$LocalEXCELTEMPLOGSPATH."'/></td></tr>" . CRLF); }
  
  // Submit
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"SaveURLs\")'><img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a></td></tr>");
  
  echo("</table><br>" . CRLF);

  	
  echo("</form>" . CRLF);
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);
  
  echo("    <body>" . CRLF);
  echo("        <div id='data'></div>" . CRLF);
  echo("    </body>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);

  echo("</ul>" . CRLF);
  echo("</div>" . CRLF);
  
  echo("</div>" . CRLF);
  echo("</div>" . CRLF);

  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'>&nbsp;" . CRLF);

  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  
  echo("  </ul></div>" . CRLF);
  echo("<div class='postcontent'>" . CRLF);
  echo("		<div class='clear'></div>" . CRLF);
  echo("	</div>" . CRLF);
  echo("  <input type='hidden' name='action' value='' />" . CRLF);
  echo("</div>" . CRLF);

  echo("<script type='text/javascript'>" . CRLF);
  echo("function submitform(action) {" . CRLF);
  echo("  //alert('submit + Action='+action);" . CRLF);
  echo("  document.ChangeVariables.action.value = action;" . CRLF);
  echo("  document.ChangeVariables.submit();" . CRLF);
  echo("}" . CRLF);
  
  // Javascript to hide VERIFY Button, if PATH empty or Invalid
  echo("var textBox_Change = function(e) {" . CRLF);
  echo("          // just calls the function that sets the visibility" . CRLF);
  echo("          button_SetVisibility();" . CRLF);
  echo("      };" . CRLF);

  echo("      var button_SetVisibility = function() {" . CRLF);
  echo("          // simply check if the visibility is set to 'visible' AND textbox hasn't been filled" . CRLF);
  echo("          if (textBox.value === '')  {" . CRLF);
  echo("              button.style.visibility = 'hidden';" . CRLF);
  echo("          } else {" . CRLF);
  echo("              if (textBox.value.length>6) {" . CRLF);
  echo("                if (((textBox.value.substring(0,6)=='ftp://') && (textBox.value.slice(-1)!='/') && (textBox.value.indexOf(':',7)!==-1) && (textBox.value.indexOf('@') !== -1) && (textBox.value.indexOf('@')+1<textBox.value.length)) || ((textBox.value.substring(0,7)=='sftp://') && (textBox.value.slice(-1)!='/') && (textBox.value.indexOf(':',8)!==-1) && (textBox.value.indexOf('@') !== -1) && (textBox.value.indexOf('@')+1<textBox.value.length)) ) {" . CRLF);
  //echo("                  alert('local path, FTP or SFTP URI OK!'+textBox.value.indexOf(':',7));" . CRLF);
  echo("                  button.style.visibility = 'visible';" . CRLF);
  echo("                } else { button.style.visibility = 'hidden'; }" . CRLF);
  echo("              }" . CRLF);
  echo("          }" . CRLF);
  echo("      };   " . CRLF);
  echo("(function() {" . CRLF);
  echo("   textBox = document.getElementById(\"GraphDest\");" . CRLF);
  echo("   button  = document.getElementById(\"VerifyButton\");" . CRLF);
  echo("   if('' === button.style.visibility) { button.style.visibility = 'visible'; }" . CRLF);
  echo("   textBox.oninput = textBox_Change;" . CRLF);
  echo("" . CRLF);
  echo("})();" . CRLF);
  
  echo("</script>" . CRLF);

  mysqli_close($DB);
} // End of Function SysMap
