<?PHP

// Main Function ModConfig (Admin Config of the zWave Module)
function ModConfig() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.manufacturer.zWave.php";

  // Action Requested via Form?  
  $action    = html_postget("action");
  $SubMenu   = html_postget("SubMenu");
  $zWavePath = html_postget("zWavePath");
  $setTemp = html_postget("setTemp");

  // Variables
  $myFile  = "/data/www/smartcan/bin/python/zWaveConfig.py";
	
  // Action Request?	
  if ($action!="") {
	//echo("Action=".$action);
	if ($action=="ResetEmpty") {
	  // Delete Zwave config files
	  $output = shell_exec("sudo /bin/rm /data/www/smartcan/bin/python/OZW_Log.log");
	  $output = shell_exec("sudo /bin/rm /data/www/smartcan/bin/python/*.xml");
	  $output = shell_exec("sudo /bin/rm /data/www/smartcan/bin/python/pyozw.sqlite");

	} // END IF
	if ($action=="ScanNetwork") {
	  // Scan Zwave network
	  $output = shell_exec("sudo /usr/bin/python3 /data/www/smartcan/bin/python/ScanZwave.py");
	} // END IF
	if ($action=="SavezWaves") {
	  // Set zWave Delay
	  $sql = "SELECT COUNT(*) AS County FROM `chauffage_clef` WHERE `clef`='zWaveDelay';";
	  $query = mysqli_query($DB,$sql);
	  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
	  if ($row["County"]==0) {
	    $sql = "INSERT INTO `chauffage_clef` (`id`, `clef`, `ZoneNber`, `valeur`) VALUES (NULL, 'zWaveDelay', '0', '".$setTemp."');";
	  } else {
	    $sql = "UPDATE `chauffage_clef` SET `valeur` = '".$setTemp."' WHERE `clef` = 'zWaveDelay';";
	  }
	  $query = mysqli_query($DB,$sql);
	  // Read and change zWave Path
	  $output = shell_exec("sudo /bin/touch ".$myFile.".tmp");
	  $output = shell_exec("sudo /bin/chmod 777 ".$myFile.".tmp");
	  $reading   = fopen($myFile,'r');
	  $writing   = fopen($myFile.".tmp","w");
	  $replaced  = false;
	  $OldPassdw = mysqli_PWD;
	  // Parse config file
	  while(!feof($reading)) {
	    $line = fgets($reading,4096);
		if (substr_count($line,"var =")>0) {
		  // Change Password in file
		  //echo("MySQL, change Password to $ROOTPasswd<br>");
		  fwrite($writing,'   var = "'.$zWavePath.'"'.chr(13).chr(10));
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
        $output = shell_exec('sudo /bin/mv '.$myFile.'.tmp '.$myFile);
	  } // END IF
	} // END IF
  } // END IF

  // USB Path
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'var = "')!==false)  { $USBPath = substr($line, 10, strrpos($line,'"')-10); }
  } // END WHILE
  fclose($reading);
  // Test USB Path
  $USBPathOK = is_file($USBPath);  


  // Start Build Page ...
  echo("<h2 class='title'>".$msg["zWave"]["PageTitle"][$Lang]."</h2>");
  // Open Form
  echo("<form name='ChangeVariables' id='ChangeVariables' action='".$_SERVER['PHP_SELF']."?page=Modules&SubMenu=".$SubMenu."' method='post'>" . CRLF);
  
  // zWave Management
  echo("<div class='post_info'>".$msg["zWave"]["MngzWaveTitle"][$Lang]."&nbsp;</div>" . CRLF);
  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
  echo("<style>img {position:relative;}</style>" .CRLF);
  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  echo("<table>" . CRLF);
  
  // zWave Stick Path
  echo("<tr><td>".$msg["zWave"]["zWavePath"][$Lang]."</b>&nbsp;</td><td><input type='text' name='zWavePath' id='zWavePath' value='".$USBPath."' size=20/> ");
  if ($USBPathOK) { echo("<img src='../www/images/check.png' width='20px' heigth='20px' />"); }
             else { echo("<img src='./images/caution.png' width='20px' heigth='20px' />"); }
  echo("</td></tr>" . CRLF);
  
  // Delay between 2 Zwave poll (Set temp every X minutes)
  // zWave Polling Time?
  $sql = "SELECT * FROM chauffage_clef WHERE `clef`='zWaveDelay';";
  $query = mysqli_query($DB,$sql);
  if (empty($query)) {
    $setTemp = "10";
    $sql = "INSERT INTO `chauffage_clef` (`id`, `clef`, `ZoneNber`, `valeur`) VALUES (NULL, 'zWaveDelay', '0', '".$setTemp."');";
    $query = mysqli_query($DB,$sql);
  } else {
    //echo("OK zWaveDelay");
    $row = mysqli_fetch_array($query, MYSQLI_BOTH);
    $setTemp = $row['valeur'];
  } // END IF

  echo("<tr><td>".$msg["zWave"]["setTemp"][$Lang]."</b>&nbsp;</td><td><input type='text' length='3' name='setTemp' id='setTemp' value='".$setTemp."' size=20/> ");
  echo("&nbsp;".$msg["zWave"]["setTempMin"][$Lang]."</td></tr>" . CRLF);

    // Submit
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"SavezWaves\")'><img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a></td></tr>");
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");  
  
  // Scan Network for new node

  echo("<tr><td style='vertical-align: middle'>".$msg["zWave"]["ScanNetwork"][$Lang]."</td><td align='center'>&nbsp;&nbsp;<a href='javascript:submitform(\"ScanNetwork\")'><img src='./images/scan-net.png' width='70px' heigth='60px' /></a></td></tr>"); 
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");    
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
	
  // Reset / Empty Config

  echo("<tr><td style='vertical-align: middle'>".$msg["zWave"]["ResetEmpty"][$Lang]."</td><td align='center'>&nbsp;&nbsp;<a href='javascript:submitform(\"ResetEmpty\")'><img src='./images/Reset-btn.jpg' width='70px' heigth='60px' /></a></td></tr>"); 
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");
  echo("</table>" . CRLF);
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

  
  // Find zWave config file
  $file = glob("/data/www/smartcan/bin/python/zwcfg_*.xml");
  //echo("File=".$file[0]. "<br>". CRLF);
  
  if (isset($file)) {
    // List zWave Nodes 
    //
    // Scans Zwave config XML
    $NberNodes = 0;
    $handle = @fopen($file[0], "r");
    if ($handle) {
      while (!feof($handle)) {
        $buffer = fgets($handle);
        if (substr_count($buffer, '<Node id="')!=0) {
			$ZNodeID = substr($buffer,strpos($buffer,'<Node id="')+10,strpos($buffer,'" name="')-strpos($buffer,'<Node id="')-10);
		    $Ztype = substr($buffer,strpos($buffer,'" type="')+8,strpos($buffer,'" listening="')-strpos($buffer,'" type="')-8);
			$buffer = fgets($handle);
			$ManuName = substr($buffer,strpos($buffer,'" name="')+8,strpos($buffer,'">')-strpos($buffer,'" name="')-8);
			$buffer = fgets($handle);
			$ProdType = substr($buffer,strpos($buffer,'<Product type="')+15,strpos($buffer,'" id="')-strpos($buffer,'<Product type="')-15);
			$ProdName = substr($buffer,strpos($buffer,'" name="')+8,strpos($buffer,'" />')-strpos($buffer,'" name="')-8);
			
			if ($ProdType!="0") {
			  $NberNodes+=1;
			  $LzNodeID[$NberNodes]=$ZNodeID; $LzType[$NberNodes]=$Ztype; $LmanuName[$NberNodes]=$ManuName; $LprodType[$NberNodes]=$ProdType; $LprodName[$NberNodes]=$ProdName;
              //echo( $ZNodeID . "-" . $Ztype . "-" .$ManuName . "-" . $ProdType . "-" . $ProdName  ."<br>");
			}
		} // ENDIF
      } // END WHILE
      fclose($handle);
    } // ENDIF
    if ($NberNodes!=0) {
      echo("<div class='post_info'>".$msg["zWave"]["ListzWaveTitle"][$Lang]."&nbsp;</div>" . CRLF);
      echo("	<div class='postcontent' name='plan' " .
          "style='" .
          " width: 550px; margin-left: 50px;'>" . CRLF);
      echo("<table>" . CRLF);
	  echo("<tr><td>Node ID</td><td>Type</td><td>Device Name</td></td>");
	  for ($x = 1; $x <= $NberNodes; $x++) {
		echo("<tr><td align='center'>" . $LzNodeID[$x] . "</td><td>" . $LzType[$x] . "</td><td>" .$LmanuName[$x] . " " . $LprodName[$x]  ."</td></td>");
		if ($LprodType[$x]=="3") {
		  $sql = "SELECT * FROM `ha_element` WHERE `Manufacturer`='zWave' AND `card_id`='".str_pad($LzNodeID[$x], 4, "0", STR_PAD_LEFT)."' AND `element_type`='0x31';";
		  $query = mysqli_query($DB,$sql);
		  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
		  if (!$row['id']) {
		    $sql2 = "INSERT INTO `".TABLE_ELEMENTS."` (`id`, `Manufacturer`, `card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL, 'zWave', '".str_pad($LzNodeID[$x], 4, "0", STR_PAD_LEFT)."', '0x31', '', '" . substr($LprodName[$x],0,40) ."');";
		  } else {
		    $sql2 = "UPDATE `ha_element` SET `Manufacturer` = 'zWave', `card_id` = '".str_pad($LzNodeID[$x], 4, "0", STR_PAD_LEFT)."', `element_type` = '0x31', `element_name` = '" . substr($LprodName[$x],0,40) ."' WHERE `id` = ".$row['id'].";";
		  }
		  $query = mysqli_query($DB,$sql2);
		} // END IF
	  } // END FOR

      echo("</table>" . CRLF);
      echo("</div>" . CRLF);
    } // END IF
  } // END IF

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
  

  echo("" . CRLF);
  echo("  </ul></div>" . CRLF);
  echo("<div class='postcontent'>" . CRLF);
  echo("		<div class='clear'></div>" . CRLF);
  echo("	</div>" . CRLF);
  echo("</div>" . CRLF);
  # Javascript
  echo("<script type='text/javascript'>" . CRLF);
  echo("function submitform(action) {" . CRLF);
  echo("  //alert('submit + Action='+action);" . CRLF);
  echo("  if (action=='ResetEmpty') {" . CRLF);
  echo("     var r = confirm('" . $msg["zWave"]["SureReset"][$Lang] . "');" . CRLF);
  echo("  } else {" . CRLF);
  echo("	  var r = true;" . CRLF);
  echo("  }" . CRLF);
  echo("  if (r==true) {" . CRLF);
  echo("    document.ChangeVariables.action.value = action;" . CRLF);
  echo("    document.ChangeVariables.submit();" . CRLF);
  echo("  }" . CRLF);
  echo("}" . CRLF);
  echo("</script>" . CRLF);

  mysqli_close($DB);
} // End of Function SysMap