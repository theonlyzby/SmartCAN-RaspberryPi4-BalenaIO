<?php

class zWave_class {
  
  function get_Temp($sensor) {
	global $DB;
	// Detects if first Zwave
	$sql = "SELECT * FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE (`id_sonde` LIKE 'zWave_%') ORDER BY `id_sonde`;";
    $return = mysqli_query($DB,$sql);
    $row = mysqli_fetch_array($return, MYSQLI_BOTH);
	$DBsensor_id   = $row["id_sonde"];
	$id = $row["id"];
	
	// Send zWave command only once!
	if ($DBsensor_id==$sensor) {
	  // Detect if time for next update? (zWave Delay)
	  $sql = "SELECT * FROM `chauffage_clef` WHERE `clef`='zWaveDelay';";
	  $return = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($return, MYSQLI_BOTH);
	  $zWaveDelay = $row["valeur"]; if ($zWaveDelay=="") { $zWaveDelay = "5"; }
	  $INTzWaveDelay = intval($zWaveDelay) - 1;
	  $newTime = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." -".$INTzWaveDelay." minutes"));
	  $sql = "SELECT `update` FROM `chauffage_temp` WHERE `id`=".$id.";";
	  $return = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($return, MYSQLI_BOTH);
	  $update = $row['update'];
	  $sql = "SELECT COUNT(*) AS NextUpdate  FROM `chauffage_temp` WHERE `update`<'".$newTime."' AND `id`=".$id.";";
	  //echo("Next Update (delay=".$INTzWaveDelay.")??? sql = " . $sql . CRLF);
	  $return = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($return, MYSQLI_BOTH);
	  if ($row["NextUpdate"]>0 || $update=="0000-00-00 00:00:00") {
	    //echo("First zWave =".$DBsensor_id.")> GO!".CRLF);
		// Clean possible stoled Zwave python script
		shell_exec("sudo ps -ef | grep 'SetTempZwave.py' | grep -v grep | awk '{print $2}' | xargs -r kill -9");
		// List ALL Zwave Thermostats to build grabber parameters list
	    $sql = "SELECT * FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE (`id_sonde` LIKE 'zWave_%') ORDER BY `id_sonde`;";
	    $return = mysqli_query($DB,$sql);
        $params1 = $params2 = "";
	    $Now    = date("H:i:00");
	    $DayBit = date("N");
        $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
        $i=0;
        // Away?
        $sql3="SELECT * FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef`='absence';";
        $return3 = mysqli_query($DB,$sql3);
        $row3 = mysqli_fetch_array($return3, MYSQLI_BOTH);
        $Away = $row3['clef'];
	    while ($row = mysqli_fetch_array($return, MYSQLI_BOTH)) {
		  $i++;
		  $description[$i] = $row["description"];
		  if ($params1=="") { $n_param="-n "; $t_param=" -t "; } else { $n_param=" "; $t_param=" "; }
		  $node = substr($row["id_sonde"],6);
		  $n[$i] = intval($node);
		  $zone = $row["moyenne"]; if ($zone=="1") { $zone_select = "`active`='Y'"; } else { $zone_select = str_pad("",(intval($zone)-1),"_")."1".str_pad("",(6-intval($zone)),"_"); }
		  $id   = $row["id"];
		  $nID[$i] = $id;

		  // Any Heating Active period?
		  $sql2 = "SELECT COUNT(*) AS County FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `zones`='".$zone_select."';";
		  //echo("sql2 =".$sql2.CRLF);
		  $retour2 = mysqli_query($DB,$sql2);
		  $row2 = mysqli_fetch_array($retour2, MYSQLI_BOTH);
		  if (($row2["County"]!=0) && ($Away=='0')) { $clef = "temperature"; } else { $clef = "tempminimum"; }
		  $sql2 = "SELECT * FROM `chauffage_clef` WHERE `ZoneNber`=".$zone." AND `clef`='".$clef."';";
		  //echo("sql3 =".$sql2.CRLF);
		  $retour2 = mysqli_query($DB,$sql2);
		  $row2 = mysqli_fetch_array($retour2, MYSQLI_BOTH);
		  $temp = $row2["valeur"];
		  //echo("temp =".$temp.CRLF);
		  $params1 .= $n_param . intval($node);
		  $params2 .= $t_param . $temp;
	    } // END WHILE
		//echo("Python CMD = python3 /data/www/smartcan/bin/python/SetTempZwave.py ". $params1 . $params2 . CRLF);
	    $json = shell_exec("sudo /usr/bin/python3 /data/www/smartcan/bin/python/SetTempZwave.py ". $params1 . $params2);
	    //$json = '{"Sensor4": {"comfortTemp": 22.0, "ID": 4, "manufacturer": "zWave", "temperature": 23.9, "battery": 90}, '.
		//			'"Sensor5": {"ID": 5, "manufacturer": "zWave", "temperature": 24.44, "battery": 65}, '.
		//			'"Sensor6": {"comfortTemp": 22.5, "ID": 6, "manufacturer": "zWave", "temperature": 23.54, "battery": 100}, '.
		//			'"Sensor7": {"ID": 7, "manufacturer": "zWave", "temperature": 23.45, "battery": 100}}';
	    //echo("OUT=".$json.CRLF);
        $decoded = json_decode($json);
		
		$j=0;
		while($j<$i) {
		  $j++;
		  if ($decoded->{"Sensor".$n[$j]}->{"temperature"}) {
		    //echo("node (".$n[$j]."), temp=" . $decoded->{"Sensor".$n[$j]}->{"temperature"} . CRLF);
		    $sql = "UPDATE `chauffage_temp` SET `valeur` = '".$decoded->{"Sensor".$n[$j]}->{"temperature"}."', `update` = NOW(), `battery` = '".$decoded->{"Sensor".$n[$j]}->{"battery"}."' WHERE `id` = ".$nID[$j].";";
		    //echo("SQL= " . $sql . CRLF);
		    $return = mysqli_query($DB,$sql);
			$BatVal = intval({"Sensor".$n[$j]}->{"battery"}));
			if ($BatVal<20) {
			  // Parse DB to find active users, their lang & Firebase Token
			  $sql4 = "SELECT * FROM `users_notification`;";
			  $return4 = mysqli_query($DB, $sql4);
			  $base_curl = "curl -X POST -H \"Authorization: key=AAAAGAKq-Y4:APA91bH9gphJptTwGpiQ32cHpldseJMsRWCV6jdyAB-ESHX4Vxs3XEmABzwz7Im7QD0SBCVvQeJRxgdbmsm3KGZwRaLnA8vzBIkNz3wbFO4L55x2KTFTdO6O03UwIv1RowqKVY36dTuO\" " .
					"-H \"Content-Type: application/json\" -d '{\"data\": {\"notification\": {";
			  while ($row4 = mysqli_fetch_array($return4, MYSQLI_BOTH)) {
			    // Send ALERT Notification
				$curl = $base_curl . "\"title\": \"ALARM: Battery LOW\", " .
					"\"body\": \"" . $description[$j] . " LOW! (" . $BatVal . "%)\", " .
					"\"icon\": \"/smartcan/www/images/icons/icon-192x192.png\" } }," .
					"\"to\": \"".$row4["Token"]."\" }' https://fcm.googleapis.com/fcm/send";
				//echo("curl: " . $curl . CRLF);
				$feedback = exec($curl);
				$dec = json_decode($feedback);
				if (!is_numeric(substr($dec->{"results"}[0]->{"message_id"},0,1))) {
				  //echo("NOK");
				  // BAD Feedback, will remove from DB
				  $sql2 = "DELETE FROM `users_notification` WHERE `Alias`='".$row["Alias"]."' AND `Lang`='".$row["Lang"]."' AND `User_Agent`='".$row["User_Agent"]."' AND `Token`='".$row["Token"]."';";
				  mysqli_query($DB, $sql2);
				} // END IF
			  } // END WHILE
	
			} // END IF
		  } // END IF
		} // END WHILE
	  } else {
	    //echo($sensor.", NOT running Yet ... wait" . CRLF);
	  } // END IF
	} else {
	  //echo("SECOND zWave =".$sensor.")> NO GO (NOT ".$DBsensor_id .") :-)".CRLF);
	} // END IF
  
	// Return Temperature "N/A" if not used
	return("N/A");

  } // END Function
  

} // END Class

?>