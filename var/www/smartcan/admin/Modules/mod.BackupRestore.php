<?PHP
error_reporting(E_ALL);
ini_set('display_errors', '1');
// Includes


// Need to add SSH PHP lib, via apt-get install libssh2-php

// Main Function ModConfig (Admin COnfig of the Module
function BackupRestore() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.BackupRestore.php";
  
  // Local Variables
  $URI_Check = "none";

  // Action Requested via Form?  
  $action = html_postget("action");
  
  // Setting variables and Restore Config Settings
  $fileOK=""; 
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='backup_uri' OR `variable`='daily_backup' OR `variable`='weekly_backup' OR `variable`='monthly_backup';";
  $query = mysqli_query($DB,$sql);
  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    $Var = $row['variable'];
	$Val = $row['value'];
	if ($Var=="backup_uri") { if ($Val=="") { $BackupDir = $_SERVER['DOCUMENT_ROOT']."/backups/"; $BackupDirDisplay = "";} else { $BackupDir = $BackupDirDisplay = $Val; }}
	if ($Var=="daily_backup")   { $daily_backup   = $Val; }
	if ($Var=="weekly_backup")  { $weekly_backup  = $Val; }
	if ($Var=="monthly_backup") { $monthly_backup = $Val; }
  } // END WHILE
  
  //echo("Action=$action<br>");
  // Change URI?
  if (($action=="CheckURI") || ($action=="Save")) {
    $BackupDest = html_postget("BackupDest");
    //echo("dest=$BackupDest=-<br>");
	if ($BackupDest != $BackupDirDisplay) {
	  // Backup Dest changed => Check & Update DB if OK
	  $URIChecked = "N";
	  if ($BackupDest=="") {
	    // Empty path => back to DEFAULT
		$URIChecked = "Y"; $BackupDir = $_SERVER['DOCUMENT_ROOT']."/backups/"; $BackupDirDisplay = "";
		echo("<b>".$msg["BACKUPRESTORE"]["back2default"][$Lang]."</b><br>");
	  } else {
	    $choppedURL = parse_url($BackupDest);
	    if ($choppedURL["scheme"]=="ftp") {
		  $user = $choppedURL["user"];
		  $pass = $choppedURL["pass"];
		  $host = $choppedURL["host"];
		  if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	      //echo("ftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		  $conn_id = ftp_connect($host); 
		  // login with username and password 
		  $login_result = ftp_login($conn_id, $user, $pass); 
		  // upload a file 
		  if (ftp_put($conn_id, $path."/SmartCAN-Test.txt", $_SERVER['DOCUMENT_ROOT']."/test-ftp.txt", FTP_BINARY)) { 
		    echo("<b>".$msg["BACKUPRESTORE"]["ftptested"][$Lang]."</b><br>");
			$URIChecked = "Y";
			$BackupDir = $BackupDirDisplay = $BackupDest;
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
			  ssh2_exec($connection, 'logout');
			  $URIChecked = "Y";
			  $BackupDir = $BackupDirDisplay = $BackupDest;
			} else {
			  echo("<font color=red<b>".$msg["BACKUPRESTORE"]["sftpNOK"][$Lang]."</b></font> <br>");
			} // END IF
		  } else {
		    // Other Local PATH
			$URIChecked = "Y";
		  } // END IF SFTP
	    } // END IF FTP
	  } // END IF Empty PATH
	  
	  // URI Checked? => Update DB   
      if ($URIChecked=="Y") {
	    $sql = "UPDATE `ha_settings` SET `value` = '".$BackupDest."' WHERE `ha_settings`.`variable` = 'backup_uri';";
		$query = mysqli_query($DB,$sql);
      } // END IF	  
	} // END IF URI Changed
  } // END IF
  
  // Change Automatic Backup Frequency
  if ($action=="Save") {
    $FormDailyBackup   = html_postget("AutoDailyBackup");   if ($FormDailyBackup!="")   { $FormDailyBackup    = "Y"; } else { $FormDailyBackup   = "N"; }
	$FormWeeklyBackup  = html_postget("AutoWeeklyBackup");  if ($FormWeeklyBackup!="")  { $FormWeeklyBackup  = "Y";  } else { $FormWeeklyBackup = "N"; }
	$FormMonthlyBackup = html_postget("AutoMonthlyBackup"); if ($FormMonthlyBackup!="") { $FormMonthlyBackup  = "Y"; } else { $FormMonthlyBackup = "N"; }
	//echo("Save/Change Backup Frequency: Daily=$daily_backup<>$FormDailyBackup, Monthly=$monthly_backup<>$FormMonthlyBackup<br>");
	// Daily Backup?
	if ($FormDailyBackup!=$daily_backup) {
	  $sql = "UPDATE `ha_settings` SET `value` = '".$FormDailyBackup."' WHERE `ha_settings`.`variable` = 'daily_backup';";
	  $query = mysqli_query($DB,$sql);
	  $daily_backup= $FormDailyBackup;
	} // END IF
	// Weekly Backup?
	if ($FormWeeklyBackup!=$weekly_backup) {
	  $sql = "UPDATE `ha_settings` SET `value` = '".$FormWeeklyBackup."' WHERE `ha_settings`.`variable` = 'weekly_backup';";
	  $query = mysqli_query($DB,$sql);
	  $weekly_backup = $FormWeeklyBackup;
	} // END IF
	// Monthly Backup?
	if ($FormMonthlyBackup!=$monthly_backup) {
	  $sql = "UPDATE `ha_settings` SET `value` = '".$FormMonthlyBackup."' WHERE `ha_settings`.`variable` = 'monthly_backup';";
	  $query = mysqli_query($DB,$sql);
	  $monthly_backup = $FormMonthlyBackup;
	} // END IF
  } // END IF
  
  
  // Manual Backup
  if ($action=="backUp") {
    //echo("Manual Backup<br>");
    $BackupDest = $_SERVER['DOCUMENT_ROOT']."/backups/";
    exec("sudo mysqldump --opt --user=" . mysqli_LOGIN . " --password='".mysqli_real_escape_string($DB,mysqli_PWD)."' " . mysqli_DB . " > " . $BackupDest . "domotique.sql");
	exec("sudo mysqldump --user=" . mysqli_LOGIN . " --password='".mysqli_real_escape_string($DB,mysqli_PWD)."' mysql user > " . $BackupDest . "mysql.sql");
	exec("sudo pdbedit -e smbpasswd:" . $BackupDest . "samba-users.smbback");
	$fileName = date("Ymd-His-").'FULLsmartCAN-BACKUP.tar.gz';
	exec('sudo tar -czvf '. $BackupDest.$fileName . " " .
			$BackupDest . 'domotique.sql ' .
			$BackupDest . 'mysql.sql ' .
			$BackupDest . 'samba-users.smbback ' .
			PATHBASE . '/www/conf ' .
			PATHBASE . '/www/js/weather.js ' .
			PATHBASE . '/www/images/outputs ' .
			PATHBASE . '/www/images/plans ' .
			PATHBASE . '/bin/domocan-server ' .
			'/usr/local/nginx/conf/nginx.conf ' .
			'/etc/network ' .
			'/etc/passwd ' .
			'/etc/shadow ' .
			'--exclude={"/etc/network/run","/etc/network/if*.d"}');
	exec('sudo rm ' . $BackupDest . 'domotique.sql');
	exec('sudo rm ' . $BackupDest . 'mysql.sql');
	exec('sudo rm ' . $BackupDest . 'samba-users.smbback');
	// Need to move?
	if ($BackupDest!=$BackupDir) {
	  // to Local?
	  if (substr($BackupDir,0,1)=="/") {
	    rename($BackupDest.$fileName, $BackupDir.$fileName);
	  } // END IF
	  // to FTP ?
	  $choppedURL = parse_url($BackupDir);
	  if ($choppedURL["scheme"]=="ftp") {
		$user = $choppedURL["user"];
		$pass = $choppedURL["pass"];
		$host = $choppedURL["host"];
		if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	    //echo("ftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		$conn_id = ftp_connect($host); 
		// login with username and password 
		$login_result = ftp_login($conn_id, $user, $pass); 
		// upload a file 
		//echo("Move =".$BackupDest.$fileName."= to FTP =" . $path.$fileName . "=<br>");
		if (ftp_put($conn_id, "/".$path."/".$fileName, $BackupDest.$fileName, FTP_BINARY)) { 
		  echo("<b>".$msg["BACKUPRESTORE"]["moveftpOK"][$Lang]."</b><br>");
		  exec('rm ' . $BackupDest . $fileName);
		} // END IF
		ftp_close($conn_id);
	  } // END IF
	  // to SFTP ?
	  if ($choppedURL["scheme"]=="sftp") {
		$user = $choppedURL["user"];
		$pass = $choppedURL["pass"];
		$host = $choppedURL["host"];
		if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	    //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		$connection = ssh2_connect($host, 22);
		ssh2_auth_password($connection, $user, $pass);
		$sftp = ssh2_sftp($connection);
		//echo("Move =".$BackupDest.$fileName."= to SFTP =" . $path.$fileName . "=<br>SFTP: =ssh2.sftp://".$sftp."/".$path.$fileName."<br>");
		$file = file_get_contents($BackupDest.$fileName);
		if (file_put_contents("ssh2.sftp://".$sftp."/".$path."/".$fileName, $file)) {
		  echo("<b>".$msg["BACKUPRESTORE"]["movesftpOK"][$Lang]."</b><br>");
		  exec('rm ' . $BackupDest . $fileName);
		} // END IF
		ssh2_exec($connection, 'logout');
	  } // END IF
	} // END IF
  } // END IF
  
  
  // Restore Stored Backup
  if ($action=="reStoreBackup") {
    $BackupDest = $_SERVER['DOCUMENT_ROOT']."/backups/";
	$BackupURI = html_postget("backupURI");
	$fileName = substr($BackupURI, strrpos($BackupURI,"/")+1);
	echo($msg["BACKUPRESTORE"]["restore"][$Lang]." $fileName<br>");
	// Move?
	
	// local?
	if (substr($BackupURI,0,1)=="/") {
	  rename($BackupURI , $BackupDest.$fileName);
	} // END IF
	
	// from FTP ?
	if (substr($BackupURI,0,4)=="ftp:") {
	  $pass_pos = strrpos($BackupURI,":");
	  $user = substr($BackupURI,6, $pass_pos-6);
	  $host_pos = strrpos($BackupURI,"@");
	  $pass = substr($BackupURI, $pass_pos+1, $host_pos-$pass_pos-1);
	  $path_pos = strpos(substr($BackupURI,$host_pos),"/")+$host_pos;
	  $host = substr($BackupURI, $host_pos+1, $path_pos-$host_pos-1);
	  $path = substr($BackupURI, $path_pos+1);
	  $fileName = substr($BackupURI, strrpos($BackupURI,"/")+1);
	  //echo("ftp ! Host=$host, Path=$path, User=$user, Passwd=$pass, fileName=$fileName<br>");
	  $conn_id = ftp_connect($host); 
	  $login_result = ftp_login($conn_id, $user, $pass); 
	  //echo("Move ftp =" . $path.$fileName . "= to =" . $BackupDest.$fileName."=<br>");
	  if (ftp_get($conn_id, $BackupDest.$fileName, "/".$path, FTP_BINARY)) { 
	    echo("<b>".$msg["BACKUPRESTORE"]["moveftpOK"][$Lang]."</b><br>");
	  } // END IF
	  ftp_close($conn_id);
	} // END IF
	
	// from SFTP ?
	if (substr($BackupURI,0,5)=="sftp:") {
	  $pass_pos = strrpos($BackupURI,":");
	  $user = substr($BackupURI,7, $pass_pos-7);
	  $host_pos = strrpos($BackupURI,"@");
	  $pass = substr($BackupURI, $pass_pos+1, $host_pos-$pass_pos-1);
	  $path_pos = strpos(substr($BackupURI,$host_pos),"/")+$host_pos;
	  $host = substr($BackupURI, $host_pos+1, $path_pos-$host_pos-1);
	  $file_pos = strrpos($BackupURI,"/");
	  $path = substr($BackupURI, $path_pos+1, $file_pos-$path_pos);
	  $fileName = substr($BackupURI, $file_pos+1);
	  //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass, FileName=$fileName<br>");
	  $connection = ssh2_connect($host, 22);
	  ssh2_auth_password($connection, $user, $pass);
	  $sftp = ssh2_sftp($connection);
	  //echo("Move STFP =".$path.$fileName . "=<br>SFTP: =ssh2.sftp://".$sftp."/".$path.$fileName."= to =".$BackupDest.$fileName."<br>");
	  $remote = fopen("ssh2.sftp://$sftp/$path$fileName", 'r');
	  $local = fopen($BackupDest . $fileName, 'w');
	  $read = 0;
	  $filesize = filesize("ssh2.sftp://$sftp/$path$fileName");
	  while (($read < $filesize) && ($buffer = fread($remote, $filesize - $read))) {
		$read += strlen($buffer);
		if (fwrite($local, $buffer) === FALSE) {
		  echo("<font color=red><b>".$msg["BACKUPRESTORE"]["sftpError"][$Lang]."</b></font><br>");
		} // END IF
	  } // END WHILE
	  fclose($local);
	  fclose($remote);
	  ssh2_exec($connection, 'logout');
	} // END IF	
	  
	// UnTAR & Restore Files & DB
	//echo("sudo /bin/tar zxvf ".$BackupDest . $fileName." -C /  2>&1<br>");
	shell_exec("sudo /bin/tar zxvf ".$BackupDest . $fileName." -C /  2>&1");
	// SQL File present in Extracted files
	$ScanDir  = scandir($BackupDest);
	$ndir=0;
	while (isset($ScanDir[$ndir+2])) {
	  if (substr($ScanDir[$ndir+2],-4)==".sql") { 
		echo($msg["BACKUPRESTORE"]["SQLrestored"][$Lang].$ScanDir[$ndir+2]."<br>");
		importDB($BackupDest."/".$ScanDir[$ndir+2]);
		unlink($BackupDest."/".$ScanDir[$ndir+2]);
	  } // END IF
	  if ($ScanDir[$ndir+2]=="samba-users.smbback") {
	    shell_exec("sudo pdbedit -i smbpasswd:".$BackupDest."/samba-users.smbback");
		unlink($BackupDest."/".$ScanDir[$ndir+2]);
	  } // END IF
	  $ndir++;
	} // END WHILE
	 
  } // END IF
  
  // Upload & Restore
  if ($action=="upLoadBackup") {
    if (basename($_FILES['PackageFile']['type'])=="x-gzip") {
	  $BackupDest = $_SERVER['DOCUMENT_ROOT']."/backups/";
      //echo("UPLOAD!-=".basename($_FILES['PackageFile']['name'])."=-<br>");
      $fileName = basename($_FILES['PackageFile']['name']);
	  $uploadfile = $BackupDest . $fileName;
	  if (move_uploaded_file($_FILES['PackageFile']['tmp_name'], $uploadfile)) {
		//echo("tar zxvf ".$uploadfile." -C <br>");
		//shell_exec("chmod u+s ".$uploadfile." 2>&1");
		//echo("Moved to $uploadfile!<br>");		
		
		if (!shell_exec("sudo /bin/tar zxvf ".$uploadfile." -C /  2>&1")) {
		  $fileOK="NOK";
		} else {
		  // SQL File present in Extracted files
		  $ScanDir  = scandir($BackupDest);
		  $ndir=0;
		  while (isset($ScanDir[$ndir+2])) {
		    //echo("Dir=".$ScanDir[$ndir+2]."<br>");
			if (substr($ScanDir[$ndir+2],-4)==".sql") { importDB($BackupDest."/".$ScanDir[$ndir+2]); unlink($BackupDest."/".$ScanDir[$ndir+2]); }
			$ndir++;
		  } // END WHILE
		  $fileOK="OK";
		  //system("rm ".$uploadfile);
		} // ENDIF
		

	 } else {
        echo "<font size=4 color=red><b>".$msg["BACKUPRESTORE"]["CopyError"][$Lang]."</b></font>\n";
     } // ENDIF
    } else {
      echo "<font size=4 color=red><b>".$msg["BACKUPRESTORE"]["IncorrectFile"][$Lang]."</b></font>\n";
    } // ENDIF
  } // ENDIF

  // Start Build Page ...
  echo("<h2 class='title'> ".$msg["BACKUPRESTORE"]["TitleBackup"][$Lang]." </h2>");
  echo("<div class='post_info'>&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
		
  echo("<style>" . CRLF);
  echo("<" . CRLF);
  echo("img" . CRLF);
  echo("{" . CRLF);
  echo("position:relative;" . CRLF);
  echo("}" . CRLF);
  echo("</style>" . CRLF);

  if ($fileOK=="OK") {
    echo ("<h2>".$msg["BACKUPRESTORE"]["DBrestoreOK"][$Lang]."</h2>");
	// Need to move?
	if ($BackupDest!=$BackupDir) {
	  // to Local?
	  if (substr($BackupDir,0,1)=="/") {
	    rename($BackupDest.$fileName, $BackupDir.$fileName);
	  } // END IF
	  // to FTP ?
	  $choppedURL = parse_url($BackupDest);
	  if ($choppedURL["scheme"]=="ftp") {
		$user = $choppedURL["user"];
		$pass = $choppedURL["pass"];
		$host = $choppedURL["host"];
		if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	    //echo("ftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		$conn_id = ftp_connect($host); 
		// login with username and password 
		$login_result = ftp_login($conn_id, $user, $pass); 
		// upload a file 
		//echo("Move =".$BackupDest.$fileName."= to FTP =" . $path.$fileName . "=<br>");
		if (ftp_put($conn_id, "/".$path."/".$fileName, $BackupDest.$fileName, FTP_BINARY)) { 
		  echo("<b>".$msg["BACKUPRESTORE"]["moveftpOK"][$Lang]."</b><br>");
		  exec('rm ' . $BackupDest . $fileName);
		} // END IF
		ftp_close($conn_id);
	  } // END IF
	  // to SFTP ?
	  if ($choppedURL["scheme"]=="ftp") {
		$user = $choppedURL["user"];
		$pass = $choppedURL["pass"];
		$host = $choppedURL["host"];
		if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	    //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		$connection = ssh2_connect($host, 22);
		ssh2_auth_password($connection, $user, $pass);
		$sftp = ssh2_sftp($connection);
		//echo("Move =".$BackupDest.$fileName."= to SFTP =" . $path.$fileName . "=<br>SFTP: =ssh2.sftp://".$sftp."/".$path.$fileName."<br>");
		$file = file_get_contents($BackupDest.$fileName);
		if (file_put_contents("ssh2.sftp://".$sftp."/".$path."/".$fileName, $file)) {
		  echo("<b>".$msg["BACKUPRESTORE"]["movesftpOK"][$Lang]."</b><br>");
		  exec('rm ' . $BackupDest . $fileName);
		} // END IF
		ssh2_exec($connection, 'logout');
	  } // END IF
	} // END IF
  } // ENDIF
  if ($fileOK=="NOK") {
    echo ("<h2><font color=red><b>".$msg["BACKUPRESTORE"]["DecompressNOK"][$Lang]."</font></h2>");
  } // ENDIF
  
  echo("<form name='ChangeVariables' id='ChangeVariables' enctype='multipart/form-data' action='./index.php?page=BackupRestore' method='post'>" . CRLF);
  echo("<input type='hidden' name='action' id ='action' value=''/><input type='hidden' name='MAX_FILE_SIZE' value='20000000' />" . CRLF);
  echo("<table>" . CRLF);

  // Backup
  //Backup Destination
  echo("<tr><td colspan=2><b>".$msg["BACKUPRESTORE"]["BackupDest"][$Lang]." &nbsp;</b><input type='text' name='BackupDest' id='BackupDest' value='".$BackupDirDisplay."' size=35>" .CRLF);
  echo("<a href='javascript:submitform(\"ChangeVariables\",\"action\",\"CheckURI\")'>" . CRLF);
  echo("<img id='VerifyButton' src='" . CRLF);
  if ($URI_Check=="none") { echo("./images/verify.png" . CRLF); }
  echo("' width=36 height=36 style='visibility:" . CRLF);
  if ($URI_Check=="none") { echo("hidden" . CRLF); }
  echo("'></a><br><font color=red>".$msg["BACKUPRESTORE"]["BackupMsg1"][$Lang]);
  echo("<br>".$msg["BACKUPRESTORE"]["BackupMsg2"][$Lang]."</td></tr>");
  // Cron? ... Automatic backup @ 4 AM?
  echo("<tr><td colspan=2><br><b>".$msg["BACKUPRESTORE"]["AutoBackup"]["en"]."</b>&nbsp;<input type='checkbox' name='AutoDailyBackup' id='AutoDailyBackup' ");
  if ($daily_backup=="Y") { echo("checked "); }
  echo("/>".$msg["BACKUPRESTORE"]["DailyBackup"][$Lang]."</td></tr>");
  echo("<tr><td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='AutoWeeklyBackup' id='AutoWeeklyBackup' ");
  if ($weekly_backup=="Y") { echo("checked "); }
  echo("/>".$msg["BACKUPRESTORE"]["WeeklyBackup"][$Lang]."&nbsp;</td></tr>");
  echo("<tr><td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='AutoMonthlyBackup' id='AutoMonthlyBackup' ");
  if ($monthly_backup=="Y") { echo("checked "); }
  echo("/>".$msg["BACKUPRESTORE"]["MonthlyBackup"][$Lang]."<br>&nbsp;</td></tr>");
  // Save?
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"ChangeVariables\",\"action\",\"Save\")'><img src='./images/save.png' width='64px' heigth='64px'/></a><br>&nbsp;</td></tr>");
  // Manual Backup
  echo("<tr><td valign='top'><a href='javascript:submitform(\"ChangeVariables\",\"action\",\"backUp\")'><img src='./images/backup.png' width='64px' heigth='64px'/></a></td>" .
		"<td style='font-size:30px'><a href='javascript:submitform(\"ChangeVariables\",\"action\",\"backUp\")' v-align='middle'> ".$msg["BACKUPRESTORE"]["ManualBackup"][$Lang]."</a><br>&nbsp;</td></tr>");

  
  echo("</table>" . CRLF);

  echo("</form>" . CRLF);
  
  // Javascript to hide VERIFY Button, if PATH empty or Invalid
  echo("<script type=\"text/javascript\">" . CRLF);
  
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
  echo("                if (((textBox.value.substring(0,6)=='ftp://') && (textBox.value.slice(-1)!='/') && (textBox.value.indexOf(':',7)!==-1) && (textBox.value.indexOf('@') !== -1) && (textBox.value.indexOf('@')+1<textBox.value.length)) || ((textBox.value.substring(0,7)=='sftp://') && (textBox.value.slice(-1)!='/') && (textBox.value.indexOf(':',8)!==-1) && (textBox.value.indexOf('@') !== -1) && (textBox.value.indexOf('@')+1<textBox.value.length)) || ((textBox.value.substring(0,1)=='/') && (textBox.value.substring(1,2)!='/')) ) {" . CRLF);
  //echo("                  alert('local path, FTP or SFTP URI OK!'+textBox.value.indexOf(':',7));" . CRLF);
  echo("                  button.style.visibility = 'visible';" . CRLF);
  echo("                } else { button.style.visibility = 'hidden'; }" . CRLF);
  echo("              }" . CRLF);
  echo("          }" . CRLF);
  echo("      };   " . CRLF);
  echo("(function() {" . CRLF);
  echo("   textBox = document.getElementById(\"BackupDest\");" . CRLF);
  echo("   button  = document.getElementById(\"VerifyButton\");" . CRLF);
  echo("   if('' === button.style.visibility) { button.style.visibility = 'visible'; }" . CRLF);
  echo("   textBox.oninput = textBox_Change;" . CRLF);
  echo("" . CRLF);
  echo("})();" . CRLF);
  echo("</script>" . CRLF);
  // END Javascript
  
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

  // Restore
  echo("<h2 class='title'> Restore </h2>");
  echo("<div class='post_info'>&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
		
  echo("<style>" . CRLF);
  echo("<" . CRLF);
  echo("img" . CRLF);
  echo("{" . CRLF);
  echo("position:relative;" . CRLF);
  echo("}" . CRLF);
  echo("</style>" . CRLF);
  echo("<form name='ChangeVariables2' id='ChangeVariables2' enctype='multipart/form-data' action='./index.php?page=BackupRestore' method='post'>" . CRLF);
  echo("<input type='hidden' name='action' id ='action2' value=''/>" . CRLF);
  echo("<input type='hidden' name='backupURI' id ='backupURI' value=''/>" . CRLF);
  echo("<input type='hidden' name='MAX_FILE_SIZE' value='20000000' />" . CRLF);
  echo("<table>" . CRLF);
  
  // File List
  echo("<tr><td colspan=2><b>".$msg["BACKUPRESTORE"]["AvailableBackup"][$Lang]."</b></td></tr>");
  $i=0;
  // Local Folder? or FTP Folder?
  if ((substr($BackupDir,0,1)=="/") || (substr($BackupDir,0,4)=="ftp:")) { 
    // Local backup
	$files = scandir($BackupDir, 1);
  } else {
    // SFTP?
    $choppedURL = parse_url($BackupDir);
    if ($choppedURL["scheme"]=="sftp") {
	  $user = $choppedURL["user"];
	  $pass = $choppedURL["pass"];
	  $host = $choppedURL["host"];
	  if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	  //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
	  $connection = ssh2_connect($host, 22);
	  ssh2_auth_password($connection, $user, $pass);
	  $sftp = ssh2_sftp($connection);
      $files = scandir('ssh2.sftp://' . $sftp . '/' . $path, 1);
	  ssh2_exec($connection, 'logout');
    } // END IF
  } // END IF
  
  while ((isset($files[$i]))) {
   if (($files[$i]!="..") && (substr($files[$i],-7)==".tar.gz")) {
    if (substr($BackupDir,-1)!="/") { $BackupDir=$BackupDir."/"; }
	echo("<tr><td colspan=2><a href='javascript:submitformConfirm(\"ChangeVariables2\",\"action2\",\"reStoreBackup\",\"".$BackupDir.$files[$i]."\");' title='Restore'>".$files[$i]."</a>");
	$Dir="";
	if (substr($BackupDir,0,8)==$_SERVER['DOCUMENT_ROOT']) { $Dir = substr($BackupDir,8); }
	if (substr($BackupDir,0,4)=="ftp:")     { $Dir = $BackupDir.'/'; }
	if ($Dir!="") {
	  echo("&nbsp;<a href='".$Dir.$files[$i]."' target='_blank'><img src='./images/download.png' width=32 height=32 title='Download'/></a>");
	} // END IF
	echo("</td></tr>");
   } // END IF
   $i++;
  } // END WHILE
  
  
  // NO Files :-(
  if ($i==0) { echo("<tr><td colspan=2><font color=red><b>".$msg["BACKUPRESTORE"]["NoBackupFile"][$Lang]."</b></font></td></tr>"); }
  
  echo("<tr><td colspan=2>&nbsp;</td></tr>");
  
  // Upload File
  echo("<tr><td colspan=2><b>".$msg["BACKUPRESTORE"]["BackupUpload"][$Lang]." (.tar.gz):</b></td></tr>");
  echo("<tr><td>&nbsp;</td><td><input name='PackageFile' type='file' /></td></tr>");
  
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"ChangeVariables2\",\"action2\",\"upLoadBackup\")'><img src='./images/upload.png' width='64px' heigth='64px' /></a></td></tr>");
  
  echo("</table>" . CRLF);

  echo("</form>" . CRLF);
  echo("</div>");
  
  echo("<body>" . CRLF);
  echo("<div id='data'></div>" . CRLF);
  echo("</body>" . CRLF);
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
  

  echo("</ul></div>" . CRLF);
  echo("<div class='postcontent'>" . CRLF);
  echo("<div class='clear'></div>" . CRLF);
  echo("</div>" . CRLF);
  echo("<input type='hidden' name='action' value='' />" . CRLF);
  echo("</div>" . CRLF);
  echo("<script type='text/javascript'>" . CRLF);
  echo("function submitform(formID,actionID,action) {" . CRLF);
  //echo("  alert('submit from Form='+formID+' + Action='+action);" . CRLF);
  echo("  document.getElementById(actionID).value = action;" . CRLF);
  echo("  document.getElementById(formID).submit();" . CRLF);
  echo("}" . CRLF);
  //echo("</script>" . CRLF);
  //echo("<script type='text/javascript'>" . CRLF);
  echo("function submitformConfirm(formID,actionID,action,uri) {" . CRLF);
  //echo("  alert('submit Confirm + Action='+action+', from form='+formID);" . CRLF);
  echo("  var answer = confirm('".$msg["BACKUPRESTORE"]["ConfirmRestore"][$Lang]." '+uri+' ?')" . CRLF);
  echo("  document.getElementById(actionID).value = action;" . CRLF);
  echo("  document.getElementById('backupURI').value = uri;" . CRLF);
  echo("  if (answer) { document.getElementById(formID).submit(); }" . CRLF);
  echo("}" . CRLF);
  echo("</script>" . CRLF);

  //mysqli_close($DB);
} // End of Function InstallMod

function importDB($filename) {
  global $DB;
  // Temporary variable, used to store current query
  $templine = '';
  // Read in entire file
  $lines = file($filename);
  // Loop through each line
  foreach ($lines as $line) {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '')
    continue;

    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) == ';') {
      // Perform the query
      mysqli_query($DB,$templine) or print($msg["BACKUPRESTORE"]["SQLQueryNOK"][$Lang].' \'<strong>' . $templine . '\': ' . mysqli_error($DB) . '<br /><br />');
      // Reset temp variable to empty
      $templine = '';
    } // ENDIF
  } // END FOREACH
  if ($filename=="mysql.sql") { mysqli_query($DB,"FLUSH PRIVILEGES"); }
  //echo "Tables imported (".$filename.") successfully"; 
} // END FUNCTION importtDB
?>
