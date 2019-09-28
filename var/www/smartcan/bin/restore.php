<?php
// System restore ... from raspi-config

// Includes
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
include_once $base_URI.'/www/smartcan/www/conf/config.php';

// Connect DB
$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_set_charset($DB,'utf8'); 
mysqli_select_db($DB,mysqli_DB);

$variable = $argv[1];
//echo("Console Variable = $variable");

if ($variable=="/boot/FULLsmartCAN-BACKUP.tar.gz") {
  // Restore
  // UnTAR & Restore Files & DB
  $BackupDest = $base_URI."/www/backups";
  echo("Uncompressing /boot/FULLsmartCAN-BACKUP.tar.gz /n" . CRLF);
  shell_exec("sudo /bin/tar zxvf /boot/FULLsmartCAN-BACKUP.tar.gz -C /  2>&1");
  // SQL File present in Extracted files
  $ScanDir  = scandir($BackupDest);
  $ndir=0;
  while (isset($ScanDir[$ndir+2])) {
    if (substr($ScanDir[$ndir+2],-4)==".sql") { 
	  echo("SQL File : ".$ScanDir[$ndir+2]. " Restored ;-) /n". CRLF);
	  importDB($BackupDest."/".$ScanDir[$ndir+2]);
	  unlink($BackupDest."/".$ScanDir[$ndir+2]);
    } // END IF
	if ($ScanDir[$ndir+2]=="samba-users.smbback") {
	  echo("Restoring SAMBA Passwords/n");
	  shell_exec("sudo pdbedit -i smbpasswd:".$BackupDest."/samba-users.smbback");
	  unlink($BackupDest."/".$ScanDir[$ndir+2]);
	} // END IF
    $ndir++;
  } // END WHILE
} // END IF

function importDB($filename) {
  global $DB;
  $table = substr($filename,strrpos($filename,"/")+1,strlen($filename)-5-strrpos($filename,"/"));
  echo("Select Table: $table/n");
  mysqli_select_db($DB,$table);
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
      mysqli_query($DB,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($DB) . '<br /><br />');
      // Reset temp variable to empty
      $templine = '';
    } // ENDIF
  } // END FOREACH
  //if ($filename=="mysql.sql") { mysqli_query($DB,"FLUSH PRIVILEGES"); }
  //echo "Tables imported (".$filename.") successfully"; 
} // END FUNCTION importtDB


?>
