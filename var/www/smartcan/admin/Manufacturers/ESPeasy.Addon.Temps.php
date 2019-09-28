<?php

class ESPeasy_class {

  
  function new() {
	  global $id_sonde;
	  global $Temp_Name;
	  $DHT22_pin = html_get("DHT22_Pin");
	  //echo("New EspEasy, id_sonde=".$id_sonde.", DHT22_pin=".$DHT22_pin);
	
	  $id_sonde  = html_get("ESP_IP");
	  $DHT22_pin = html_get("DHT22_Pin");
		
	  // Send Config Commands to ESP
	  $ip = shell_exec("ifconfig eth0| grep 'inet ' | cut -d: -f2");
	  $ip=substr($ip,0, strpos($ip, " netmask")-1);
	  $ip=substr($ip,(strrpos($ip," ")-strlen($ip)+1));
		
	  // Disable WiFi Status Led (D4), and I2C pins
	  $ESP_cmd  = "http://" . $id_sonde . "/hardware?pled=-1&psda=-1&pscl=-1";
	  $ESP_Call = fopen($ESP_cmd, "r");
	  fclose($ESP_Call);
	  sleep(2);
	  // Configure controller's Webhook
	  $ESP_cmd  = "http://" . $id_sonde . "/controllers?index=1&protocol=8&usedns=0&deleteoldest=1&controllerip=".$ip.
					"&controllerport=80&controllerpublish=/smartcan/webhook/ESPeasy-WebHook.php%3Fname%3D%25sysname%25%26task%3D%25tskname%25%26valuename%3D%25valname%25%26value%3D%25value%25" .
					"&controllerenabled=on";
	  $ESP_Call = fopen($ESP_cmd, "r");
	  fclose($ESP_Call);
	  sleep(2);
	  $ESP_Call = fopen($ESP_cmd, "r");
	  fclose($ESP_Call);
	  sleep(2);
	  // Configures DHT22
	  $ESP_cmd  = "http://" . $id_sonde . "/devices?index=1&edit=1&page=1&TDNUM=5&TDN=".$Temp_Name."&TDE=on&taskdevicepin1=".$DHT22_pin."&plugin_005_dhttype=22&TDSD1=on&TDT=60" .
					"&TDVN1=Temperature&TDF1=%25value%25&TDVD1=2&TDVN2=Humidity&TDF2=%25value%25&TDVD2=2";
	  echo($ESP_cmd."<br>");
	  $ESP_Call = fopen($ESP_cmd, "r");
	  fclose($ESP_Call);
	  sleep(3);
	  $ESP_Call = fopen($ESP_cmd, "r");
	  fclose($ESP_Call);
	  // Reboots the ESP
	  $ESP_cmd  = "http://" . $id_sonde . "/?cmd=reboot";
	  $ESP_Call = fopen($ESP_cmd, "r");
	  fclose($ESP_Call);
	  
	  // Return ID value to save into DB
	  return("ESP_" . $id_sonde);

  }
  
  function HTMLoption() {
	  GLOBAL $msg, $Lang;
	  echo("<option value='NewESPeasy' >" . $msg["TEMPS"]["NewESP"][$Lang] . "</option>" . CRLF);
  } // END FUNCTION HTMLselect
  
  
  function HTMLconfig() {
	  GLOBAL $msg, $Lang;
	  // Determine Local IP Range
	  $ip = shell_exec("ifconfig eth0| grep 'inet ' | cut -d: -f2");
	  $ip=substr($ip,0, strpos($ip, " netmask")-1);
	  $ip=substr($ip,(strrpos($ip," ")-strlen($ip)+1));
	  $ip=substr($ip,0,strrpos($ip,".")+1);
	  
	  echo('<tr id="NewESPeasy0" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF);
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["IPaddress"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><input type="text" name="ESP_IP" id="ESP_IP" value="' . $ip . '"></td></tr>' . CRLF);
	  echo('<tr id="NewESPeasy1" style="display: none;"><td width="20%">&nbsp;</td>' . CRLF);
	  echo('<td width="30%" align="right">' . $msg["TEMPS"]["DHT22GPIO"][$Lang] . '&nbsp;&nbsp;&nbsp;<br><br></td>' . CRLF);
	  echo('<td width="50%"><select name="DHT22_Pin"><option value=0>GPIO-0 (D3)</option><option value=1 disabled>GPIO-1 (D10)</option><option value=2 selected>GPIO-2 (D4)</option>' . CRLF);
	  echo('<option value=3 disabled>GPIO-3 (D9)</option><option value=4 disabled>GPIO-4 (D2)</option><option value=5 disabled>GPIO-5 (D1)</option><option value=9>GPIO-9 (D11) &#9888;</option>' . CRLF);
	  echo('<option value=10>GPIO-10 (D12)</option><option value=12>GPIO-12 (D6)</option><option value=13>GPIO-13 (D7)</option><option value=14>GPIO-14 (D5)</option><option value=15>GPIO-15 (D8)</option>' . CRLF);
	  echo('<option value=16>GPIO-16 (D0)</option></select></td></tr>' . CRLF);
  } // END FUNCTION HTMLconfig
  
  function javaChange() {
	  //echo('alert("sel="+selected);');
	  echo('if (selected==="NewESPeasy") { document.getElementById("NewESPeasy0").style.display = "table-row";document.getElementById("NewESPeasy1").style.display = "table-row";'.
	       'var a = document.getElementById("ESP_IP").value; document.getElementById("ESP_IP").value = ""; document.getElementById("ESP_IP").focus(); document.getElementById("ESP_IP").value = a;'.
		   '} else { document.getElementById("NewESPeasy0").style.display = "none";document.getElementById("NewESPeasy1").style.display = "none";}');
  } // END FUNCTION javaChange


} // END Class

?>