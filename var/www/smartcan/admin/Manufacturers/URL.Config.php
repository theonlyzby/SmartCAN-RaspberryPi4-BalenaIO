<?PHP
// Includes
//include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function ModConfig (Admin Config of the URL Module)
function ModConfig() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.manufacturer.URL.php";

  // Action Requested via Form?  
  $action    = html_postget("action");
  $SubMenu   = html_postget("SubMenu");
  $onURL     = html_postget("onURL");
  $offURL    = html_postget("offURL");
  $invertURL = html_postget("invertURL");

	
  // Action Request?	
  if ($action!="") {
	// Save URLs
	//echo("onURL=$onURL, offURL=$offURL, invertURL=$invertURL<br>".CRLF);
	$sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$onURL."' WHERE `variable` = 'onURL';";
	$query=mysqli_query($DB,$sql);
	if ($onURL=="") {
	  foreach (array("FbURL","FbVar0","FbVal0","FbVar1","FbVal1","FbIntVar","FbIntValue","FbSource") as $value) {
	    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '' WHERE `variable` = '".$value."';";
	    $query=mysqli_query($DB,$sql);
	  } // END FOREACH
	} // END IF
	$sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$offURL."' WHERE `variable` = 'offURL';";
	$query=mysqli_query($DB,$sql);
	$sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$invertURL."' WHERE `variable` = 'invertURL';";
	$query=mysqli_query($DB,$sql);
	
	// Feedback Details (URL + Source)
	if ($onURL) {
	  $start_URL=strpos($onURL, "//")+2;
	  $URL_Len=strpos($onURL, "/", $start_URL)-$start_URL;
	  $var1Pres = 0;
	  if (substr_count(substr($onURL,$start_URL,$URL_Len),"*#--")) { 
	    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '".substr($onURL,$start_URL,$URL_Len)."' WHERE `variable` = 'FbSource';";
		$FbSource = substr($onURL,$start_URL,$URL_Len);
	  } else {
	    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '' WHERE `variable` = 'FbSource';";
		$FbSource = "";
	  } // END IF
	  $query=mysqli_query($DB,$sql);
	  $FbURL = "http://".$_SERVER['HTTP_HOST']."/smartcan/class/URL/URLfeedback.php";
	  $sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$FbURL."' WHERE `variable` = 'FbURL';";
	  $query=mysqli_query($DB,$sql);
	  // Find Arguments in GET URL
	  $URL_Args = explode("&", substr($onURL, strpos($onURL, "?")+1));
	  $i=0; $j=0; $Intensity_Arg=0;
	  $FbIntVar="";$FbIntValue="";$FbVar[0]="";$FbVal[0]="";$FbVar[1]="";$FbVal[1]="";
	  while (isset($URL_Args[$i])) {
	    $URL_element=explode("=",$URL_Args[$i]);
		//echo("0=".$URL_element[0].",1=".$URL_element[1]."<br>");
		if (isset($URL_element[1])) {
		  if ($URL_element[1]=="*#--INTENSITY--#*") { 
		    $Intensity_Arg=1; 
		    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$URL_element[0]."' WHERE `variable` = 'FbIntVar';";
		    $query=mysqli_query($DB,$sql);
		    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$URL_element[1]."' WHERE `variable` = 'FbIntValue';";
	        $query=mysqli_query($DB,$sql);
			$FbIntVar   = $URL_element[0];
			$FbIntValue = $URL_element[1];
		  } else {
		    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$URL_element[0]."' WHERE `variable` = 'FbVar".$j."';";
	        $query=mysqli_query($DB,$sql);
		    $sql = "UPDATE `ha-URLmod-vars` SET `value` = '".$URL_element[1]."' WHERE `variable` = 'FbVal".$j."';";
		    $query=mysqli_query($DB,$sql);
			$FbVar[$j] = $URL_element[0];
			$FbVal[$j] = $URL_element[1];
		    $j++;
		  } // END IF
		} // END IF
		$i++;
	  } // END WHILE
	  if ($j<=1) {
	    while ($j<=1) {
	      $sql = "UPDATE `ha-URLmod-vars` SET `value` = '' WHERE `variable` = 'FbVar".$j."';";
	      $query=mysqli_query($DB,$sql);
		  $sql = "UPDATE `ha-URLmod-vars` SET `value` = '' WHERE `variable` = 'FbVal".$j."';";
		  $query=mysqli_query($DB,$sql);
		  $FbVar[$j] = "";
		  $j++;
	    } // END WHILE
	  } // END IF
	  if ($Intensity_Arg==0) {
	    $sql = "UPDATE `ha-URLmod-vars` SET `value` = 'Value' WHERE `variable` = 'FbIntVar';";
		$query=mysqli_query($DB,$sql);
		$sql = "UPDATE `ha-URLmod-vars` SET `value` = '*#--INTENSITY--#*' WHERE `variable` = 'FbIntValue';";
	    $query=mysqli_query($DB,$sql);
	  } // END IF
	} // END IF
	
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
		$ElementDesc[$j]  = html_postget("ElementDesc_".$i);
		$ElementOne[$j]   = html_postget("ElementOne_".$i);
		$ElementTwo[$j]   = html_postget("ElementTwo_".$i);
		$ElementID[$j]    = html_postget("ElementID_".$i);
		/// Exist => Update? or Create
		if (($ElementID[$j]==0) && ($ElementDesc[$j]!="")) {
		  // Create
		  $sql = "INSERT INTO `ha_element` (`id`, `Manufacturer`, `card_id`, `element_type`, `element_reference`, `element_name`) " .
					"VALUES (NULL, 'URL' ,'".$ElementOne[$j]."', '0x12', '".$ElementTwo[$j]."', '".$ElementDesc[$j]."');";
		  $query=mysqli_query($DB,$sql);
		  $ElementID[$j] = mysqli_insert_id($DB);
		  //echo("Element INSERT (".$ElementID[$j].",i=$i, j=$j): sql=$sql<br>");
		} else {
		  // Update
		  $sql = "UPDATE `ha_element` SET `element_name`='".$ElementDesc[$j]."', `card_id` = '".$ElementOne[$j]."', `element_reference`='".$ElementTwo[$j]."'" .
					" WHERE `id` = '".$ElementID[$j]."';";
		  //echo("Update Element ID = ".$ElementID[$j]."<br>SQL=$sql<br>".CRLF);
		  $query=mysqli_query($DB,$sql);
		} // END IF

	  } // END IF
	  $i++;$j++;
	} // END While
  } // END IF
  
  // No action Request => Parse DBs
  $sql = "SELECT * FROM `ha-URLmod-vars`;";
  $query=mysqli_query($DB,$sql);
  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    if ($row['variable']=="onURL")      { $onURL      = $row['value'];}
    if ($row['variable']=="offURL")     { $offURL     = $row['value'];}
    if ($row['variable']=="invertURL")  { $invertURL  = $row['value'];}
    if ($row['variable']=="FbURL")      { $FbURL      = $row['value'];}
    if ($row['variable']=="FbVar0")     { $FbVar[0]   = $row['value'];}
    if ($row['variable']=="FbVal0")     { $FbVal[0]   = $row['value'];}
    if ($row['variable']=="FbVar1")     { $FbVar[1]   = $row['value'];}
    if ($row['variable']=="FbVal1")     { $FbVal[1]   = $row['value'];}
    if ($row['variable']=="FbIntVar")   { $FbIntVar   = $row['value'];}
    if ($row['variable']=="FbIntValue") { $FbIntValue = $row['value'];}
    if ($row['variable']=="FbSource")   { $FbSource   = $row['value'];}
  } // END WHILE
  $sql = "SELECT * FROM `ha_element` WHERE `Manufacturer`='URL' ORDER BY id;";
  //echo("sql=$sql<br>");
  $query=mysqli_query($DB,$sql);
  $i=0;
  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
	$ElementID[$i]   = $row['id'];
	$ElementDesc[$i] = $row['element_name'];
	$ElementOne[$i]  = $row['card_id'];
	$ElementTwo[$i]  = $row['element_reference'];
	//echo("Element Content from DB: id=".$ElementID[$i].", Desc=".$ElementDesc[$i].", V1=".$ElementOne[$i].", V2=".$ElementTwo[$i]."<br>");
	$i++;
  } // END WHILE
  if (($i==0) || ($action=="AddElement")) { $ElementID[$i]=0; $ElementDesc[$i]=""; $ElementOne[$i]=""; $ElementTwo[$i]=""; }


  // Start Build Page ...
  echo("<h2 class='title'>".$msg["URL"]["PageTitle"][$Lang]."</h2>");
  // Open Form
  echo("<form name='ChangeVariables' id='ChangeVariables' action='".$_SERVER['PHP_SELF']."?page=Modules&SubMenu=".$SubMenu."' method='post'>" . CRLF);
  
  // URL Management
  echo("<div class='post_info'>".$msg["URL"]["MngUrlTitle"][$Lang]."&nbsp;</div>" . CRLF);

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
    alert("Description Vide!");
	return;
  }
}
</script>

  <?PHP
  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  echo("<table>" . CRLF);
  echo("<tr><td colspan=2 align=middle><b>".$msg["URL"]["ComposeURL"][$Lang]."</b></td></tr>" . CRLF);

  
  // Light ON
  echo("<tr><td>".$msg["URL"]["TurnOnURL"][$Lang]."</b>&nbsp;</td><td><input type='text' name='onURL' id='onURL' value='$onURL' size=50/> ".
		"<br>&nbsp;&nbsp;<input type='button' value='*#--ONE--#*' id='button' onclick='javascript:ClickToAdd(\"onURL\", \"*#--ONE--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--TWO--#*' id='button' onclick='javascript:ClickToAdd(\"onURL\", \"*#--TWO--#*\");' />" .
		"&nbsp;&nbsp;<input type='button' value='*#--INTENSITY--#*' id='button' onclick='javascript:ClickToAdd(\"onURL\", \"*#--INTENSITY--#*\");' />" .
		"&nbsp;&nbsp;<input type='button' value='*#--DELAY--#*' id='button' onclick='javascript:ClickToAdd(\"onURL\", \"*#--DELAY--#*\");' /></td></tr>" . CRLF);
  echo("<tr><td>&nbsp;</td><td>".$msg["URL"]["TurnOnDesc1"][$Lang]."</td></tr>"); 
  echo("<tr><td>&nbsp;</td><td>".$msg["URL"]["TurnOnDesc2"][$Lang]."</td></tr>");
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
  
  // Light OFF
  echo("<tr><td>".$msg["URL"]["TurnOffURL"][$Lang]."</b>&nbsp;&nbsp;&nbsp;</td><td><input type='text' name='offURL' id='offURL' value='$offURL' size=50/> ".
		"<br>&nbsp;&nbsp;<input type='button' value='*#--ONE--#*' id='button' onclick='javascript:ClickToAdd(\"offURL\", \"*#--ONE--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--TWO--#*' id='button' onclick='javascript:ClickToAdd(\"offURL\", \"*#--TWO--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--INTENSITY--#*' id='button' onclick='javascript:ClickToAdd(\"offURL\", \"*#--INTENSITY--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--DELAY--#*' id='button' onclick='javascript:ClickToAdd(\"offURL\", \"*#--DELAY--#*\");' /></td></tr>" . CRLF);
  echo("<tr><td>&nbsp;</td><td>".$msg["URL"]["TurnOffDesc1"][$Lang]."</td></tr>"); 
  echo("<tr><td>&nbsp;</td><td>".$msg["URL"]["TurnOffDesc2"][$Lang]."</td></tr>");
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
    
  // Light Invert
  echo("<tr><td>".$msg["URL"]["InvertURL"][$Lang]."</b>&nbsp;&nbsp;&nbsp;</td><td><input type='text' name='invertURL' id='invertURL' value='$invertURL' size=50/> ".
		"<br>&nbsp;&nbsp;<input type='button' value='*#--ONE--#*' id='button' onclick='javascript:ClickToAdd(\"invertURL\", \"*#--ONE--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--TWO--#*' id='button' onclick='javascript:ClickToAdd(\"invertURL\", \"*#--TWO--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--INTENSITY--#*' id='button' onclick='javascript:ClickToAdd(\"invertURL\", \"*#--INTENSITY--#*\");' />".
		"&nbsp;&nbsp;<input type='button' value='*#--DELAY--#*' id='button' onclick='javascript:ClickToAdd(\"invertURL\", \"*#--DELAY--#*\");' /></td></tr>" . CRLF);
  echo("<tr><td>&nbsp;</td><td>".$msg["URL"]["InvertDesc1"][$Lang]."</td></tr>"); 
  echo("<tr><td>&nbsp;</td><td>".$msg["URL"]["InvertDesc2"][$Lang]."</td></tr>");
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"); 
   
  // Feedback details
  if ($onURL!="") {
  
    echo("<tr><td><b>".$msg["URL"]["Feedback"][$Lang]."</b></td><td>".$FbURL);
	$URL_Sep=array("?","&","&","&");$j=0;
	if ($FbVar[0]!="") { echo($URL_Sep[$j].$FbVar[0]."=".$FbVal[0]);   $j++; }
	if ($FbVar[1]!="") { echo($URL_Sep[$j].$FbVar[1]."=".$FbVal[1]);   $j++; }
	if ($FbIntVar!="") { echo($URL_Sep[$j].$FbIntVar."=".$FbIntValue); $j++; }
	echo("</td></tr>"); 
	if ($FbSource!="") { 
	  echo("<tr><td>".$msg["URL"]["SourceAddr"][$Lang]."</td><td>".$FbSource."</td></tr>"); 
	} // END IF
    
  } // END IF
  
  // Submit
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"SaveURLs\")'><img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a></td></tr>");
  
  echo("</table>" . CRLF);
  
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

   ////////////
  // Outputs //
  ////////////
  echo("<div class='post_info'>".$msg["URL"]["MngOutputs"][$Lang]."</div>" . CRLF);
  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF); 
	
  // Table, to Add or Modify Elements
  echo("<table>" . CRLF);
  echo("<tr><td width='5%'>&nbsp;&nbsp;&nbsp;&nbsp;</td>" . CRLF);
  echo("<td width='30%'>".$msg["MAIN"]["Description"][$Lang]."</td>" . CRLF);
  echo("<td width='30%'>".$msg["URL"]["ValueONE"][$Lang]."</td>" . CRLF);
  echo("<td width='30%'>".$msg["URL"]["ValueTWO"][$Lang]."</td>" . CRLF);
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
    // Variable 1
    echo("<td><input type=\"text\" name=\"ElementOne_$i\" id=\"ElementOne_$i\"  value=\"".$ElementOne[$i]."\" size='15'></td>" . CRLF);
    // Variable 2
    echo("<td><input type=\"text\" name=\"ElementTwo_$i\" id=\"ElementTwo_$i\" value=\"".$ElementTwo[$i]."\" size='15'></td>" . CRLF);
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
  echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('ElementDesc_".($i-1)."','SaveElements');\">Sauver</a></p></span>" . CRLF);
  echo("  <div class=\"clear\"></div>" . CRLF);
  echo("</div>" . CRLF);
  echo("</td>" . CRLF);
  echo("<td>&nbsp;</td>" . CRLF);
  echo("<td>&nbsp;</td></tr>" . CRLF);
  echo("</table><br>" . CRLF);

  	
  echo("</form>" . CRLF);
  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);
?>

    <body>
        <div id="data"></div>
    </body>

<div id="rss-3" class="block widget_rss">
<ul>
<?PHP

  echo("</ul>" . CRLF);
  echo("</div>" . CRLF);
?>


 
</div>
</div>

<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'>&nbsp;" . CRLF);

  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  

?>
  </ul></div>


<div class="postcontent">

	
		<div class="clear"></div>
	</div>



  <input type="hidden" name="action" value="" />    

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