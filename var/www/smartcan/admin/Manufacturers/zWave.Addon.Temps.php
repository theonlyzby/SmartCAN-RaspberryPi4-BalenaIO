<?php

class zWave_class {

  
  function new() {
	  global $id_sonde;
	  global $Temp_Name;
	  //$DHT22_pin = html_get("DHT22_Pin");
	  //echo("New zWave, id_sonde=".$id_sonde.", DHT22_pin=".$DHT22_pin);
	
	  $id_sonde  = html_get("id_sonde");
	  //$DHT22_pin = html_get("DHT22_Pin");
		

	  
	  // Return ID value to save into DB
	  return("Zwave_" . $id_sonde);

  }
  
  function HTMLoption() {
	  GLOBAL $DB, $msg, $Lang;
	  $sql = "SELECT * FROM `".TABLE_ELEMENTS."` WHERE `element_type`='0x31'  ORDER BY `id` ASC;";
	  $query2 = mysqli_query($DB,$sql);
	  while ($row2 = mysqli_fetch_array($query2, MYSQLI_BOTH)) {
	    echo("<option value='Zwave_".$row2['card_id']."' >" . $row2['Manufacturer'] . " " . $row2['element_name'] . ", Node " . $row2['card_id'] . "</option>" . CRLF);
	  } // End While


	  
	  
	  
	  
	  
  } // END FUNCTION HTMLselect
  
  
  function HTMLconfig() {
	  GLOBAL $msg, $Lang;
	  // Determine Local IP Range
	  $ip = shell_exec("ifconfig eth0| grep 'inet ' | cut -d: -f2");
	  $ip=substr($ip,0, strpos($ip, " netmask")-1);
	  $ip=substr($ip,(strrpos($ip," ")-strlen($ip)+1));
	  $ip=substr($ip,0,strrpos($ip,".")+1);
	  
	  echo('<tr id="NewzWave0" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF);
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["IPaddress"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><input type="text" name="ESP_IP" id="ESP_IP" value="' . $ip . '"></td></tr>' . CRLF);
	  echo('<tr id="NewzWave1" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF);
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["DHT22GPIO"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><select name="DHT22_Pin"><option value=0>GPIO-0 (D3)</option><option value=1 disabled>GPIO-1 (D10)</option><option value=2 selected>GPIO-2 (D4)</option>' . CRLF);
	  echo('<option value=3 disabled>GPIO-3 (D9)</option><option value=4 disabled>GPIO-4 (D2)</option><option value=5 disabled>GPIO-5 (D1)</option><option value=9>GPIO-9 (D11) &#9888;</option>' . CRLF);
	  echo('<option value=10>GPIO-10 (D12)</option><option value=12>GPIO-12 (D6)</option><option value=13>GPIO-13 (D7)</option><option value=14>GPIO-14 (D5)</option><option value=15>GPIO-15 (D8)</option>' . CRLF);
	  echo('<option value=16>GPIO-16 (D0)</option></select></td></tr>' . CRLF);
  } // END FUNCTION HTMLconfig
  
  function javaChange() {
	  //echo('alert("sel="+selected);');
	  echo('if (selected==="Zwave_4") { document.getElementById("NewzWave0").style.display = "table-row";document.getElementById("NewzWave1").style.display = "table-row";'.
	       'var a = document.getElementById("ESP_IP").value; document.getElementById("ESP_IP").value = ""; document.getElementById("ESP_IP").focus(); document.getElementById("ESP_IP").value = a;'.
		   '} else { document.getElementById("NewzWave0").style.display = "none";document.getElementById("NewzWave1").style.display = "none";}');
  } // END FUNCTION javaChange


} // END Class

?>