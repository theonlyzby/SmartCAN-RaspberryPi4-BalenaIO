<?php

class zWave_class {
  
  function new() {
	  global $Moyenne, $id_sonde, $Temp_Name, $DB;
	  //$DHT22_pin = html_get("DHT22_Pin");
	  //echo("New zWave, id_sonde=".$id_sonde.", DHT22_pin=".$DHT22_pin);
	

	  //$id_sonde  = substr(html_get("id_sonde"),4);
	  $ZoneID    = html_get("ZoneID");
	  $Zone_Name = html_get("Zone_Name");
	  $ZoneNber  = html_get("ZoneNber");
	  $NewZwave  = html_get("NewZwave");
	  
	  //echo("ZoneID=".$ZoneID . "NewZwave=" . $NewZwave . "<br>".CRLF);
	  // Assign Zone if new
	  if ($ZoneID=="0") {
	    $sql = "UPDATE `ha_thermostat_zones` SET `Name` = '" . $Zone_Name . "' WHERE `ZoneNber` = " . $ZoneNber . ";";
		mysqli_query($DB,$sql);
		$ZoneID = $ZoneNber;
	  } // END IF
	  
	  //echo("Moyenne Before=".$Moyenne.",After=".$ZoneID."<br>".CRLF);
	  $Moyenne = $ZoneID;
	  
	  
	  // Return ID value to save into DB
	  return($NewZwave);
	  //return("id".$id_sonde."ZID".$ZoneID."ZN".$Zone_Name."ZC".$ZoneColor."NZW".$NewZwave);

  }
  
  function HTMLoption() {
	  GLOBAL $DB, $msg, $Lang;
	  $sql = "SELECT * FROM `".TABLE_ELEMENTS."` WHERE (`element_type`='0x31' AND `Manufacturer`='zWave')  ORDER BY `id` ASC;";
	  $query = mysqli_query($DB,$sql);
	  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
		$sql2 = "SELECT COUNT(*) AS County FROM `".TABLE_CHAUFFAGE_SONDE."` WHERE `id_sonde`='zWave_".str_pad($row['card_id'], 4, "0", STR_PAD_LEFT)."'";
		$query2 = mysqli_query($DB,$sql2);
		$row2 = mysqli_fetch_array($query2, MYSQLI_BOTH);
		if ($row2['County']==0) {
	      echo("<option value='zWave_".str_pad($row['card_id'], 4, "0", STR_PAD_LEFT)."' >" . $row['Manufacturer'] . " " . $row['element_name'] . ", Node " . $row['card_id'] . "</option>" . CRLF);
		}
	  } // End While


	  
	  
	  
	  
	  
  } // END FUNCTION HTMLselect
  
  
  function HTMLconfig() {
	  GLOBAL $msg, $Lang, $DB;
	  // Determine Local IP Range
	  $ip = shell_exec("ifconfig eth0| grep 'inet ' | cut -d: -f2");
	  $ip=substr($ip,0, strpos($ip, " netmask")-1);
	  $ip=substr($ip,(strrpos($ip," ")-strlen($ip)+1));
	  $ip=substr($ip,0,strrpos($ip,".")+1);
	  

	  // Existing Zone ? 
	  echo('<tr id="NewzWave0" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF);
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["Zone"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><select id="ZoneID" name="ZoneID" onchange="NewZone()"><option value="">Select</option>');
	  

	  // Already defined Zones?
	  $sql = "SELECT * FROM `ha_thermostat_zones`;";
	  $query = mysqli_query($DB,$sql);
	  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
		if ($row["Name"]!="") {
	      echo('<option value="' . $row["ZoneNber"] . '" style="background-color: #' . $row["Color_Code"] . ';color: white;">' . $row["Name"] .'</option>' . CRLF);
		} // END IF
	  } // END WHILE
	  echo('<option value="0" onselect="javascript:NewZone();">' . $msg["TEMPS"]["NewZone"][$Lang] . '</option></select></td></tr>' . CRLF);

	  
	  // New Zone Name
	  echo('<tr id="NewzWave1" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF); # table-row
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["ZoneName"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><input id="Zone_Name" name="Zone_Name" type="text"/></td></tr>' . CRLF);
	  
	  // New Zone Color
	  echo('<tr id="NewzWave2" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF); # table-row
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["ZoneColor"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><select id="ZoneNber" name="ZoneNber"><option value="">Select</option>' . CRLF);
	  $sql = "SELECT * FROM `ha_thermostat_zones`;";
	  $query = mysqli_query($DB,$sql);
	  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
		if ($row["Name"]=="") {
	      echo('<option value="'.$row["ZoneNber"].'" style="background-color: #'.$row["Color_Code"].';color: white;">' . $msg["TEMPS"][$row["Color_Name"]][$Lang] .'</option>' . CRLF);
		} // END IF
	  } // END WHILE

	  echo('</select></td></tr>' . CRLF);
	  echo('<input type="hidden" id="NewZwave" name="NewZwave" value="3487">' . CRLF);
	  
  } // END FUNCTION HTMLconfig
  
  function javaChange() {
	  //echo('alert("sel="+selected);');
	  echo('if (selected.substring(0, 6)==="zWave_") { document.getElementById("Mean01").style.display = "none";document.getElementById("Mean02").style.display = "none";'.
	       'document.getElementById("NewzWave0").style.display = "table-row";'.
		   '} else { document.getElementById("Mean01").style.display = "inline";document.getElementById("Mean02").style.display = "inline";' .
		   'document.getElementById("NewzWave0").style.display = "none";document.getElementById("NewzWave1").style.display = "none";}' . CRLF);
      echo('}' . CRLF);
      echo('function NewZone() {' . CRLF);
	  echo('  var value = document.getElementById("ZoneID").value;');
      echo('  if (value==0) { document.getElementById("NewzWave1").style.display = "table-row";document.getElementById("NewzWave2").style.display = "table-row";  document.getElementById("Zone_Name").focus();}' . CRLF);
      echo('  else { document.getElementById("NewzWave1").style.display = "none";document.getElementById("NewzWave2").style.display = "none"; }' . CRLF);
      
  } // END FUNCTION javaChange

  function HTMLcheck() {
	global $msg, $Lang;
    echo('var e = document.getElementById("ZoneID");' . CRLF);
    echo('  var ZoneID = e.options[e.selectedIndex].value;' . CRLF);	
	echo('  if ((document.getElementById("NewzWave0").style.display=="table-row") && (ZoneID=="")) { alert("' . $msg["TEMPS"]["NOZoneError"][$Lang] .'");return; }');
    echo('  var e = document.getElementById("ZoneNber");' . CRLF);
    echo('  var ZoneNber = e.options[e.selectedIndex].value;' . CRLF);
	echo('  if ((document.getElementById("NewzWave1").style.display=="table-row") && ((document.getElementById("Zone_Name").value=="") || (ZoneNber==""))) { alert("' . $msg["TEMPS"]["ZoneSelectError"][$Lang] .'");return; }' . CRLF);
    echo('  var e = document.getElementById("id_sonde");' . CRLF);
	echo('  var t = e.options[e.selectedIndex].text;' . CRLF);
	//echo(' alert(t.substr(t.length-4));');
    echo('  e.options[e.selectedIndex].value="NewzWave";' . CRLF);
	echo('  document.getElementById("NewZwave").value="zWave_"+t.substr(t.length-4) ;' . CRLF);
  } // END FUNCTION HTMLcheck

} // END Class

?>
