<?PHP
// Main Function Vibes
function Vibes() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.Vibes.php";

  // Variables passed within the <Form> or URL
  $selected_level = html_postget("selected_level");
  if ($selected_level=="") { 
    $sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_vibe_pages` LIMIT 1;");
    $query = mysqli_query($DB,$sql);
    $row = mysqli_fetch_array($query, MYSQLI_BOTH);
    $selected_level = $row['page_name'];
  } // End IF
  $action     = html_postget("action");
  //echo("Action=".$action." (VibeID=".html_postget("VibeID").",VibeD=".html_postget("VibeD").")<br>");
  //if (ADMIN_DEBUG) { echo("Action=$action, Access Level=$Access_Level<br>"); }
  $Vibe_Name  = html_postget("Vibe_Name");
  $VibeID     = html_postget("VibeID");
  $IdArray    = html_postget("moveMe_id");
  $DescArray  = html_postget("moveMe_desc");
  $XcoorArray = html_postget("moveMe_xcoor");
  $YcoorArray = html_postget("moveMe_ycoor");  
  
  if (($action=="AddVibeElement") || ($action=="Prepare_AddVibeElement")) {
	$VibeID    = html_postget("VibeD");
  }
  if ($action=="EditVibe") {
    $idV       = html_postget("VibeD");
	$Vibe_Name = $DescArray[$idV];
	$VibeID    = $IdArray[$idV];
    // Fill VibeElements Arrays from DB
	$sql = "SELECT * FROM `ha_vibe_elements` WHERE `vibe_id`=".$VibeID." ORDER BY id;";
	//echo("EditVibe: sql=$sql<br>");
	$query=mysqli_query($DB,$sql);
	$i=0;
	while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
	  $VibeElType[$i]  = $row['mode'];
	  $VibeElMem[$i]   = $row['memory_number'];
	  $VibeElOut[$i]   = $row['output_number'];
	  $VibeElDelay[$i] = $row['delay'];
	  $VibeElID[$i]    = $row['id'];
	  //echo("Edit VAlue: Grab DB Values, ".$VibeElID[$i].", mode=".$VibeElType[$i]."<br>");
	  $i++;
	} // END WHILE
  } else {
    // Fill VibeElements Arrays from Form
    $i=0;$j=0;
    while (((html_postget("VibeElType_".$i)!="") || $i==0) && ($action!="AddVibe"))  {
      if (($action=="RemoveVibeElement") && (html_postget("VibeD")==html_postget("VibeElID_".$i))) { 
	    // Delete VibeElementID
	    $idV        = html_postget("VibeD");
	    //echo($action."VibeElementID to Delete=$idV<br>");
	    $sql = "DELETE FROM `ha_vibe_elements` WHERE `ha_vibe_elements`.`id` = ".$idV.";";
	    $query=mysqli_query($DB,$sql);
		$j--;
	  } else {
        $VibeElType[$j]  = html_postget("VibeElType_".$i);
	    $VibeElMem[$j]   = html_postget("VibeElMem_".$i);
	    $VibeElOut[$j]   = html_postget("VibeElOut_".$i);
	    $VibeElDelay[$j] = html_postget("VibeElDelay_".$i);
	    $VibeElID[$j]    = html_postget("VibeElID_".$i);
		if ($action=="SaveVibeElements") {
		  //  Save Vibe Elements
		  $idV       = html_postget("VibeD");
		  $Vibe_Name = html_postget("Vibe_Name");
		  $Vide_id   = html_postget("VibeD"); 
		  if (isset(html_postget("moveMe_id")[$idV])) { $VibeID    = html_postget("moveMe_id")[$idV]; } else { $VibeID = 0; }
		  
		  // Create New Vibe?
		  if ($Vide_id==0) {
		    // Add Vibe
			$sql = "INSERT INTO `ha_vibes` (`id`, `page`, `img_x`, `img_y`, `description`) VALUES (NULL, '".$selected_level."', '0', '0', '".$Vibe_Name."');";
			$query=mysqli_query($DB,$sql);
			$Vide_id = $VibeID = mysqli_insert_id($DB);
			//echo("Create Vibe: SQL=$sql <br>".CRLF);
		  } // END IF
		  
		  if ($VibeElID[$j]==0) {
		    // Create Vibe Element
			$value="";
			if ($VibeElType[$j]=="OUT") {
			  $sql = "SELECT * FROM `lumieres_status` WHERE `id`=".$VibeElOut[$j].";";
			  $query=mysqli_query($DB,$sql);
			  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
			  $value = $row['valeur'];
			} // END IF
			$sql = "INSERT INTO `ha_vibe_elements` (`id`, `vibe_id`, `page`, `mode`, `card_number`, `memory_number`, `output_number`, `output_value`, `delay`) VALUES (NULL, '".$VibeID."', '".$selected_level.
					"', '".$VibeElType[$j]."', '', '".$VibeElMem[$j]."', '".$VibeElOut[$j]."', '".$value."', '".$VibeElDelay[$j]."');";
			$query=mysqli_query($DB,$sql);
			$VibeElID[$j] = mysqli_insert_id($DB);
			//echo("Create Vibe Elements of $Vibe_Name(".$idV."): Element ID = ".$VibeElID[$j]."<br>SQL=$sql<br>".CRLF);
		  } else {
		    // Update Vibe Element
			$sql = "SELECT * FROM `ha_vibe_elements` WHERE `id`=".$VibeElID[$j].";";
			$query=mysqli_query($DB,$sql);
			$row = mysqli_fetch_array($query, MYSQLI_BOTH);
			$value = $row['output_value'];
			$query=mysqli_query($DB,$sql);
			if ($VibeElOut[$j]!=$row['output_number']) {
			  $value="";
			  if ($VibeElOut[$j]!="") {
			    // Changes to OUT or other OUT => get live value
			    $sql = "SELECT * FROM `lumieres_status` WHERE `id`=".$VibeElOut[$j].";";
			    $query=mysqli_query($DB,$sql);
			    $row = mysqli_fetch_array($query, MYSQLI_BOTH);
			    $value = $row['valeur'];
			  } // END IF
			} // END IF
		    $sql = "UPDATE `ha_vibe_elements` SET `mode`='".$VibeElType[$j]."', `memory_number` = '".$VibeElMem[$j]."', `output_number`='".$VibeElOut[$j]."', `output_value`='".$value."', " .
					"`delay`='".$VibeElDelay[$j]."' WHERE `ha_vibe_elements`.`id` = '".$VibeElID[$j]."';";
		    //echo("Update Vibe Elements of ".$Vibe_Name."(".$Vide_id."): Element ID = ".$VibeElID[$j]."<br>SQL=$sql<br>".CRLF);
			$query=mysqli_query($DB,$sql);
		  } // END IF
		} // END IF SaveVibeElements
	  } // END IF
	  $i++;$j++;
    } // END WHILE
	
	// Add Vibe
	if ($action=="AddVibe") {
	  $Vibe_Name = ""; $VibeID = 0;
	  $VibeElID=array();
	} // END IF
  } // END IF
  
  //Delete Vibe
  if ($action=="DeleteVibe") {
    $VibeD = html_postget("VibeD");
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `ha_vibes` " .
              " WHERE `id` = \"" . $VibeD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `ha_vibe_elements` " .
              " WHERE `vibe_id` = \"" . $VibeD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
  } // End IF
  
  // Add or Modify Vibes
  if (($action=="update") || ($action=="AddVibe") || ($action=="EditVibe")) {
    //echo("<br>update or AddVibe: id=".$IdArray[1]."<br>".CRLF);
    // Update DB
    $i = 1;
    while (isset($IdArray[$i])) { 
	  //echo("<br>update: id=".$IdArray[$i].", X=".substr($YcoorArray[$i],0,-2).", Y=".substr($XcoorArray[$i],0,-2).CRLF);
      $sql = mysqli_real_escape_string($DB,"UPDATE `ha_vibes` SET `img_x` = \"" . 
             $YcoorArray[$i] . "\", " . "`img_y` = \"" .
             $XcoorArray[$i] . "\", `description` = \"" .
             $DescArray[$i]  . "\" " .
             "WHERE `Id` = \"" . $IdArray[$i] . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  //echo(" SQL=$sql<br>".CRLF);
      if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
      $i++;
    } // End While

  } // End IF (Add or Modify Vibes

  // Add Level
  if ($action=="AddLevel") {
    // Move File
	$Level_Name = html_postget("Level_Name");
	if ($Level_Name!="") {
	  // Create New DB Entry
      $sql = mysqli_real_escape_string($DB,"INSERT INTO `domotique`.`localisation` (`id`, `lieu`) " .
		         "VALUES (NULL, \"" . $Level_Name . "\");");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
	} else {
	  log_this($msg["MAIN"]["FileCopyError"][$Lang]);
    } // End IF
  } // End IF
	
    // Modify Page
    if ($action=="ModifyPage") {
      $Level = html_postget("Level"); // original Name
	  $LName = html_postget("LName"); // New Name
      $sql = mysqli_real_escape_string($DB,"UPDATE `ha_vibe_pages` SET `page_name` = \"" . $LName .
               "\" WHERE `page_name` = \"" . $Level . "\" LIMIT 1;");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
	    
	  $sql = mysqli_real_escape_string($DB,"UPDATE `vibes` SET `page` = \"" . $LName .
               "\" WHERE `page` = \"" . $Level . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
	  $selected_level = $LName;
    } // End IF
  
    // Delete Page
    if ($action=="DeletePage") {
      $Level = html_postget("Level"); // original Name
	    $sql = mysqli_real_escape_string($DB,"DELETE FROM `localisation` " .
               " WHERE `lieu` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
	    
		// Remove Lights
		$sql = mysqli_real_escape_string($DB,"DELETE FROM `lumieres` " .
               " WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }

		// Remove Temperature Probe
		$sql = mysqli_real_escape_string($DB,"DELETE FROM `chauffage_sonde` " .
               " WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }

    } // End IF
    
  // Start Build Page ...

  // Existing levels
  $sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_vibe_pages` WHERE 1;");
  $query = mysqli_query($DB,$sql);
  $i=0;
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $level[$i] = $row['page_name'];
    $i++;
  } // End While

  echo("<h2 class='title'>" . $msg["VIBES"]["Title"][$Lang] . "</h2>");
  echo("<form name='ChangeName' id='ChangeName' action='./index.php?page=Vibes' method='post'>" . CRLF .
       "<div class='post_info'>&nbsp;Niveau: " . CRLF . 
       "<input type='text' name='LName' id='LName' value='" . $selected_level . "'/>&nbsp;&nbsp;" . CRLF .
       "<a href='javascript:void();' onClick='ActiveLevelChange(\"LName\")'><img src='./images/edit.png'/></a>&nbsp;&nbsp;" . CRLF .
       "<a href='javascript:void();' onClick='ActiveLevelDelete()'><img src='./images/drop.png'/></a>&nbsp;&nbsp;" . CRLF .
       "</div><input type='hidden' name='action' id ='action' value=''/>" . CRLF .
	   "<input type='hidden' name='page' id ='page' value='Vibes'/>" . CRLF .
	   "<input type='hidden' name='Level' id ='Level' value='" . $selected_level . "'/>" . CRLF .
	   "</form>" . CRLF);
  $ImgURL = "../www/images/plans/border_bkg.png";
  $ImgURL = $ImgURL . "?" . rand(1,30000);
  $ImgURL = str_replace(" ",chr(37)."20",$ImgURL);
  echo("	<div class='postcontent' name='plan' " .
        "style='background-image: url(" . $ImgURL . "); position:relative; background-position: 50px 0px;" .
        " background-repeat: no-repeat; height: 355px;" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

<script type="text/javascript">
//document.onmousedown=coordinates;
document.onmouseup=mouseup;

function coordinates(object,e)
{
//movMeId=document.getElementById("moveMe");
//alert(object);
movMeId=document.getElementById(object);
if (e == null) { e = window.event;}
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
//alert(sender.id.substring(0,6));

if (sender.id.substring(0,6)=="moveMe")
  {
  //alert('-'+sender.id.substring(6,10)+'-');
  var ObjName = sender.id.substring(0,6);
  var ObjNber = sender.id.substring(6,10);

  document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].style.backgroundColor='lime';

  mouseover=true;
  pleft=parseInt(movMeId.style.left);
  ptop=parseInt(movMeId.style.top);
  xcoor=e.clientX;
  ycoor=e.clientY;
  document.onmousemove=moveImage;
  return false;
  }
else { return false; }
}

function moveImage(e)
{
if (e == null) { e = window.event;}
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
//alert(sender.id.substring(6,7));
posx = (pleft+e.clientX-xcoor)+(39*(sender.id.substring(6,7)-1));
movMeId.style.left=pleft+e.clientX-xcoor+"px";
movMeId.style.top=ptop+e.clientY-ycoor+"px";
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

var ObjName = sender.id.substring(0,6);
var ObjNber = sender.id.substring(6,10);
document.pos.elements[ObjName + '_xcoor[' + ObjNber + ']'].value=posx+"px";
document.pos.elements[ObjName + '_ycoor[' + ObjNber + ']'].value=movMeId.style.top;
return false;
}

function mouseup(e) {
if (e == null) { e = window.event;}
document.onmousemove=null;
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

var ObjName = sender.id.substring(0,6);
var ObjNber = sender.id.substring(6,10);
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].style.backgroundColor='white';
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].focus();
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].setSelectionRange(300, 300);
}

function Vibefocus(e)
{
document.getElementById("moveMe"+e).src="../www/images/bkg_vibe_on.png";
//alert("focus"+e);
}

function VibeUNfocus(e)
{
document.getElementById("moveMe"+e).src="../www/images/bkg_vibe_silver.png";
}
</script>

  <?PHP
  // Places Vibe Images on plan
  $sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_vibes` WHERE " .
           "`page`=\"" . $selected_level . "\";");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysqli_query($DB,$sql);
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $id[$i]          = $row['id'];
    $description[$i] = $row['description'];
    $img_x[$i]       = $row['img_x'];
    $img_y[$i]       = $row['img_y'];
	if ($i%10!=0) {
      $web_offset[$i]  = (($i%10)-1)*39;
	} else {
	  $web_offset[$i]  = ($i%10)*39;
	}
	if ($i>10) { $web_offset[$i] = $web_offset[$i] - ((($i%10)-1)*39);}
	
    // Display on page
    echo("<img id='moveMe" . $i . "' " .
         "src='../www/images/bkg_vibe_silver.png' " .
         "onmousedown='coordinates(\"moveMe" . $i . "\")' />" . CRLF);
    echo("<script type='text/javascript'>" . CRLF);
    echo("//Movable image" . CRLF);
    echo("movMeId".$i."=document.getElementById(\"moveMe".$i."\");" . CRLF);
    echo("//image starting location" . CRLF);
    echo("movMeId".$i.".style.top=\"".$img_x[$i]."px\";" . CRLF);
    echo("movMeId".$i.".style.left=\"".
         ($img_y[$i]-$web_offset[$i])."px\";" . CRLF);
    echo("</script>" . CRLF);

    $i++;
  } // End While


  echo("<div class='clear'></div>" . CRLF);
//  echo("<a href='javascript:void();' onClick=''>" .
//      "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a>");
  echo("</div>	<!-- end .postcontent -->" . CRLF);

?>
<div id="rss-3" class="block widget_rss">
<ul>
<?PHP
  $i=0;
  echo("<table width='100%'><tr><td width='70%'>");
  if (isset($level[$i])) { echo($msg["VIBES"]["OtherPage"][$Lang]."<br>"); }
  while (isset($level[$i])) {
    if ($level[$i]!=$selected_level) {
      echo("<li>&nbsp;<a class='rsswidget' href='./index.php?page=Vibes&selected_level=" .
			$level[$i] . "' title='".$msg["VIBES"]["EditPage"][$Lang]."'>" .
            $level[$i] .
            "</a> <span class='rss-date'></span></li>");
    } // End IF
    $i++;
  } // End WHile
  echo("</td><td width='30%' style='vertical-align:middle'><a href='javascript:void();' " .
       "onClick=\"showOverlay('ChangeImg','Lvl_Name');\">" .
       "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' " .
	   "title=\"".$msg["MAIN"]["ChangeLevelImage"][$Lang]."\"/></a></td></tr></table>");
  
  echo("</ul>" . CRLF);
  echo("<div class='post_info'><a href='javascript:void();' onClick=\"showOverlay('AddLevel','Level_Name');\" id='clickMe'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;".$msg["VIBES"]["AddPage"][$Lang]."</a></div>" . CRLF);
  echo("</div>" . CRLF);
?>

<div id="ChangeImg" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('ChangeImg');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>
<p align=center><h1 align=center><?php echo($msg["MAIN"]["ChangeLevelImage"][$Lang]." ".$selected_level);?></h1></p>
<br><br><br><br>
<form id="NewImg" name="NewImg" enctype="multipart/form-data" action="./index.php?page=Vibes&selected_level=<?php echo($selected_level);?>" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelImage"][$Lang]); ?>:&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="ImgFile" name="ImgFile" type="file" accept="image/png" /></td></tr>
<tr><td width="20%">&nbsp;<input type="hidden" name="FakeLvl_Name" id="FakeLvl_Name" value="01234567890azertyuiop"/>
<input type="hidden" name="Lvl_Name" id="Lvl_Name" value="<?php echo($selected_level);?>"/>
<input type="hidden" name="action" id="action" value="ChangeImg"/></td></td>
<td width="30%"><div class="postcontent" style="valign:absmiddle;">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewImg','FakeLvl_Name','ImgFile');"><?php echo($msg["MAIN"]["Modify"][$Lang]); ?></a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>
</div>

<div id="AddLevel" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('AddLevel');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>
<p align=center><h1 align=center><?php echo($msg["VIBES"]["AddPage"][$Lang]); ?></h1></p>
<br><br><br><br>

 <form id="NewLevel" name="NewLevel" enctype="multipart/form-data" action="./index.php?page=Vibes&action=AddLevel" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["VIBES"]["PageName"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td>
<td width="50%"><input name="Level_Name" id="Level_Name" type="text" size="20" value="" /></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%"><div class="postcontent">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewLevel','Level_Name','NewPlan');"><?php echo($msg["MAIN"]["Add"][$Lang]); ?></a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>

</div>

<script type="text/javascript">
 var op = 0;
 
 function showOverlay(divID,Ifocus) {
 var o = document.getElementById(divID);
 SurImpose('main',divID);
 o.style.visibility = 'visible';
 o.style.opacity = 0.05;
 op=op+5;
 fadein(op,divID);
 document.getElementById(Ifocus).focus();
 }

function SurImpose(Ref,Obj) {
  oElement = document.getElementById(Ref);
  ToMove =  document.getElementById(Obj);
  var iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetTop;
    oElement = oElement.offsetParent;
  }
  ToMove.style.top = (iReturnValue+5)+"px";
  oElement = document.getElementById('header');
  iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetLeft;
    oElement = oElement.offsetParent;
  }
  ToMove.style.left = (iReturnValue+5)+"px";
  return true;
}
 
function fadein(op,divID) {

 var o = document.getElementById(divID);
 opa = op/100;
 
 o.style.opacity = opa;
 op=op+5;

 if(op>=105) { return; }
 var cmd = "fadein(" + op.toString() + ",'" + divID.toString() + "')";
 setTimeout(cmd,50);
}

 function hideOverlay(lID) {
 var o = document.getElementById(lID);
 o.style.visibility = 'hidden';
 }

function CheckLevelName(lID) {
   var Name   = document.getElementById(lID).value;
   // Empty?
   if (Name.length==0) {
	 alert("Nom de page Vide!");
     document.getElementById(lID).focus();
	 return;
   }
   // Correct Name?
   var NameOut = "";
   var j = 0;
   var d = "";

   var l = Name.length-1;
   while (Name.substr(l,1) == " ") {
     l = l - 1;
   }
   l =l + 1;

   for (i = 0 ; i < l ;i++) {
     c = Name.substr(i,1);
     if ((((c >= "0") && (c <= "9")) || ((c >= "a") 
      && (c <= "z")) || ((c >= "A") && (c <= "Z"))) || ((c==" ") 
      && (d!=" "))) {
        NameOut += c;
        d=c;
     }
   }
   if (NameOut != Name) {
    alert("<?php echo($msg["MAIN"]["UseOnlyAZ"][$Lang]); ?>");
    document.getElementById(lID).value = NameOut;
    document.getElementById(lID).focus();
    return;
   }
   

   // Level Exits?
   <?php
   $i   = 0;
   $var = "var Levels=new Array(";
   while ( isset($level[$i]) ) {
     if ($i>=1) { $var .= ","; }
     $var .= "\"" . $level[$i] . "\"";	
     $i++;
   }
   $i--;
   $var .= ");" . CRLF;
   if ($i>=0) {
   echo($var);
   ?>
   var i = 0;
   while (i<=<?php echo($i);?>) {
     if (Name==Levels[i]) {
	   alert("<?php echo($msg["VIBES"]["PageAlreadyExists"][$Lang]); ?>!");
	   document.getElementById(lID).focus();
       return;
	 }
	 i=i+1;
   }
   <?php } else { echo($msg["MAIN"]["DBempty"][$Lang] . CRLF); } // End IF ?>
   return true;
}

 function CheckForm(FormName,lName,ImgFile) {
   // Check Level Name
   if (!CheckLevelName(lName)) {alert("NOK"); return;}
   // Form OK => Submit
   document.getElementById(FormName).submit();
 }

 function ActiveLevelChange(lID) {
  	if (CheckLevelName(lID)) {
	  document.getElementById("action").value="ModifyPage";
	  document.getElementById("ChangeName").submit();
	}
 }
 
 function ActiveLevelDelete() {
   if (confirm("Etes vous certain?")) {
	 document.getElementById("action").value="DeletePage";
	 document.getElementById("ChangeName").submit();
   }	
 }
 
</script>
 
</div>
</div>

<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . $msg["VIBES"]["SideTitle"][$Lang] . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss' style='height:280pt;overflow:auto'>" . CRLF);
  echo("<ul>" . CRLF);

  // List Vibes
  echo("<form name='pos' method='post' id='ChangeVibe'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."'>" . CRLF);
  echo("<input type='hidden' name='page' value='" .
        "Vibes'/>" . CRLF);
  echo("<input type='hidden' name='selected_level' value='" .
        $selected_level . "'/>" . CRLF);
  echo("<input type='hidden' name='VibeD' value=''/>" . CRLF);
  $i = 1;
  while (isset($id[$i])) {
    echo("  <li><input type='text'" .
         " name='moveMe_desc[".$i."]'" .
         " onfocus='Vibefocus(\"$i\")' " .
         " onblur='VibeUNfocus(\"$i\")' " . 
         " size=17 value='" . $description[$i] . 
         "' />" . CRLF);
	echo("<a href='javascript:void();' onClick='VibeEdit(".$i.");'><img src='./images/edit.png'/></a>" . CRLF);
	echo("<a href='javascript:void();' onClick='VibeDelete(".$id[$i].");'><img src='./images/drop.png'/></a></li>" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe_id[".$i."]' value='" . $id[$i] . "' />" . CRLF);

    echo("  <input type='hidden'" .
         " name='moveMe_xcoor[".$i."]' value='" . $img_y[$i] .
         "px' />" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe_ycoor[".$i."]' value='" . $img_x[$i] . 
         "px' />" . CRLF);
    $i++;
  } // End While
  
?>

&nbsp;&nbsp;<a href='javascript:void(1);' onClick='VibeAdd();'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;&nbsp;&nbsp;<?php echo($msg["VIBES"]["AddVibe"][$Lang]); ?></a>
  </ul></div>


<div class="postcontent">

		<span class="readmore_b">
<a class="readmore" href="javascript:void(1);" style="color: white;" onclick="submitform('update');"><?php echo($msg["MAIN"]["update"][$Lang]); ?></a></span>
		<div class="clear"></div>
	</div>

<script type="text/javascript">

// Submit Modify or Add Vibes
function VibeAdd(id) {
  submitform("AddVibe");
}

function VibeDelete(id) {
  if (confirm("<?php echo($msg["VIBES"]["SureDelVibe"][$Lang]); ?>")) {
    document.pos.VibeD.value = id;
    submitform("DeleteVibe");
  }
}

function VibeEdit(id) {
  document.pos.VibeD.value = id;
  submitform("EditVibe");
}

function CheckSubmitform(field,action,id) {
  document.pos.VibeD.value = id;
  var ll = document.getElementById(field).value;
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("<?php echo($msg["MAIN"]["EmptyDescription"][$Lang]); ?>!");
	return;
  }
}

function submitform(action) {
  //alert("submit + Action="+action);
  document.pos.action.value = action;
  document.pos.submit();
}
</script>

  <input type="hidden" name="action" value="" />    

</div>


<div id="NewVibe" style="<?php 
if (($action!="AddVibeElement") && ($action!="Prepare_AddVibeElement") &&  ($action!="AddVibe") &&  ($action!="EditVibe") &&  ($action!="RemoveVibeElement") ) { 
  echo("visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px;"); 
} else {
  echo("visibility:visible; z-index: 1; position: absolute; top: 200px; left: 475px;");
}
?> background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('NewVibe');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>

<p align=center><h1 align=center><?php echo($msg["VIBES"]["AddEditVibe"][$Lang]." ".$selected_level);?></h1></p>
<br><br>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo($msg["VIBES"]["VibeName"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<input id="Vibe_Name" name="Vibe_Name" type="text" value ="<?php echo($Vibe_Name); ?>"/>
<br><br>

<table>

<tr><td width='10%'>&nbsp;&nbsp;&nbsp;&nbsp;</td><td width='20%'><?php echo($msg["VIBES"]["Type"][$Lang]); ?></td><td width='20%'><?php echo($msg["MAIN"]["Memory"][$Lang]); ?></td><td width='20%'><?php echo($msg["MAIN"]["Output"][$Lang]); ?></td><td width='20%'><?php echo($msg["MAIN"]["Delay"][$Lang]); ?></td><td width='10%'>&nbsp;</td></tr>




<?php
$i=0;$j=0; 
// When Creating a new Vibe
if ($action=="AddVibe") {$j=1;} // END IF
while ((isset($VibeElType[$i])) || ($j==1) ) {
  if ((!isset($VibeElID[$i])) && ($action=="AddVibe") ) { 
    $j++; //echo("AddVibe, J=$j<br>");
  }
  if ( (!isset($VibeElType[$i+1])) && ($action=="AddVibeElement")) {
	$j++; //echo("AddVibeElement, J=$j<br>");
	$VibeElID[$i+1]=0;
  }
  
  echo("<tr>" . CRLF);
  echo("<td>&nbsp;</td>" . CRLF);
  if ($action=="AddVibe") {$VibeElID[$i]=0;} // END IF
  echo("<input type='hidden' id='VibeElID_$i' name='VibeElID_$i' value='".$VibeElID[$i]."'>". CRLF);
  // OLD echo("<td><select name=\"VibeElType_$i\" id=\"VibeElType_$i\" onchange='javascript:document.getElementById(\"VibeElCard_$i\").style.visibility = \"visible\";'><option value=\"\"></option>" . CRLF);
  echo("<td><select name=\"VibeElType_$i\" id=\"VibeElType_$i\" onchange=\"javascript:CheckSubmitform('Vibe_Name','Prepare_AddVibeElement',$i);\"><option value=\"\"></option>" . CRLF);
  echo("<option value=\"MEM\" "); if (isset($VibeElType[$i])) { if ($VibeElType[$i]=="MEM") { echo("selected"); } } echo(">".$msg["MAIN"]["Memory"][$Lang]."</option>" . CRLF);
  echo("<option value=\"OUT\" "); if (isset($VibeElType[$i])) { if ($VibeElType[$i]=="OUT") { echo("selected"); } } echo(">".$msg["MAIN"]["Output"][$Lang]."</option>" . CRLF);

  echo("</select></td>" . CRLF);

  echo("<td><select name=\"VibeElMem_$i\" id=\"VibeElMem_$i\" ");
  if (isset($VibeElType[$i])) {
    if ($VibeElType[$i]!="MEM") {
	  echo("style=\"visibility:hidden\">");
	} else {
      echo("><option value=\"\"></option>" . CRLF);
      $sql = "SELECT * FROM `ha_element` WHERE (`element_type`='0x16') ORDER BY `Manufacturer`, `card_id`, `element_name`;";
      $query = mysqli_query($DB,$sql);
      while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
        $name = $row['Manufacturer'] . "/" . $row['element_name'] . " (Subsystem ".$row['card_id'].")";
        $idM  = $row['id'];
	    $select = "";
	    if ($VibeElMem[$i]==$idM) { $select = " selected"; }
        echo("<option value=\"" . $idM . "\"" . $select . ">" . $name . "</option>" . CRLF);
      } // END WHILE
	} // END IF
  } else { echo("style=\"visibility:hidden\">"); } // END IF
  echo("</select></td>" . CRLF);

  // Element Output Select
  echo("<td><select name=\"VibeElOut_$i\" id=\"VibeElOut_$i\" ");
  if (isset($VibeElType[$i])) {
    if ($VibeElType[$i]!="OUT") {
	  echo("style=\"visibility:hidden\">");
	} else {
      echo("><option value=\"\"></option>" . CRLF);
      $sql = "SELECT * FROM `lumieres` WHERE 1 ORDER BY `Manufacturer`, `localisation`, `description`;";
      $query = mysqli_query($DB,$sql);
      while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
        $name = $row['Manufacturer'] . "/" . $row['localisation'] . "/" . $row['description'];
        $idO  = $row['id'];
	    $select = "";
	    if ($VibeElOut[$i]==$idO) { $select = " selected"; }
        echo("<option value=\"" . $idO . "\"" . $select . ">" . $name . "</option>" . CRLF);
      } // END WHILE
	} // END IF
  } else { echo("style=\"visibility:hidden\">"); } // END IF
  echo("</select></td>" . CRLF);
  
  echo("<td><select name=\"VibeElDelay_$i\" id=\"VibeDelay_$i\"");
  if (isset($VibeElType[$i])) {
    echo(">" . CRLF);
    for ($ii = 0; $ii <= 255; $ii++) {
      echo("<option value=\"".$ii."\"");
	  if ($VibeElDelay[$i]==$ii) { echo(" selected"); }
	  echo(">".($ii*10)." ms</option>" . CRLF);
    } // END FOR
  } else {
    echo("style=\"visibility:hidden\">");
  }
  echo("</select></td>" . CRLF);
  echo("<td><a href='javascript:void();' onClick=\"CheckSubmitform('Vibe_Name','RemoveVibeElement',".$VibeElID[$i].");\"><img src='./images/drop.png'/></a></td>" . CRLF);
  echo("</tr>" . CRLF);
  $i++;
} // END WHILE

if ((isset($VibeElType[$i-1])) || ($action=="AddVibe")) {
  echo("<tr><td>&nbsp;</td>" . CRLF);
  echo("<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>" . CRLF);
  echo("<td><a href=\"javascript:void(1);\" onClick=\"CheckSubmitform('Vibe_Name','AddVibeElement', ".$VibeID.");\"><img src='./images/add.png'/></a></td>" . CRLF);
  echo("</tr>" . CRLF);
} // END IF

echo("<tr><td>&nbsp;</td>" . CRLF);
echo("<td><div class=\"postcontent\">" . CRLF);
echo("  <span class=\"readmore_b\"><p>" . CRLF);
echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('Vibe_Name','SaveVibeElements', ".$VibeID.");\">".$msg["MAIN"]["Save"][$Lang]."</a></p></span>" . CRLF);
echo("  <div class=\"clear\"></div>" . CRLF);
echo("</div>" . CRLF);
echo("</td>" . CRLF);
echo("<td>&nbsp;</td>" . CRLF);
echo("<td>&nbsp;</td></tr>" . CRLF);
echo("</table><br>" . CRLF);
echo("</div>" . CRLF);
echo("</form>" . CRLF);
echo("" . CRLF);

  mysqli_close($DB);
} // End of Function Vibes