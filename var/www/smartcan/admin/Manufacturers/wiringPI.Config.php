<?PHP
// Main Function ModConfig (Admin COnfig of the Module
function ModConfig() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  global $Linux_Mode;
  
  // Includes
  include_once "./lang/admin.manufacturer.wiringPI.php";
  
  $SubMenu   = html_postget("SubMenu");
  // Action Requested via Form?  
  $action    = html_postget("action");
  //echo("Action = $action<br>");
  
  //
  // First Admin Use?
  //
  $sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
  $query            = mysqli_query($DB,$sql);
  $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
  $First_Use_Admin = $row['value'];
  // First Admin Use Done ??? ... Next
  if (($First_Use_Admin=="2") && (($action=="Pass_wiringPI") || ($action=="SaveElements"))) {
    // YES ... Increase Counter
	$sql = "UPDATE `".TABLE_VARIABLES."` SET `value` = '3' WHERE `variable` = 'first_use_admin';";
	$query = mysqli_query($DB,$sql);
	$First_Use_Admin = "3";
	echo("<table><tr><td width=\"40%\">&nbsp;</td><td>".CRLF);
	echo("<br>&nbsp;<br>".$msg["MAIN"]["ChangeSaved"][$Lang]."<br>".CRLF);
	echo("<span class=\"readmore_b\"><a class=\"readmore\" href=\"index.php?page=Variables\" style=\"color: white; align=middle;\" ;\">".$msg["MAIN"]["next"][$Lang].
			"</a></span><br>".CRLF);
	echo("<br><br>".CRLF);
	echo("</td><td width=\"40%\">&nbsp;</td></tr></table>".CRLF);
  } // END IF
  //

  // Variables from configs
  // 1 Wire Config (Raspberry Config file)
  $myFile  = "/boot/config.txt";
  $reading = fopen($myFile,'r');
  while(!feof($reading)) {
	$line = fgets($reading,4096);
	if (strpos($line,'dtoverlay=w1-gpio-pullup,gpiopin=')!==false)  { $GPIOpin = substr($line, 33,-1); }
  } // END WHILE
  fclose($reading);
  $Rasp_PIN = array(2=>'3',3=>'5',4=>'7',14=>'8',15=>'10',17=>'11',18=>'12',27=>'13',22=>'15',23=>'16',24=>'18',10=>'19',9=>'12',25=>'21',11=>'23',8=>'24',7=>'16');
  //echo("GPIO PIN =". $Rasp_PIN[$GPIOpin] . "=-<br>" . CRLF);
  // CAN Int?
  $retstatus = exec('ifconfig can0', $Output, $retval);
  if ($retval==0) { $CAN_Int="Y"; } else { $CAN_Int="N"; }
  //echo("CAN Int? -=$CAN_Int=-<br>" . CRLF);
  // Build GPIO Table
  exec('gpio readall', $readall);
  $j=0;
  for ($i = 3; $i < (count($readall) - 1); $i++) {
	$row = explode('|', $readall[$i]);
	$pin=0;  if (isset($row[1])) {  $pin  = intval(trim($row[1]));  }
	$pin2=0; if (isset($row[12])) { $pin2 = intval(trim($row[12])); }
	if (($pin!=0) && (trim($row[6])<50) && (substr(trim($row[3]),0,1)=="G") && (trim($row[6])!=$Rasp_PIN[$GPIOpin]) && !(trim($row[6])==22 && $CAN_Int=="Y")) { 
	  $j++;
	  $GPIO_Name[$j]     = trim($row[3]);
	  $GPIO_Rasp_Pin[$j] = trim($row[6]);
	  //echo $GPIO_Rasp_Pin[$j].'-';
	  //echo $GPIO_Name[$j].'-';
	  //echo trim($row[4]).'-';
	  //echo trim($row[5]).'<br>';
	} // END IF
	if (($pin2!=0) && (trim($row[8])<50) && (substr(trim($row[11]),0,1)=="G") && (trim($row[8])!=$Rasp_PIN[$GPIOpin]) && !(trim($row[8])==22 && $CAN_Int=="Y")) {
	  $j++;
	  $GPIO_Name[$j]     = trim($row[11]);
	  $GPIO_Rasp_Pin[$j] = trim($row[8]);
	  //echo $GPIO_Rasp_Pin[$j].'-';
	  //echo $GPIO_Name[$j].'-';
	  //echo trim($row[10]).'-';
	  //echo trim($row[9]).'<br>';
	} // END IF
  } // END FOR
  //echo("Number of GPIOs (j)=$j<br>");
  
  // Action Request?	
  if ($action!="") {
    $i=0;$j=0;
	while (((html_postget("ElementDesc_".$i)!="") || $i==0) && ($action!="")) {
	  if (($action=="RemoveElement") && (html_postget("ElementID_".$i)==html_postget("ElID"))){ 
	    // Delete Element
		$idE = html_postget("ElID");
	    $sql = "DELETE FROM `ha_element` WHERE `id` = ".$idE.";";
	    //echo($action.", ElID to Delete=$idE, SQL=$sql<br>");
		$query=mysqli_query($DB,$sql);
		$j--;
	  } else {
	    // Grap from POST and update Elements
		$ElementDesc[$j] = html_postget("ElementDesc_".$i);
		$Element[$j]     = html_postget("Element_".$i);
		$Mode[$j]        = html_postget("Mode_".$i); 
		$Trigger[$j]     = html_postget("Trigger_".$i);
		// Modifies mode
		system("gpio -1 mode " . $Element[$j] . " " . $Mode[$j]);
		//echo("gpio -1 mode " . $Element[$j] . " " . $Mode[$j]);
		if ($Mode[$j]=="OUT") {$Mode[$j]="0x12";} if ($Mode[$j]=="IN") {$Mode[$j]="0x22";}
		$ElementID[$j]   = html_postget("ElementID_".$i);
		/// Exist => Update? or Create
		if (($ElementID[$j]==0) && ($ElementDesc[$j]!="")) {
		  // Create
		  $sql = "INSERT INTO `ha_element` (`id`, `Manufacturer`, `card_id`, `element_type`, `element_reference`, `element_name`) " .
					"VALUES (NULL, 'wiringPI' ,'".$Trigger[$j]."', '".$Mode[$j]."', '".$Element[$j]."', '".$ElementDesc[$j]."');";
		  $query=mysqli_query($DB,$sql);
		  $ElementID[$j] = mysqli_insert_id($DB);
		  //echo("Element INSERT (".$ElementID[$j].",i=$i, j=$j): sql=$sql<br>");
		} else {
		  // Update lumieres table
		  $sql = "SELECT * FROM `ha_element` WHERE `id` = '".$ElementID[$j]."';";
		  $query=mysqli_query($DB,$sql);
		  if ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
		    $sql = "UPDATE `lumieres` SET `sortie` = '".$Element[$j]."' WHERE `sortie`='".$row['element_reference']."' AND `Manufacturer`='WiringPI' AND `carte` = '".$Trigger[$j]."';";
			$query=mysqli_query($DB,$sql);
		  } // END IF
		  // Update ha_element table
		  $sql = "UPDATE `ha_element` SET `element_name`='".$ElementDesc[$j]."', `card_id` = '".$Trigger[$j]."', `element_reference`='".$Element[$j]."'" .
					", `element_type`='".$Mode[$j]."' WHERE `id` = '".$ElementID[$j]."';";
		  //echo("Update Element ID = ".$ElementID[$j]."<br>SQL=$sql<br>".CRLF);
		  $query=mysqli_query($DB,$sql);
		} // END IF
	  } // END IF
	  $i++;$j++;
	} // END While

  } // END IF
  
  // No action Request => Parse DBs
  $sql = "SELECT * FROM `ha_element` WHERE `Manufacturer`='wiringPI' ORDER BY id;";
  //echo("sql=$sql<br>");
  $query=mysqli_query($DB,$sql);
  $i=0;
  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
	$ElementID[$i]   = $row['id'];
	$ElementDesc[$i] = $row['element_name'];
	$Element[$i]     = $row['element_reference'];
	$Trigger[$i]     = $row['card_id'];
	$Mode[$i]        = $row['element_type']; if ($Mode[$i]=="0x12") {$Mode[$i]="OUT";} if ($Mode[$i]=="0x22") {$Mode[$i]="IN";}
	//echo("Element Content from DB: id=".$ElementID[$i].", Desc=".$ElementDesc[$i].", RaspPIN=".$Element[$i]."<br>");
	$i++;
  } // END WHILE
  if (($i==0) || ($action=="AddElement")) { $ElementID[$i]=0; $ElementDesc[$i]=""; $Element[$i]=""; $Trigger[$i]="HIGH"; $Mode[$i]="OUT";}


  // Start Build Page ...
  echo("<h2 class='title'>".$msg["URL"]["PageTitle"][$Lang]."</h2>");
  // Open Form
  echo("<form name='ChangeVariables' id='ChangeVariables' action='".$_SERVER['PHP_SELF']."?page=Modules&SubMenu=".$SubMenu."' method='post'>" . CRLF);
  
  // URL Management
  echo("<div class='post_info'>".$msg["URL"]["MngGPIOTitle"][$Lang]."&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

<script type="text/javascript">
function ClickToAdd(areaId,text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
        "ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}

function CheckSubmitform(field,action,val) {
  var ll = document.getElementById(field).value;
  document.getElementById('ElID').value=val;
  if ((ll.length>=1) || (action=="RemoveElement")) {
    submitform(action);
  } else {
    alert("Empty Description!");
	return;
  }
}
</script>

  <?PHP
  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  // Table, to Add or Modify Elements
  echo("<table>" . CRLF);
  echo("<tr><td width='5%'>&nbsp;&nbsp;&nbsp;&nbsp;</td>" . CRLF);
  echo("<td width='30%'>".$msg["MAIN"]["Description"][$Lang]."</td>" . CRLF);
  echo("<td width='30%'>".$msg["URL"]["RaspPin"][$Lang]."</td>" . CRLF);
  echo("<td width='15%'>".$msg["URL"]["Mode"][$Lang]."</td>" . CRLF);
  echo("<td width='15%'>".$msg["URL"]["Trigger"][$Lang]."</td>" . CRLF);
  echo("<td width='5%'>&nbsp;</td></tr>" . CRLF);
  $i=0; 
  // When Creating a new Element
  echo("<input type='hidden' id='ElID' name='ElID' value=''>". CRLF);
  while ((isset($ElementDesc[$i]))) {
    echo("<tr>" . CRLF);
    echo("<td>&nbsp;</td>" . CRLF);
    //if ($action=="") {$ElementID[$i]=0;} // END IF
	echo("<input type='hidden' id='ElementID_$i' name='ElementID_$i' value='".$ElementID[$i]."'>". CRLF);
    // Element Description
    echo("<td><input type=\"text\" name=\"ElementDesc_$i\" id=\"ElementDesc_$i\" value='".$ElementDesc[$i]."' size='15'></td>" . CRLF);
    // Raspberry Pin #
    echo("<td><select name=\"Element_$i\" id=\"Element_$i\">" . CRLF);
	$j=1;
	while (isset($GPIO_Name[$j])) {
	  if ((!in_array($GPIO_Rasp_Pin[$j], $Element)) || ($GPIO_Rasp_Pin[$j]==$Element[$i])) {
	    echo("<option ");
	    if ($GPIO_Rasp_Pin[$j]==$Element[$i]) { echo("selected "); }
	    echo("value='".$GPIO_Rasp_Pin[$j]."'>".$GPIO_Rasp_Pin[$j]." (".$GPIO_Name[$j].")</option>" . CRLF);
	  } // END IF
	  $j++;
	}
	echo("</select>" . CRLF);
	// Mode
	echo("<td><select name=\"Mode_$i\" id=\"Mode_$i\">" . CRLF);
	echo("<option value='OUT'>".$msg["URL"]["Output"][$Lang]."</option>" . CRLF);
	echo("<option "); if ($Mode[$i]=="IN") {echo("selected ");} echo("value='IN'>".$msg["URL"]["Input"][$Lang]."</option>" . CRLF);
	echo("</select>" . CRLF);
	// Trigger
	echo("<td><select name=\"Trigger_$i\" id=\"Trigger_$i\">" . CRLF);
	echo("<option value='HIGH'>".$msg["URL"]["HIGHTrigger"][$Lang]."</option>" . CRLF);
	echo("<option "); if ($Trigger[$i]=="low") {echo("selected ");} echo("value='low'>".$msg["URL"]["lowTrigger"][$Lang]."</option>" . CRLF);
	echo("</select>" . CRLF);
    // Drop?
	//if ($ElementDesc[$i]!="") {
      echo("<td><a href='javascript:void();' onClick=\"CheckSubmitform('ElementDesc_$i','RemoveElement',".$ElementID[$i].");\"><img src='./images/drop.png'/></a></td>" . CRLF);
      echo("</tr>" . CRLF);
	//} // END IF
    $i++;
  } // END WHILE
  // Add?
  if ((isset($ElementDesc[$i-1])) || ($action=="")) {
    echo("<tr><td>&nbsp;</td>" . CRLF);
    echo("<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>" . CRLF);
    echo("<td><a href=\"javascript:void(1);\" onClick=\"CheckSubmitform('ElementDesc_".($i-1)."','AddElement');\"><img src='./images/add.png'/></a></td>" . CRLF);
    echo("</tr>" . CRLF);
  } // END IF
  echo("<tr><td>&nbsp;</td>" . CRLF);
  echo("<td><div class=\"postcontent\">" . CRLF);
  echo("  <span class=\"readmore_b\"><p>" . CRLF);
  echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('ElementDesc_".($i-1)."','SaveElements');\">".$msg["MAIN"]["Save"][$Lang]."</a></p></span>" . CRLF);
  echo("  <div class=\"clear\"></div>" . CRLF);
  echo("</div>" . CRLF);
  echo("</td>" . CRLF);
  echo("<td>&nbsp;</td>" . CRLF);
  echo("<td>&nbsp;</td></tr>" . CRLF);
  echo("</table><br>" . CRLF);
  
  // Submit

  echo("</table></form>" . CRLF);
  
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);


?>

    <body>
        <div id="data"></div>
    </body>

<div id="rss-3" class="block widget_rss">
<ul>
<?PHP

  echo($msg["URL"]["Info1"][$Lang]."<br>");
  echo($msg["URL"]["Info2"][$Lang]."2</i>");
  
  echo("</ul>" . CRLF);
  echo("</div>" . CRLF);
?>


 
</div>
</div>

<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'>&nbsp;" . CRLF);

if ($First_Use_Admin=="2") {
  echo("<br><br><br><br><br><p size=2>".$msg["URL"]["NoWiringPI"][$Lang]."</p><br><span class=\"readmore_b\"><a class=\"readmore\" href=\"javascript:void(1);\" style=\"color: white; align=middle;\" onClick='submitform(\"Pass_wiringPI\",0);';\">".
		$msg["MAIN"]["Pass"][$Lang]."</a></span><br>");
}// END IF

  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  

?>
  </ul></div>


<div class="postcontent">

	
		<div class="clear"></div>
	</div> 
</div>
<script type="text/javascript">
function submitform(action) {
  //alert("submit + Action="+action);
  document.ChangeVariables.action.value = action;
  document.ChangeVariables.submit();
}
</script>


<?php
  mysqli_close($DB);
} // End of Function SysMap