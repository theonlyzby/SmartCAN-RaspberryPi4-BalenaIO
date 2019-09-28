<?PHP
// Includes
//include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function CamConfig 
function CamConfig() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.CamConfig.php";

	  //echo("<script src=\"https://code.jquery.com/jquery-1.11.3.min.js\"></script>");
	  echo("<link href=\"./js/resources/jquery.selectareas.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\" />" . CRLF);
	  echo("<script type=\"text/javascript\" src=\"./js/jquery.selectareas.js\"></script>" . CRLF);
	  echo("<script type=\"text/javascript\" src=\"./js/resources/example.css\"></script>" . CRLF);

  // Action Requested via Form?  
  $action    = html_postget("action");
  //echo("Action=".$action."<br>");
  // Action Request?	
  if ($action!="") {
	// Save Surveillance URLs
	$i=0;$j=0;
	while (((html_postget("CameraName_".$i)!="") || $i==0) && ($action!="")) {
	  //echo($action.", CamID to Delete=".html_postget("CamID").", CameraID_$i=".html_postget("CameraID_".$i)."<br>");
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

	  $i++;$j++;
	} // END While
  } // END IF
  
  // Parse DBs to get field values
  $sql = "SELECT * FROM `ha_cameras` WHERE `id`='".html_postget("CamID")."';";
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
  $i--;

  // Start Build Page ...
  echo("<h2 class='title'>".$msg["CAMCONFIG"]["Title"][$Lang]."</h2>");
  // Open Form
  echo("<form name='ChangeCameras' id='ChangeCameras' action='?page=ConfigCam' method='post'>" . CRLF);
  
  // Camera Zones Management
  echo("<div class='post_info'>".$msg["CAMCONFIG"]["Zones"][$Lang]."&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: -15px;'>" . CRLF);
?>

<script type="text/javascript">
function CheckSubmitform(field,action,val) {
  //alert("SubmitForm");
  var ll = document.getElementById(field).value;
  document.getElementById('CamID').value=val;
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("Empty Configuration!");
	return;
  }
}

</script>

  <?PHP
  // Add or Modify Camera Zones
  $area_x[0] = 10;
  $area_y[0] = 20;
  
  $area_x[1] = 100;
  $area_y[1] = 200;
?>
<script type="text/javascript">
$(document).ready(function () {
  $('img#camera').selectAreas({
					minSize: [10, 10],
					onChanging: AreaChanged,
					onChanged: AreaChanged,
					width: 640,
					height: 480,
					maxAreas: 4,
					areas: [
						{
							id: 0,
							x: 10,
							y: 20,
							width: 60,
							height: 100,
						},
						{
							id: 1,
							x: 100,
							y: 200,
							width: 100,
							height: 60,
						}
					]
  });
  $('#imgtarget').mouseup(function(){
	$('img#camera').selectAreas('remove', 7);
  });
});

function AreaChanged (event, id, areas) {
	var inf = "";
	for (var i = 0; i <= (areas.length-1); i++) {
		inf=inf+" - Area #"+i+". x="+areas[i].x+", y="+areas[i].y;
		var pos_x                = (areas[i].x);
		var pos_y                = (areas[i].y)-500-(i*20);
		var div_txt              = document.getElementById("text_zone_"+i);
		var div_act              = document.getElementById("Action_"+i);
		div_txt.style.top        = pos_y+"px";
		div_txt.style.left       = pos_x+"px";
		div_txt.style.visibility = "visible";
		div_act.style.visibility = "visible";
		//alert("Zone "+i+" moved to X="+pos_x+", Y="+pos_y);
	}
	for (var i = areas.length; i <= 3; i++) {
		//alert(i);
		var div_txt              = document.getElementById("text_zone_"+i);
		var div_act              = document.getElementById("Action_"+i);
		div_txt.style.visibility = "hidden";
		div_act.style.visibility = "hidden";
	}
};

</script>
<div class="image-decorator" id="imgtarget">
<?php
  // Displays camera image
  $pageURL = 'http';
  if (isset($_SERVER["HTTPS"])) { $pageURL .= "s"; }
  $pageURL .= "://";
  $pageURL .= $_SERVER["HTTP_HOST"];
  $camID = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(14/strlen($x)) )),1,14);
  $sqlR = "INSERT INTO `ha_cameras_temp` (`id`, `Camera_URL`, `Temp_URL`, `Authentication`, `Create_Date`) VALUES (NULL, '".$CameraURL[$i]."', '".$camID.
			  "', '".$CameraAuthentication[$i]."', CURRENT_TIMESTAMP);;";
  $retourR = mysqli_query($DB,$sqlR);
  $pageURL .= "/homectrl/camera?camID=".$camID;
  echo("<img id=\"camera\" src=\"".$pageURL."\" width=640px height=480px />" . CRLF);
  echo("</div>" .CRLF);
  
  // Zone Numbers (IDs)
  for ($i = 0; $i <= 3; $i++) {
    $vis = "hidden"; if (isset($area_x[$i])) { $vis = "visible"; }
    echo("<div id='text_zone_".$i."' STYLE='visibility:" . $vis . "; z-index: 200; position:relative; ");
	echo("top:".(($area_y[$i])-500-($i*20))."px; ");
	echo("left:".$area_x[$i]."px; ");
	echo("width:15px; text-align: center; height:16px; background-color:#00B7EB;border: 2px solid white; border-radius: 5px;'><h2><font color=white><b>" .
			($i+1) . "</b></font></h2></div>" . CRLF);
  } // END FOR
  
  echo("" . CRLF);
  //echo("  <span class=\"readmore_b\"><p>" . CRLF);
  //echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('CameraName_".($i-1)."','SaveCameras');\">Sauver</a></p></span>" . CRLF);
  echo("  <div class=\"clear\"></div>" . CRLF);
  echo("</div>" . CRLF);
  echo("</td>" . CRLF);
  //echo("<td>&nbsp;</td>" . CRLF);


  
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  
  echo("</form>" . CRLF);
  
  echo("</div>	<!-- end .postcontent -->" . CRLF);
  
  echo("</div>" . CRLF);
  echo("</div><br>" . CRLF);
  
  echo("<div id='sidebar' height='500' >" . CRLF);
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . "Actions" . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss' style='height:380pt;overflow:auto'><br><br>" . CRLF);
  
  
  for ($i = 0; $i <= 3; $i++) {
    echo("<ul>" . CRLF);
	$vis = "hidden"; if (isset($area_x[$i])) { $vis = "visible"; }
    echo("<div id='Action_".$i."' STYLE='visibility:" . $vis . "'>" . CRLF);
	echo("<li>Action ".($i+1) . ": &nbsp; " . CRLF);
	echo("<select id='FN_$i'>" . CRLF);
	echo("<option value='Mail'"); if ($i=="Mail") { echo(" selected"); }  echo(">Mail</option>");
	echo("<option value='FTP'"); if ($i=="Mail") { echo(" selected"); }  echo(">FTP</option>");
	echo("</select>" . CRLF);
	echo("<br>Destination: <input type=\"text\" name=\"CameraPTZpos3_$i\" id=\"CameraPTZpos3_$i\"  value=\"\" size='20'></li>" . CRLF);
	echo("</div></ul>" . CRLF);
  } // END FOR

  
  echo("</div>");
   echo("  <span class=\"readmore_b\"><p>" . CRLF);
  echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('CameraName_".($i-1)."','SaveCameras');\"><font color=white>Sauver</font></a></p></span>" . CRLF); 
  
  
?>






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