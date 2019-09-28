<?php
// Daily Tasks run @04:05

// Includes
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
include_once $base_URI.'/www/smartcan/www/conf/config.php';

// Connect DB
$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_set_charset($DB,'utf8'); 
mysqli_select_db($DB,mysqli_DB);

// Remove UnDeleted One Time Heating Requests
$sql = "DELETE FROM `ha_thermostat_timeslots` WHERE `days`='00000001' AND NOT ('04:05:00' BETWEEN `start` AND `stop`);";
$query = mysqli_query($DB,$sql);
echo("Thermostat timeslots left over deleted! " . $query.chr(10));


// Get Config Settings
$fileOK=""; 
$sql = "SELECT * FROM `ha_settings` WHERE `variable`='backup_uri' OR `variable`='daily_backup' OR `variable`='weekly_backup' OR `variable`='monthly_backup';";
$query = mysqli_query($DB,$sql);
while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
  $Var = $row['variable'];
  $Val = $row['value'];
  if ($Var=="backup_uri") { if ($Val=="") { $BackupDir = $base_URI."/www/backups/"; $BackupDirDisplay = "";} else { $BackupDir = $BackupDirDisplay = $Val; }}
  if ($Var=="daily_backup")   { $daily_backup   = $Val; }
  if ($Var=="weekly_backup")  { $weekly_backup  = $Val; }
  if ($Var=="monthly_backup") { $monthly_backup = $Val; }
} // END WHILE

  // Backup
  if (($daily_backup=="Y") || (($weekly_backup=="Y") && (date("N")=="5")) || (($monthly_backup=="Y") && (date("d")=="01"))) {
    $BackupDest = $base_URI."/www/backups/";
    exec('mysqldump --user=' . MYSQL_LOGIN . ' --password=' . MYSQL_PWD . " " . MYSQL_DB . ' > ' . $BackupDest . 'domotique.sql');
	$fileName = date("Ymd-His-").'FULLsmartCAN-BACKUP.tar.gz';
	exec('tar -czvf '. $BackupDest.$fileName . " " .
			$BackupDest . 'domotique.sql ' .
			PATHBASE . '/www/conf ' .
			PATHBASE . '/www/images/outputs ' .
			PATHBASE . '/www/images/plans ' .
			PATHBASE . '/bin/domocan-server ' .
			PATHBASE . '/rrdtool ' .
			'/etc/network ' .
			'--exclude={"/etc/network/run","/etc/network/if*.d"}');
	exec('rm ' . $BackupDest . 'domotique.sql');
	// Need to move?
	if ($BackupDest!=$BackupDir) {
	  // to Local?
	  if (substr($BackupDir,0,1)=="/") {
	    rename($BackupDest.$fileName, $BackupDir.$fileName);
	  } // END IF
	  // to FTP ?
	  if (substr($BackupDir,0,4)=="ftp:") {
	    $pass_pos = strrpos($BackupDir,":");
		$user = substr($BackupDir,6, $pass_pos-6);
		$host_pos = strrpos($BackupDir,"@");
		$pass = substr($BackupDir, $pass_pos+1, $host_pos-$pass_pos-1);
		$path_pos = strpos(substr($BackupDir,$host_pos),"/")+$host_pos;
		$host = substr($BackupDir, $host_pos+1, $path_pos-$host_pos-1);
		$path = substr($BackupDir, $path_pos+1)."/";
	    //echo("ftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		$conn_id = ftp_connect($host); 
		// login with username and password 
		$login_result = ftp_login($conn_id, $user, $pass); 
		// upload a file 
		//echo("Move =".$BackupDest.$fileName."= to FTP =" . $path.$fileName . "=<br>");
		if (ftp_put($conn_id, "/".$path."/".$fileName, $BackupDest.$fileName, FTP_BINARY)) { 
		  echo("<b>FTP ok</b><br>");
		  exec('rm ' . $BackupDest . $fileName);
		} // END IF
		ftp_close($conn_id);
	  } // END IF
	  // to SFTP ?
	  if (substr($BackupDir,0,5)=="sftp:") {
	    $pass_pos = strrpos($BackupDir,":");
		$user = substr($BackupDir,7, $pass_pos-7);
		$host_pos = strrpos($BackupDir,"@");
		$pass = substr($BackupDir, $pass_pos+1, $host_pos-$pass_pos-1);
		$path_pos = strpos(substr($BackupDir,$host_pos),"/")+$host_pos;
		$host = substr($BackupDir, $host_pos+1, $path_pos-$host_pos-1);
		$path = substr($BackupDir, $path_pos+1);
	    //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
		$connection = ssh2_connect($host, 22);
		ssh2_auth_password($connection, $user, $pass);
		$sftp = ssh2_sftp($connection);
		//echo("Move =".$BackupDest.$fileName."= to SFTP =" . $path.$fileName . "=<br>SFTP: =ssh2.sftp://".$sftp."/".$path.$fileName."<br>");
		$file = file_get_contents($BackupDest.$fileName);
		if (file_put_contents("ssh2.sftp://".$sftp."/".$path."/".$fileName, $file)) {
		  echo("<b>SFTP ok</b><br>");
		  exec('rm ' . $BackupDest . $fileName);
		} // END IF
		ssh2_exec($connection, 'logout');
	  } // END IF
	} // END IF
  } // END IF
  
// Write New Time into RTC
shell_exec('sudo hwclock -w');
// Send New Time to Horloge Card on CAN Bus

// Erase Logs
exec('sudo rm -R '.$base_URI."/log/*');

// Daily Reboot within 5 minutes, if needed?!?
// shell_exec('sudo /sbin/shutdown -r +5 Daily Reboot');
?>
