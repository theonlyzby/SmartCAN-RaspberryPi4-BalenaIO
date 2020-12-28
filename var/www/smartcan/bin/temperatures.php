<?php
/*
Info sources for Web Temp:
http://blog.turningdigital.com/2012/09/raspberry-pi-ds18b20-temperature-sensor-rrdtool/
http://weather.noaa.gov/pub/data/observations/metar/decoded/EBBR.TXT
*/
  /*
    SCRIPT DE RECUPERATION DES TEMPERATURES VIA 1WIRE, ET MISE EN BDD
  */

  /* DEPENDANCIES */
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
  include_once($base_URI.'/www/smartcan/www/conf/config.php');

  /* SQL CONNECTION */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  // chauffage_temp & lumieres_status Table already populated? + Determine Server's IP in config.php
  $sql = "SELECT COUNT(*) AS county FROM `" . TABLE_CHAUFFAGE_TEMP . "`;";
  $retour = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  if ($row['county']==0) {
    // Populate chauffage_temp (temperaure reading in RAM) ... no disk access every minute ;-)
    $sql = "SELECT * FROM  `" . TABLE_CHAUFFAGE_SONDE . "` ORDER BY `id`;";
    $retour = mysqli_query($DB,$sql);
    while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      $id   = $row['id'];
	  $mean = $row['moyenne'];
      $sql2 = "INSERT INTO `" . TABLE_CHAUFFAGE_TEMP . "` (`id`, `valeur`, `moyenne`, `update`) VALUES ('" . $id . "', '0', '" . $mean . "', '0000-00-00 00:00:00');";
      $retour2 = mysqli_query($DB,$sql2);
    } // END WHILE
	
	// Populates lumieres_status (light status in RAM)
	$sql = "SELECT * FROM  `" . TABLE_LUMIERES . "` ORDER BY `id`;";
	$retour = mysqli_query($DB,$sql);
	while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      $id   = $row['id'];
	  $sql2 = "INSERT INTO `" . TABLE_LUMIERES_STATUS . "` (`id`, `valeur`, `timer_pid`) VALUES ('" . $id . "', '00', '0');";
      $retour2 = mysqli_query($DB,$sql2);
	  // Set configured GPIOs in OUTPUT Mode
	  if ($row['Manufacturer']=="wiringPI") {
	    exec('gpio -1 mode '.$row['sortie'].' out');
		exec('gpio -1 write '.$row['sortie'].' 0');
	  } // END IF
    } // END WHILE
	
	// Sets wiringPi pin modes
	$sql = "SELECT * FROM `ha_element` WHERE `Manufacturer`='wiringPI';";
	$retour = mysqli_query($DB,$sql);
	while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
	  $pin     = $row['element_reference'];
	  $type    = $row['element_type'];
	  $trigger = $row['card_id']; if ($trigger=="low") { $trigger="1"; } else { $trigger="0"; }
	  if (($type == "0x11") || ($type == "0x12") ) { exec('gpio -1 mode '.$pin.' out'); exec('gpio -1 write '.$pin.' '.$trigger);}
	  if  ($type == "0x22") { exec('gpio -1 mode '.$pin.' in'); }
	} // END WHILE
	
	
	// Populates chauffage_clef_TEMP (Heating system Status in RAM)
	$sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'chaudiere', '0');";
	$retour = mysqli_query($DB,$sql);
	$sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'boiler', '0');";
	$retour = mysqli_query($DB,$sql);
	$sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'warm_water', '1');"; 
	$retour = mysqli_query($DB,$sql);
	
	// If NOT on Balena, Check Server's IP address into config.php ... and change it if needed
	if ($base_URI == "/var") {
	  $ifconfig = shell_exec('/sbin/ifconfig eth0');
	  preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
	  $server_IP=$match[1];
	  if ($server_IP!=LOCAL_IP) {
	    // Need to modify config.php
	    $myFile = $base_URI."/www/smartcan/www/conf/config.php";
	    $reading   = fopen($myFile,'r');
	    $writing   = fopen($myFile.".tmp","w");
	    while(!feof($reading)) {
	      $line = fgets($reading,4096);
		if (strpos($line,"define('LOCAL_IP'")!==false) {
		  fwrite($writing,"  define('LOCAL_IP', '".$server_IP."');" .chr(13).chr(10));
		} else {
		  fwrite($writing,$line);
		} // END IF
	    } // END WHILE
	    fclose($reading);
	    fclose($writing);
	    shell_exec("sudo cp -f ".$myFile.".tmp ".$myFile);
	    shell_exec("sudo rm -f ".$myFile.".tmp");
	  } // END IF ($server_IP!=LOCAL_IP)
	} // END IF
	
	
  } // END IF


  /* RECUPERATION DES VALEURS DE TEMPERATURE ET MISES A JOUR DES GRAPHIQUES */
  $sql = "SELECT * FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE 1 ORDER BY `id_sonde`;"; // 'ESP_%'    (`id_sonde` NOT LIKE '%_%')
  $retour_s = mysqli_query($DB,$sql);
  while ( $row_s = mysqli_fetch_array($retour_s, MYSQLI_BOTH) ) {
    $sensor      = $row_s["id_sonde"];
	$sensor_id   = $row_s["id"];
	$sensor_name = $row_s["description"];
	$b           = "";
	
	// From Other manufacturer (than 1wire and Internet)?
	if (strpos($sensor, "_") !== false) {
	  //echo("External Sensor = " . $sensor . ", strpos=" . strpos($sensor, "_") . CRLF);
	  include_once(PATHBIN . "Manufacturers".'/'.substr($sensor,0,strpos($sensor, "_")).'.Addon.TempsBin.php');
	  $addOnClass_fullName = substr($sensor,0,strpos($sensor, "_")) . "_class";
	  $addOnClass          = new $addOnClass_fullName();
	  $b = $addOnClass->get_Temp($sensor);
	  if ($b!="N/A") {
	    //echo("GO for ".$sensor."! temp=".$b . CRLF);
		//$sql = "UPDATE `" . TABLE_CHAUFFAGE_TEMP . "` SET `valeur` = '" . $b . "', `update` = now() WHERE `id` = '" . $sensor_id . "';";
	    //mysqli_query($DB,$sql);
	  } else {
	    //echo("NO GO for ".$sensor.CRLF);
	  } // END IF
	  //exit; 
	} else {
	  // Built in Sensor readin (1wire or Internet)
	  //echo("Included grabber for :".$sensor.CRLF);
	
	  if (substr($sensor,0,2)=="28") {
        // 1 Wire
	    // 1Wire Mode = OWFS
	    if (ONEWIRE_MODE=="OWFS") {
          $a = exec('cat ' . PATHOWFS . '/' . $sensor . '/temperature');
          $b = round(str_replace(' ', '', $a), 2);
        } // END IF
	    // 1Wire Mode = RaspberryPi
	    if (ONEWIRE_MODE=="RPI") {
	      $OneWireDir = "/sys/bus/w1/devices/" . $sensor . "/w1_slave";
	      $data = array();
	      $data = file($OneWireDir);  
	      $data = explode('t=',$data[1]);
	      $b = round((int)$data[1]/1000, 2); 
	    } // END IF
	    $sql = "UPDATE `" . TABLE_CHAUFFAGE_TEMP . "` SET `valeur` = '" . $b . "', `update` = now() WHERE `id` = '" . $sensor_id . "';";
	    mysqli_query($DB,$sql);
	  } else {
	    // From http://weather.noaa.gov ?
	    $URL = WEB_TEMP_URL . $sensor . ".TXT";
	    $k=0;$b="";
	    while (($k<3) && ($b=="")) {
	      if (($handle = fopen($URL, "r")) !== FALSE) {
            $data = fgetcsv($handle, 1000, " ");
            $date = str_replace("/","-",$data[0]) . " " . $data[1] . ":00";
            $data = fgetcsv($handle, 1000, " ");
		    $j=0;
		    while (isset($data[$j])) {
		      if (strpos($data[$j],"/")) { break;}
		      $j++;
		    } // END WHILE
		    $b="";
            if (substr($data[$j],0,1)=="M") { $b = "-" . substr($data[$j],1, strpos($data[$j],"/")-1); } else { $b = substr($data[$j],0, strpos($data[$j],"/"));}
            fclose($handle);
		
		    //echo(CRLF.CRLF.CRLF.CRLF.CRLF." Value b=$b=-".CRLF);
		    // Update DB
		    if ($b!="") {
		      //$sql = "UPDATE ($sensor_id)`:" . TABLE_CHAUFFAGE_TEMP . "` SET `valeur` = '" . $b . "', `update` = '" . $date . "' WHERE `id` = '" . $sensor_id . "';";
		      $sql = "UPDATE `" . TABLE_CHAUFFAGE_TEMP . "` SET `valeur` = '" . $b . "', `update` = now() WHERE `id` = '" . $sensor_id . "';";
		      //echo("\n UPDATE Temp ".$sql.CRLF);
		      $retour = mysqli_query($DB,$sql);
		    } // END IF
		  
		    //echo("k=$k, Ext Temp=$b".CRLF);
		  } // END IF
		  $k++;
        } // END WHILE
	  } // END IF
	} // END IF Manufacturer
	
	  // TempLog ... 4 Pitchout
	if ((fmod(date('i'),2)==0) && (EXCELTEMPLOGSPATH!='') && ($b!="")) {
	  $handle = fopen(EXCELTEMPLOGSPATH . date('Ymd') . "-TempLog.csv", "a");
	  fwrite($handle, date('d/m/Y') . "," . date('H:i:00') . "," . $sensor_name . "," . $b . chr(13));
	  fclose($handle);
	} // ENDIF	
		
	// RRD Graph
	if ((RRDPATH!="") && (date("i")%5==0) && ($b!="")) {
	  //echo("Test if exists? ".RRDPATH . $sensor . '.rrd'. CRLF);
      if (!file_exists(RRDPATH . $sensor . '.rrd') ) {
        //echo(RRDPATH . 'creation.sh ' . RRDPATH . $sensor  . '.rrd'.CRLF);
		exec(RRDPATH . 'creation.sh ' . RRDPATH . $sensor  . '.rrd');	  
      } // END IF
      //echo('rrdtool update ' . RRDPATH . $sensor . '.rrd N:' . round($b, 1).CRLF);
	  exec('rrdtool update ' . RRDPATH . $sensor . '.rrd N:' . round($b, 1));
	  //echo(RRDPATH . 'mettre_a_jour.sh ' . RRDPATH . $sensor .CRLF);
      exec(RRDPATH . 'mettre_a_jour.sh ' . RRDPATH . $sensor );
    } // END IF
	
	// PUSH Valeur Sonde
	if ($b!="") {
	  $ch = curl_init(URIPUSH);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;" . $sensor  . "," . round($b, 1));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $ret = curl_exec($ch);
      curl_close($ch);
	} // END IF
  } // END WHILE
  
  /* MISE A JOUR DU GRAPHIQUE DE TEMPERATURE MOYENNE MAISON */
  $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE (`moyenne` = '1' AND `valeur`<>0 AND `update`>=DATE_SUB(now(), INTERVAL 2 MINUTE));");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  if ((RRDPATH!="") && (date("i")%5==0)) {
    //echo("Test if exists? ".RRDPATH . $sensor . '.rrd'. CRLF);
    if ( !file_exists(RRDPATH . 'temperaturemaison.rrd') ) {
      //echo(RRDPATH . 'creation.sh ' . RRDPATH . 'temperaturemaison.rrd'.CRLF);
	  exec(RRDPATH . 'creation.sh ' . RRDPATH . 'temperaturemaison.rrd');
    }
	//echo('rrdtool update ' . RRDPATH . 'temperaturemaison.rrd N:' . round($row[0], 1));
    exec('rrdtool update ' . RRDPATH . 'temperaturemaison.rrd N:' . round($row[0], 1));
	//echo(RRDPATH . 'mettre_a_jour.sh ' . RRDPATH . 'temperaturemaison');
    exec(RRDPATH . 'mettre_a_jour.sh ' . RRDPATH . 'temperaturemaison');
  } // END IF
	
  // PUSH Valeur Moyenne
  if ($row[0]=="") { $mm="?"; } else { $mm=round($row[0],1); }
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;moyennemaison," . $mm);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);
  
  /* MISE A JOUR DU GRAPHIQUE DE TEMPERATURE EXTERIEURE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  
  if ((RRDPATH!="") && (date("i")%5==0) && ($row[0]!="")) {  
    //echo("Test if exists? ".RRDPATH . 'temperatureexterieure.rrd'. CRLF);
    if ( !file_exists(RRDPATH . 'temperatureexterieure.rrd') ) {
	  //echo(RRDPATH . 'creation.sh ' . RRDPATH . 'temperatureexterieure.rrd');
      exec(RRDPATH . 'creation.sh ' . RRDPATH . 'temperatureexterieure.rrd');
    }
	//echo('rrdtool update ' . RRDPATH . 'temperatureexterieure.rrd N:' . round($row[0], 1).CRLF);
    exec('rrdtool update ' . RRDPATH . 'temperatureexterieure.rrd N:' . round($row[0], 1));
	//echo(RRDPATH . 'mettre_a_jour.sh ' . RRDPATH . 'temperatureexterieure'.CRLF);
    exec(RRDPATH . 'mettre_a_jour.sh ' . RRDPATH . 'temperatureexterieure');
  } // END IF
  
  // PUSH Valeur Temp Exterieure
  if ($row[0]!="") {
    $ch = curl_init(URIPUSH);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;temperatureexterieure," . round($row[0], 1));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    curl_close($ch);
  } // END IF

// Heater End & Next
  /* PRERIODE DE CHAUFFE? */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour = mysqli_query($DB,$sql);
  $row    = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "PERIODECHAUFFE;" . $row[0]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);

  
  /* AFFICHAGE DE FIN DE LA PERIODE DE CHAUFFE EN COURS */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT stop FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y' ORDER BY start DESC;";
  $retour = mysqli_query($DB,$sql);
  if ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
    $heure = substr($row[0],0,2) . substr($row[0],3,2);
	$ch = curl_init(URIPUSH);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "FINCHAUFFE;" . $heure);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($ch);
	curl_close($ch);
  } else {
    $ch = curl_init(URIPUSH);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "FINCHAUFFE;");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($ch);
	curl_close($ch);
  }

  /* AFFICHAGE DE LA PROCHAINE PERIODE DE CHAUFFE */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND (`days` LIKE '" . $Today . "') AND (`start`>'" . $Now . "') AND `active`='Y' ORDER BY `start`;";
  
  $retour = mysqli_query($DB,$sql);
  if ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
	if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . substr($row[0],3,2);}
    $ch = curl_init(URIPUSH);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "PROCHAINECHAUFFE;" . $heure);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($ch);
	curl_close($ch);
  } else {
    $DayBit   = date("N",mktime(1, 1, 1, date("m"), date("d")+1, date("y")));
    $Tomorrow = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
    $sql      = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE (`function`='HEATER'  AND (`days` LIKE '" . $Tomorrow . "') AND `active`='Y') ORDER BY `start`;";
    $retour   = mysqli_query($DB,$sql);
	$row=mysqli_fetch_array($retour, MYSQLI_BOTH);
	if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . substr($row[0],3,2);}
    $ch = curl_init(URIPUSH);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "PROCHAINECHAUFFE;" . $heure);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($ch);
	curl_close($ch);
  } // ENDIF

// End New Mod


// Every 5 minutes
if (date("i")%5==0) {
  // Need copy them to ftp or sftp?
  $sql = "SELECT * FROM `ha_settings` WHERE `variable`='graph_uri';";
  $query = mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
  $GraphDest = $row['value'];

  // Need to move?
  if ($GraphDest!="") {
    // Isolate arguments in full URI
    $choppedURL = parse_url($GraphDest);
    $user = $choppedURL["user"];
    $pass = $choppedURL["pass"];
    $host = $choppedURL["host"];
    if (isset($choppedURL["path"])) { $path = $choppedURL["path"]; } else { $path = "";}
	// ftp ?
    if ($choppedURL["scheme"]=="ftp") {
      //echo("ftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
	  $conn_id = ftp_connect($host); 
	  // login with username and password 
	  $login_result = ftp_login($conn_id, $user, $pass); 
	  // upload a file 
	  //echo("Move =".$GraphDest.$fileName."= to FTP =" . $path.$fileName . "=<br>");
	  $files = scandir(RRDPATH); $i=0;
	  while (isset($files[$i])) {
	    if ((substr($files[$i],0,1)!=".") && (substr($files[$i],-4)==".png")) {
		  //echo($files[$i] . CRLF);
		  ftp_put($conn_id, "/".$path."/".$files[$i], RRDPATH.$files[$i], FTP_BINARY);
	    } // END IF
	    $i++;
	  } // END WHILE
	  ftp_close($conn_id);
    } // END IF
    // to SFTP ?
    if ($choppedURL["scheme"]=="sftp") {
      //echo("sftp ! Host=$host, Path=$path, User=$user, Passwd=$pass<br>");
	  $connection = ssh2_connect($host, 22);
	  ssh2_auth_password($connection, $user, $pass);
	  $sftp = ssh2_sftp($connection);
	  //echo("Move =".$GraphDest.$fileName."= to SFTP =" . $path.$fileName . "=<br>SFTP: =ssh2.sftp://".$sftp."/".$path.$fileName."<br>");
	  $files = scandir(RRDPATH); $i=0;
	  while (isset($files[$i])) {
	    if ((substr($files[$i],0,1)!=".") && (substr($files[$i],-4)==".png")) {
		  //echo($files[$i] . CRLF);
		  $file = file_get_contents(RRDPATH.$files[$i]);
		  file_put_contents("ssh2.sftp://".$sftp."/".$path."/".$files[$i], $file);
	    } // END IF
	    $i++;
	  } // END WHILE
	  ssh2_exec($connection, 'logout');
    } // END IF
  } // END IF
} // END IF
mysqli_close($DB);
?>
