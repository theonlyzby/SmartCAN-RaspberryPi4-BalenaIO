<?PHP
// Includes
//include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function Surveillance 
function Surveillance() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.Surveillance.php";

  // Action Requested via Form?  
  $action    = html_postget("action");
  // Action Request?	
  if ($action!="") {
	// Save Surveillance URLs

	$i=0;$j=0;
	while (((html_postget("CameraName_".$i)!="") || $i==0) && ($action!="")) {
	  //echo($action.", CamID to Delete=".html_postget("CamID").", CameraID_$i=".html_postget("CameraID_".$i)."<br>");
	  if (($action=="RemoveCamera") && (html_postget("CameraID_".$i)==html_postget("CamID"))){ 
	    // Delete Camera
		$idE = html_postget("CamID");
	    $sql = "DELETE FROM `ha_cameras` WHERE `id` = ".$idE.";";
	    //echo($action.", CamID to Delete=$idE, SQL=$sql<br>");
		$query=mysqli_query($DB,$sql);
		$j--;
	  } else {
	    // Grap from POST and update Cameras
		$CameraID[$j]             = html_postget("CameraID_".$i);
		$CameraName[$j]           = html_postget("CameraName_".$i);
		$CameraURL[$j]            = html_postget("CameraURL_".$i);
		$CameraAuthentication[$j] = html_postget("CameraAuthentication_".$i);
									if (strpos($CameraAuthentication[$j],":")) { $CameraAuthentication[$j] = base64_encode($CameraAuthentication[$j]); }
		$RestrictUsers[$j]        = implode("",html_postget("RestrictUsers_".$i));
		$StreamType[$j]           = html_postget("StreamType_".$i);
		$CameraProfile[$j]        = html_postget("CameraProfile_".$i);
		$CameraPTZup[$j]          = html_postget("CameraPTZup_".$i);
		$CameraPTZdown[$j]        = html_postget("CameraPTZdown_".$i);
		$CameraPTZleft[$j]        = html_postget("CameraPTZleft_".$i);
		$CameraPTZright[$j]       = html_postget("CameraPTZright_".$i);
		$CameraPTZpos1[$j]        = html_postget("CameraPTZpos1_".$i);
		$CameraPTZpos2[$j]        = html_postget("CameraPTZpos2_".$i);
		$CameraPTZpos3[$j]        = html_postget("CameraPTZpos3_".$i);
		
		//echo("Authentication =". $CameraAuthentication[$j] .", Tick=" . strpos(html_postget("CameraAuthentication_".$i),":") . "<br>");
		
		/// Exist => Update? or Create
		if (($CameraID[$j]==0) && ($CameraName[$j]!="")) {
		  // Create
		  $sql = "INSERT INTO `ha_cameras` (`id`, `Camera_URL`, `Authentication`, `Restrict_Users`, `Camera_name`, `Camera_Profile`, ".
					"`Stream_Type`, `PTZ_UP`, `PTZ_DOWN`, " .
					"`PTZ_LEFT`, `PTZ_RIGHT`, `PTZ_POS1`, `PTZ_POS2`, `PTZ_POS3`) " .
					"VALUES (NULL, '".$CameraURL[$j]."', '".$CameraAuthentication[$j]."', '".$RestrictUsers[$j]."', '".$CameraName[$j]."'," . 
					" '".$CameraProfile[$j]."',".
					" '".$StreamType[$j]."', '".$CameraPTZup[$j]."', '".$CameraPTZdown[$j]."', '".$CameraPTZleft[$j]."', '".$CameraPTZright[$j]."'," .
					" '".$CameraPTZpos1[$j]."', '".$CameraPTZpos2[$j]."', '".$CameraPTZpos3[$j]."' );";
		  $query=mysqli_query($DB,$sql);
		  $CameraID[$j] = mysqli_insert_id($DB);
		  //echo("Camera INSERT (".$CameraID[$j].",i=$i, j=$j): sql=$sql<br>");
		} else {
		  // Update
		  $sql = "UPDATE `ha_cameras` SET `Camera_name`='".$CameraName[$j]."', `Camera_URL` = '".$CameraURL[$j] .
					"', `Authentication` = '".$CameraAuthentication[$j]."', " .	"`Restrict_Users`='".$RestrictUsers[$j].
					"', " .	"`Stream_Type`='".$StreamType[$j] .
					"', `PTZ_UP`='".$CameraPTZup[$j]."', `PTZ_DOWN`='".$CameraPTZdown[$j]."', `PTZ_LEFT`='".$CameraPTZleft[$j]."', " .
					"`PTZ_RIGHT`='".$CameraPTZright[$j]."', `PTZ_POS1`='".$CameraPTZpos1[$j]."', `PTZ_POS2`='".$CameraPTZpos2[$j]."', " .
					"`PTZ_POS3`='".$CameraPTZpos3[$j]."', " . "`Camera_Profile`='".$CameraProfile[$j]."' " .
					" WHERE `id` = '".$CameraID[$j]."';";
		  //echo("Update Camera ID = ".$CameraID[$j]."<br>SQL=$sql<br>".CRLF);
		  $query=mysqli_query($DB,$sql);
		} // END IF

	  } // END IF
	  $i++;$j++;
	} // END While
  } // END IF
  
  // No action Request => Parse DBs
  $sql = "SELECT * FROM `ha_cameras` WHERE 1 ORDER BY id;";
  //echo("sql=$sql<br>");
  $query=mysqli_query($DB,$sql);
  $i=0;
  while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
	$CameraID[$i]             = $row['id'];
	$CameraName[$i]           = $row['Camera_name'];
	$CameraURL[$i]            = $row['Camera_URL'];
	$CameraAuthentication[$i] = $row['Authentication'];
	$RestrictUsers[$i]        = $row['Restrict_Users'];
	$StreamType[$i]           = $row['Stream_Type'];
	$CameraProfile[$i]        = $row['Camera_Profile'];
	$CameraPTZup[$i]          = $row['PTZ_UP'];
	$CameraPTZdown[$i]        = $row['PTZ_DOWN'];
	$CameraPTZleft[$i]        = $row['PTZ_LEFT'];
	$CameraPTZright[$i]       = $row['PTZ_RIGHT'];
	$CameraPTZpos1[$i]        = $row['PTZ_POS1'];
	$CameraPTZpos2[$i]        = $row['PTZ_POS2'];
	$CameraPTZpos3[$i]        = $row['PTZ_POS3'];
	//echo("Camera Content from DB: id=".$CameraID[$i].", Desc=".$CameraName[$i].", V1=".$CameraURL[$i].", V2=".$CameraTwo[$i]."<br>");
	$i++;
  } // END WHILE
  if (($i==0) || ($action=="AddCamera")) { 
    $CameraID[$i]=0; $CameraName[$i]=""; $CameraURL[$i]=""; $CameraAuthentication[$i]=""; $RestrictUsers[$i]=""; 
	$StreamType[$i]=""; $CameraProfile[$i]=""; $CameraPTZup[$i]="";
	$CameraPTZdown[$i]=""; $CameraPTZleft[$i]=""; $CameraPTZright[$i]=""; $CameraPTZpos1[$i]=""; $CameraPTZpos2[$i]=""; $CameraPTZpos3[$i]=""; 
  } // END IF

  // Start Build Page ...
  echo("<h2 class='title'>".$msg["SURVEILLANCE"]["Title"][$Lang]."</h2>");
  // Open Form
  echo("<form name='ChangeCameras' id='ChangeCameras' action='' method='post'>" . CRLF);
  
  // URL Management
  echo("<div class='post_info'>".$msg["SURVEILLANCE"]["URLMngt"][$Lang]."&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

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
  //alert("SubmitForm");
  var ll = document.getElementById(field).value;
  document.getElementById('CamID').value=val;
  document.getElementById('ChangeCameras').action = '?page=Surveillance';
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("Empty Configuration!");
	return;
  }
}

function ConfigCamSubmitform(field,action,val) {
  var ll = document.getElementById(field).value;
  document.getElementById('CamID').value=val;
  document.getElementById('ChangeCameras').action = '?page=CamConfig';
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("Empty Configuration!");
	return;
  }
}
</script>

  <?PHP
  // Table, to Add or Modify Cameras
  echo("<table width=100%>" . CRLF);
  echo("<tr><td width='2%'>&nbsp;</td>" . CRLF);
  echo("<td width='25%'><b>".$msg["MAIN"]["Name"][$Lang]."</b></td>" . CRLF);
  // Stream Type
  echo("<td width='73%'><b>".$msg["SURVEILLANCE"]["Param"][$Lang]."</b></td>" . CRLF);
  $i=0; 
  // When Creating a new Camera
  echo("<input type='hidden' id='CamID' name='CamID' value=''>". CRLF);
  echo("<input type='hidden' name='action' id ='action' value=''/>" . CRLF);
  while ((isset($CameraName[$i]))) {
    echo("<tr>" . CRLF);
	echo("<input type='hidden' id='CameraID_$i' name='CameraID_$i' value='".$CameraID[$i]."'>". CRLF);
	// Drop?
	if ($CameraName[$i]!="") {
      echo("<td><a href='javascript:void();' onClick=\"CheckSubmitform('CameraName_$i','RemoveCamera',".$CameraID[$i].");\"><img src='./images/drop.png' " .
			" style='vertical-align:middle;'/></a></td>" . CRLF);
	} else {
	  echo("<td>&nbsp;</td>" . CRLF);
	} // END IF
    //if ($action=="") {$CameraID[$i]=0;} // END IF
	
    // Camera Description
    echo("<td><input type=\"text\" name=\"CameraName_$i\" id=\"CameraName_$i\" value='".$CameraName[$i]."' size='12'>" . CRLF);
	// Restrict Users
	echo("<br><br><br><br><br><br>Allow View by:<br><select multiple size='2' onmouseover='this.size=this.length;' onmouseout='this.size=2' ".
			"id='RestrictUsers_$i&#91; &#93;' name='RestrictUsers_$i&#91; &#93;'>");
	//echo("<option value=''"); if ($RestrictUsers[$i]=="") { echo(" selected"); }  echo(">Everyone</option>");
	$sql = "SELECT * FROM `users`";
	$query=mysqli_query($DB,$sql);
	while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
	  $ID    = $row['ID'];
	  $Alias = $row['Alias'];
	  if ($Alias!="theonlyzby") {
	    echo("<option value='*=*$ID*=*'"); if ((substr_count($RestrictUsers[$i], '*=*'.$ID.'*=*')) or ($RestrictUsers[$i]=="")) { echo(" selected"); }  echo(">".$Alias."</option>");
	  }
	}
	echo("</select><br><br>" . CRLF);
	
	// Camera Profile
	if (($CameraProfile[$i]!="basic") & ($CameraProfile[$i]!="")) { 
	  echo("<a href=\"javascript:void(1);\" onClick=\"ConfigCamSubmitform('CameraName_".($i)."','SaveCameras',".$CameraID[$i].");\">");
	  echo("<img src='./images/edit.png' title='Edit Camera Configuration'> "); 
	} // END IF
	
	echo("Camera Profile:");
	if (($CameraProfile[$i]!="basic") & ($CameraProfile[$i]!="")) { echo("</a>" . CRLF); }
	echo("<br><select id='CameraProfile_$i' name='CameraProfile_$i' ");
	if ($CameraProfile[$i]=="basic") { echo("onchange=\"ConfigCamSubmitform('CameraName_".($i)."','SaveCameras',".$CameraID[$i].");\" "); }
	echo(">");
	echo("<option value='basic'"); if ($CameraProfile[$i]=="basic") { echo(" selected"); }  echo(">basic</option>");
	echo("<option value='Sercom'"); if ($CameraProfile[$i]=="Sercom") { echo(" selected"); }  echo(">Sercom</option>");
	echo("</select></td>" . CRLF);
    // Stream Type + Camera URL
    echo("<td><b>".$msg["SURVEILLANCE"]["FlowType"][$Lang].": </b><select id='StreamType_$i' name='StreamType_$i'>");
	echo("<option value='img'"); if ($StreamType[$i]=="img") { echo(" selected"); }  echo(">".$msg["SURVEILLANCE"]["ImgOMpeg"][$Lang]."</option>");
	echo("</select><br><b>".$msg["SURVEILLANCE"]["LocalURL"][$Lang].":</b><input type=\"text\" name=\"CameraURL_$i\" id=\"CameraURL_$i\"  value=\"".$CameraURL[$i]."\" size='45'><br>".CRLF);
	// Authentication
	echo("<b>".$msg["SURVEILLANCE"]["Authentication"][$Lang]."</b><br><input type=\"text\" name=\"CameraAuthentication_$i\" id=\"CameraAuthentication_$i\"  value=\"".$CameraAuthentication[$i]."\" size='45'>" . CRLF);
	// PTZ Functions
	echo("<br><b>".$msg["SURVEILLANCE"]["PTZURL"][$Lang].":</b>".$msg["SURVEILLANCE"]["IfAvail"][$Lang]."<br><b>".$msg["SURVEILLANCE"]["Up"][$Lang].
		 ":</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"CameraPTZup_$i\" id=\"CameraPTZup_$i\"  value=\"".$CameraPTZup[$i]."\" size='42'><br>".
	     "<b>".$msg["SURVEILLANCE"]["Down"][$Lang].": </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"CameraPTZdown_$i\" id=\"CameraPTZdown_$i\"  value=\"".
		 $CameraPTZdown[$i]."\" size='42'><br><b>".$msg["SURVEILLANCE"]["Left"][$Lang].":</b><input type=\"text\" name=\"CameraPTZleft_$i\" id=\"CameraPTZleft_$i\"  value=\"".
		 $CameraPTZleft[$i]."\" size='42'><br><b>".$msg["SURVEILLANCE"]["Right"][$Lang].":</b>&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"CameraPTZright_$i\" ".
		 "id=\"CameraPTZright_$i\"  value=\"".$CameraPTZright[$i]."\" size='42'>" .
		 "<br><b>".$msg["SURVEILLANCE"]["Pos1"][$Lang].": </b>&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"CameraPTZpos1_$i\" id=\"CameraPTZpos1_$i\"  value=\"".
		 $CameraPTZpos1[$i]."\" size='42'><br><b>".$msg["SURVEILLANCE"]["Pos2"][$Lang].": </b>&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"CameraPTZpos2_$i\" ".
		 "id=\"CameraPTZpos2_$i\"  value=\"".$CameraPTZpos2[$i]."\" size='42'><br><b>".$msg["SURVEILLANCE"]["Pos3"][$Lang].": </b>&nbsp;&nbsp;&nbsp;<input ".
		 "type=\"text\" name=\"CameraPTZpos3_$i\" id=\"CameraPTZpos3_$i\"  value=\"".$CameraPTZpos3[$i]."\" size='42'>" .
	     "</td>" . CRLF);
	
	// End Table
	echo("</tr>" . CRLF);
    $i++;
  } // END WHILE
  // Add?
  if ((isset($CameraName[$i-1])) || ($action=="")) {
    echo("<tr><td>&nbsp;</td>" . CRLF);
    echo("<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>" . CRLF);
    echo("<td><a href=\"javascript:void(1);\" onClick=\"CheckSubmitform('CameraName_".($i-1)."','AddCamera');\"><img src='./images/add.png'/></a></td>" . CRLF);
    echo("</tr>" . CRLF);
  } // END IF
  echo("<tr><td>&nbsp;</td>" . CRLF);
  echo("<td colspan=2><div class=\"postcontent\">" . CRLF);
  echo("  <span class=\"readmore_b\"><p>" . CRLF);
  echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('CameraName_".($i-1)."','SaveCameras');\">Sauver</a></p></span>" . CRLF);
  echo("  <div class=\"clear\"></div>" . CRLF);
  echo("</div>" . CRLF);
  echo("</td>" . CRLF);
  //echo("<td>&nbsp;</td>" . CRLF);
  echo("<td>&nbsp;</td></tr>" . CRLF);
  echo("</table><br>" . CRLF);

  
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  
  echo("</form>" . CRLF);
  echo("<b>".$msg["SURVEILLANCE"]["SurvExample"][$Lang]);
  echo("</div>	<!-- end .postcontent -->" . CRLF);
?>






<div class="postcontent">

	
		<div class="clear"></div>
	</div> 

</div>
<script type="text/javascript">
function submitform(action) {
  //alert("submit + Action="+action);
  document.ChangeCameras.action.value = action;
  document.ChangeCameras.submit();
}
</script>


<?php
  mysqli_close($DB);
} // End of Function SysMap