<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr" lang="fr-FR" />

<META HTTP-EQUIV="expires" CONTENT="Wed, 09 Aug 2000 08:21:57 GMT" />
<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php 
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Includes
include_once '../www/conf/config.php';
include_once './lang/admin.index.php';

echo("<title>".$msg["MAIN"]["title"]["en"]."</title>");
?>
	<!-- WebApp Declarations -->
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" /> 
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

	<!-- Icons -->
	<!-- All Browser ico=32*32, png=72*72 -->
	<link rel="icon" href="../www/images/icons/favicon.ico" type="image/x-icon"/>
	<link rel="icon" href="../www/images/icons/favicon.png" type="image/x-icon"/>
	<!-- Android -->
	<link href="../www/images/icons/icon-196x196.png"                                   rel="shortcut icon" sizes="196x196" />
	<!-- iOS -->
	<link href="../www/images/icons/apple-touch-icon.png"                      rel="apple-touch-icon" />
	<link href="../www/images/icons/apple-touch-icon-57x57.png"                rel="apple-touch-icon" sizes="57x57" />
	<link href="../www/images/icons/apple-touch-icon-72x72.png"                rel="apple-touch-icon" sizes="72x72" />
	<link href="../www/images/icons/apple-touch-icon-76x76.png"                rel="apple-touch-icon" sizes="76x76" />
	<link href="../www/images/icons/apple-touch-icon-114x114.png"              rel="apple-touch-icon" sizes="114x114" />
	<link href="../www/images/icons/apple-touch-icon-114x114-precomposed.png"  rel="apple-touch-icon-precomposed" sizes="114x114" />
	<link href="../www/images/icons/apple-touch-icon-120x120.png"              rel="apple-touch-icon" sizes="120x120" />
	<link href="../www/images/icons/apple-touch-icon-144x144.png"              rel="apple-touch-icon" sizes="144x144" />
	<link href="../www/images/icons/apple-touch-icon-152x152.png"              rel="apple-touch-icon" sizes="152x152" />
	<link href="../www/images/icons/apple-touch-icon-180x180.png"              rel="apple-touch-icon" sizes="180x180" />
	<link href="../www/images/icons/icon-hires.png"                            rel="icon" sizes="192x192" />
	<link href="../www/images/icons/icon-normal.png"                           rel="icon" sizes="128x128" />
	
	<!-- Load Images -->
	<!-- iPhone -->
	<link rel="apple-touch-startup-image"       media="(device-width: 320px)"	href="../www/images/icons/apple-touch-startup-image-320x460.png" />
	<!-- iPhone (Retina) -->
	<link rel="apple-touch-startup-image"		media="(device-width: 320px) and (-webkit-device-pixel-ratio: 2)" href="../www/images/icons/apple-touch-startup-image-640x920.png" />
	<!-- iPad (portrait) -->
	<link rel="apple-touch-startup-image"       media="(device-width: 768px) and (orientation: portrait)" href="../www/images/icons/apple-touch-startup-image-768x1004.png" />
	<!-- iPad (landscape) -->
	<link rel="apple-touch-startup-image"       media="(device-width: 768px) and (orientation: landscape)" href="../www/images/icons/apple-touch-startup-image-748x1024.png" />
	<!-- iPad (Retina, landscape) -->
	<link rel="apple-touch-startup-image"       media="(device-width: 768px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)" href="../www/images/icons/apple-touch-startup-image-1496x2048.png" />  


<link rel="stylesheet" href="./reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="./style.css" type="text/css" media="screen" />
<link rel='stylesheet' id='et-shortcodes-css-css'  href='./shortcodes.css?ver=1.6' type='text/css' media='all' />


<script type='text/javascript' src='./js/jquery.easy-slider.js?ver=3.3.1'></script>
<script type='text/javascript' src='./js/jquery-1.11.3.min.js?ver=1.4.2'></script>





<?php
// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Includes
include_once './Modules/mod.Lamps.php';
include_once './Modules/mod.Therm.php';
include_once './Modules/mod.Outputs.php';
include_once './Modules/mod.Temps.php';
include_once './Modules/mod.TempGraphs.php';
include_once './Modules/mod.Vibes.php';
include_once './Modules/mod.Variables.php';
include_once './Modules/mod.Surveillance.php';
include_once './Modules/mod.CamConfig.php';
include_once './Modules/mod.BackupRestore.php';
include_once './Includes/func.misc.php';

// Connect DB
$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_set_charset($DB,'utf8'); 
mysqli_select_db($DB,mysqli_DB);

// Variables
//phpinfo();
if ($_SERVER['DOCUMENT_ROOT']=="/data/www") {
   //echo("Balena");
   $Linux_Mode = "balena.io";
} else {
   //echo("Linux");
   $Linux_Mode = "native";
} // ENDIF
  
// Security
$Access_Level = 0;     // => Visitor
$PassOK=0;            // Password NOK
// Authentication
session_start();
if(isset($_GET['logout']))
 {
   unset($_SESSION["login"]);
   session_destroy();
   //echo "<font color='white' size='16pt'>Acc&egrave;s Interdit ... ";
   //echo "[<a style='color:#ffffff; font-style: bold; size: 16pt;' href='" . $_SERVER['PHP_SELF'] . "'>Login</a>]</font>";
   //exit;
 }

 
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || !isset($_SESSION["login"]))
 {
   header("WWW-Authenticate: Basic realm=\"" . $msg["MAIN"]["title"]["en"] . "\"");
   header("HTTP/1.0 401 Unauthorized");
   //session_destroy();
   $_SESSION["login"] = true;
   echo "<font color='white' size='16pt'>".$msg["MAIN"]["forbidden"]["en"]." ... ";
   echo "[<a style='color:#ffffff; font-style: bold; size: 16pt;' href='" . $_SERVER['PHP_SELF'] . "'>Login</a>]</font>";
   //exit;
 }
 else
 {
   $SubmitUser = $_SERVER['PHP_AUTH_USER'];
   $SubmitPass = $_SERVER['PHP_AUTH_PW'];
   $sql = "SELECT COUNT(*) AS PassOK FROM `users` WHERE (Alias='" .
          $SubmitUser . "' AND Password=PASSWORD('". $SubmitPass ."'));";
   $query = mysqli_query($DB,$sql);
   $row = mysqli_fetch_array($query, MYSQLI_BOTH);
   $PassOK = $row['PassOK'];
   $sql = "SELECT * FROM `users` WHERE (Alias='" .
          $SubmitUser . "' AND Password=PASSWORD('". $SubmitPass ."'));";
   $query = mysqli_query($DB,$sql);
   $row = mysqli_fetch_array($query, MYSQLI_BOTH);
   if (isset($row['Lang'])) { 
     $Lang = $row['Lang']; 
   } else {
     $Lang="";
	 $sql2 = "ALTER TABLE `users` ADD `Lang` VARCHAR(7) NOT NULL AFTER `Last_Name`;";
	 $query = mysqli_query($DB,$sql2);
   } // END IF
   if ($Lang=="") { $Lang="en";}
   //echo("Lang=$Lang<br>");
   //echo("PassOK=".$PassOK. ", sql= ".$sql);unset($_SESSION["login"]); session_destroy(); exit();
   
   if($PassOK==1)
   {
     //echo "You have logged in ... ";
	 $Access_Level = 8;
     //echo "[<a href='" . $_SERVER['PHP_SELF'] . "?logout'>Logout</a>]";
   }
   else
   {
     unset($_SESSION["login"]);
	 session_destroy();
     header("Location: " . $_SERVER['PHP_SELF']);
   }
 }
$Lang="fr"; 
// Starts HTML Body
echo("</head><body class='home blog ie'>" . CRLF); 

// Secure Origin?
echo("<script type='text/javascript'>" . CRLF);
echo("  $(document).ready(function() {" . CRLF);
echo("    if (window.location.protocol != 'https:') {" . CRLF);
echo("	  //alert('NOT https');" . CRLF);
echo("	  location.href = location.href.replace('http://', 'https://');" . CRLF);
echo("    }" . CRLF);
echo("  });" . CRLF);
echo("</script>" . CRLF);

echo("<div id='top'>" . CRLF);
echo("<div id='header'>" . CRLF);
echo("<p><h1 align='center'><font color='white'><b>" . $msg["MAIN"]["title"][$Lang] . "</b></font></h1></p>" . CRLF);

// Acess Level OK?
if ($Access_Level>=1) {

	// On Which Page?
	$Html_Page    = html_postget("page");
	$Html_SubPage = html_postget("SubMenu");
	
	// Modules Installed
	$dir          = PATHWEBADMIN . "Manufacturers";
	$ManufactDir  = scandir($dir);
	$ndir = 0;
	while (isset($ManufactDir[$ndir+2])) { $ndir++; }
	
	// Action Requested via Form?  
    $action = html_postget("action");
	// Pass File Parsing (NO DOmoCAN or do later)
    if ($action=="Pass_Sysmap") {
	  //include_once './Manufacturers/DomoCAN3.Config.php';
	  //
	  // Increase Admin First visit ... next step
	  //
	  $sql = "UPDATE `domotique`.`ha_settings` SET `value` = '2' WHERE `ha_settings`.`variable` = 'first_use_admin';";
	  $query = mysqli_query($DB,$sql);
	  $First_Use_Admin = "2";
	} // END IF
	// First Admin Use?
	$sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
    $query            = mysqli_query($DB,$sql);
    $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
    $First_Use_Admin = $row['value'];
	//
	if ($First_Use_Admin=="1") { $Html_Page="DomoCAN"; include_once './Manufacturers/DomoCAN3.Config.php';}
	if ($First_Use_Admin=="2") { $Html_Page="wiringPI"; include_once './Manufacturers/wiringPI.Config.php';}
	if ($First_Use_Admin=="3") { $Html_Page="Therm";}
	if ($First_Use_Admin=="4") { $Html_Page="Variables";}

	// Build top menu
	$Top_Menu = array();
	$Top_Menu["Tag"][1] = "Status";       $Top_Menu["URL"][01] = "./index.php?page=Status";
	$Top_Menu["Tag"][2] = "";             $Top_Menu["URL"][2] = "#";
	$Top_Menu["Tag"][3] = "Therm";        $Top_Menu["URL"][3] = "./index.php?page=Therm";
	$Top_Menu["Tag"][4] = "Lamps";        $Top_Menu["URL"][4] = "./index.php?page=Lamps";
	$Top_Menu["Tag"][5] = "Temps";        $Top_Menu["URL"][5] = "./index.php?page=Temps";
	$Top_Menu["Tag"][6] = "Vibes";        $Top_Menu["URL"][6] = "./index.php?page=Vibes";
	$Top_Menu["Tag"][7] = "Surveillance"; $Top_Menu["URL"][7] = "./index.php?page=Surveillance";
    $Top_Menu["Tag"][8] = "Outputs";      $Top_Menu["URL"][8] = "./index.php?page=Outputs";	
	$Top_Menu["Tag"][9] = "Logics";       $Top_Menu["URL"][9] = "./index.php?page=Logics";
	
	$Top_Menu["Tag"][10]= "Modules";      $Top_Menu["URL"][10]= "#";                      if ($ndir<1) { $Top_Menu["Tag"][10]= ""; $Top_Menu["URL"][10]= "";}
	$Top_Menu["Tag"][11]= "Admin";        $Top_Menu["URL"][11]= "./index.php?page=Admin";

	$Top_SubMenu = array();
	$i=0;$j=0;
	while ($ndir>$i) {
	  if (strpos($ManufactDir[$i+2],"Config.php")!=false) {
	    $Top_SubMenu[10]["Text"][($j+1)] = substr($ManufactDir[$i+2],0,strpos($ManufactDir[$i+2],"."));   $Top_SubMenu[10]["URL"][($j+1)] = "./index.php?page=Modules&SubMenu=".($i+1); $Top_SubMenu[10]["JAVA"][($j+1)] = "";$j++;
	  }
	  $i++;
	} // END While
	$Top_SubMenu[10]["Text"][($i+1)] = "&nbsp;";             $Top_SubMenu[10]["URL"][($i+1)] = "#";                                        $Top_SubMenu[10]["JAVA"][($i+1)] = "";
	$Top_SubMenu[10]["Text"][($i+2)] = $msg["INSTALLMOD"][11][$Lang];     $Top_SubMenu[10]["URL"][($i+2)] = "./index.php?page=Modules&SubMenu=Install"; $Top_SubMenu[10]["JAVA"][($i+2)] = "";
	
	$Top_SubMenu[11]["Text"][01] = $msg["ADMINMENU"][1][$Lang]; $Top_SubMenu[11]["URL"][01] = "#";                                            $Top_SubMenu[11]["JAVA"][01] = "ConfirmAction(\"ReBoot\",\"".$msg["CONFIRM"]["REBOOT"][$Lang]."\");";
	$Top_SubMenu[11]["Text"][02] = $msg["ADMINMENU"][2][$Lang]; $Top_SubMenu[11]["URL"][02] = "./index.php?page=Admin&SubMenu=2";             $Top_SubMenu[11]["JAVA"][02] = "ConfirmAction(\"ShutDown\",\"".$msg["CONFIRM"]["SHUTDOWN"][$Lang]."\");";
	$Top_SubMenu[11]["Text"][03] = "&nbsp;";                    $Top_SubMenu[11]["URL"][03] = "./index.php?page=Admin&SubMenu=3";             $Top_SubMenu[11]["JAVA"][03] = "";
	$Top_SubMenu[11]["Text"][04] = $msg["ADMINMENU"][4][$Lang]; $Top_SubMenu[11]["URL"][04] = "./index.php?page=TempGraphs";                  $Top_SubMenu[11]["JAVA"][04] = "";
	$Top_SubMenu[11]["Text"][05] = $msg["ADMINMENU"][5][$Lang]; $Top_SubMenu[11]["URL"][05] = "./index.php?page=Variables";                   $Top_SubMenu[11]["JAVA"][05] = "";
	$Top_SubMenu[11]["Text"][06] = $msg["ADMINMENU"][6][$Lang]; $Top_SubMenu[11]["URL"][06] = "./index.php?page=BackupRestore";               $Top_SubMenu[11]["JAVA"][06] = "";
	$Top_SubMenu[11]["Text"][07] = $msg["ADMINMENU"][7][$Lang]; $Top_SubMenu[11]["URL"][07] = "./About-SmartCAN.php' target='_blank";         $Top_SubMenu[11]["JAVA"][07] = "";
	$Top_SubMenu[11]["Text"][8] = $msg["ADMINMENU"][8][$Lang]; $Top_SubMenu[11]["URL"][8] = $_SERVER['PHP_SELF'] . "?logout";               $Top_SubMenu[11]["JAVA"][8] = "";


	echo("<!-- Start Menu -->" . CRLF);

	echo("<ul id='menu-smartcan' class='sf-menu'>" . CRLF);

	$i = 1; $MenuCount = 16600; $SubCount = 25898;
	while (isset($msg["TOPMENU"][$i][$Lang])) {
	  $Selected = ""; if ($Html_Page == $Top_Menu["Tag"][$i]) { $Selected = " selectedLava"; }
	  echo("<li id='menu-item-" . $MenuCount . "' class='menu-item menu-item-type-taxonomy menu-item-object-category menu-item-" .
		   $MenuCount . $Selected .
		   "'><a href='" . $Top_Menu["URL"][$i] . "' >" . $msg["TOPMENU"][$i][$Lang] . "</a>");
	  $j = 1;
	  
	  if (isset($Top_SubMenu[$i]["Text"][1])) { echo(CRLF . "<ul class='sub-menu'>" . CRLF); } else { echo("</li>" . CRLF);} // End If
	  // --- SubMenus ---
	  while (isset($Top_SubMenu[$i]["Text"][$j])) {
		echo("<li id='menu-item-" . $SubCount . "' class='menu-item menu-item-type-post_type menu-item-object-page menu-item-" . $SubCount . "'><a ");
		if ($Top_SubMenu[$i]["URL"][$j]!="#") { echo("href='" . $Top_SubMenu[$i]["URL"][$j] ."' ");} else { echo("href='#' "); }
		if ($Top_SubMenu[$i]["JAVA"][$j]!="") { echo("onclick='javascript:" . $Top_SubMenu[$i]["JAVA"][$j] . "return false;'"); }
		echo(">" . $Top_SubMenu[$i]["Text"][$j] . "</a></li>" . CRLF);
		$SubCount++;
		$j++;
	  } // End While
	  if (isset($Top_SubMenu[$i]["Text"][1])) { echo("</ul></li>" . CRLF); $MenuCount = $SubCount; }
	  $MenuCount++; $i++;
	} // End While

	echo("</ul>" . CRLF);
	
	echo("<form name='admin' method='post' id='admin'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."'>" . CRLF);
    echo("<input type='hidden' name='page' value='Admin'/>" . CRLF);
	echo("<input type='hidden' name='SubMenu' value=''/>" . CRLF);
	echo("</form>" . CRLF);

	echo("			<!-- End Menu -->	" . CRLF);
	echo("<div id='wrap'>" . CRLF);
	echo("<!-- Main Content-->" . CRLF);
	echo("	<img src='./images/content-top.gif' alt='content top' class='content-wrap' />" . CRLF);
	echo("	<div id='content'>" . CRLF);
	echo("		<!-- Start Main Window -->" . CRLF);
	echo("		<div id='main'>" . CRLF);
	echo("<!-- Contenu -->" . CRLF);
	echo("<div class='new_post'>" . CRLF);
		
	// --- Main Page Content
	if ($Html_Page=="DomoCAN")       { ModConfig(); }
	if ($Html_Page=="wiringPI")      { ModConfig(); }
	if ($Html_Page=="Temps")         { Temps(); }
	if ($Html_Page=="TempGraphs")    { TempGraphs(); }
	if ($Html_Page=="Therm")         { Therm(); }
	if ($Html_Page=="Lamps")         { Lamps(); }
	if ($Html_Page=="Outputs")       { Outputs(); }
	if ($Html_Page=="Vibes")         { Vibes(); }
	if ($Html_Page=="Variables")     { Variables(); }
	if ($Html_Page=="Surveillance")  { Surveillance(); }
	if ($Html_Page=="CamConfig")     { CamConfig(); }
	if ($Html_Page=="BackupRestore") { BackupRestore(); }
	if ($Html_Page=="Modules")       { 
	  //echo($ManufactDir[$Html_SubPage+1]); 
	  if ($Html_SubPage=="Install") {
	    include_once("./Modules/mod.InstallModule.php");
	    InstallMod();
	  } else {
	    include_once($dir."/".$ManufactDir[$Html_SubPage+1]);
	    ModConfig();
	  } // ENDIF
	}
	
	if (($Html_Page=="Admin") && ($Html_SubPage=="ReBoot")) {
	  // Reboot System
	  echo("<h2 class='title' align='middle'>System REBOOTing !<br><br>");
	  echo($msg["MAIN"]["bepatient"]["en"]." ... <div style='display:inline' id=\"compterebours\">".$msg["MAIN"]["reload"]["fr"]."<br><br>&nbsp;</h2>");
	  echo("<script type=\"text/javascript\">" . CRLF);
	  echo("var decompte = 120;" . CRLF);
	  echo("setTimeout(\"compte_a_rebours();\", 1000);" . CRLF);
	  echo("function compte_a_rebours() {" . CRLF);
	  echo("  document.getElementById(\"compterebours\").innerHTML = decompte;" . CRLF);
	  echo("  decompte--;" . CRLF);
	  echo("  var actualisation = setTimeout(\"compte_a_rebours();\", 1000);" . CRLF);
	  echo("}" . CRLF);
	  echo("setTimeout(function () { window.location.href = \"/smartcan/admin\"; }, 120000);setTimeout(function () { window.location.reload(); }, 122000);" . CRLF);
	  echo("</script>" . CRLF);
	  //unset($_SESSION["login"]);
      //session_destroy();
	  if ($Linux_Mode=="balena.io") { 
		shell_exec('curl -X POST --header "Content-Type:application/json" --data \'{"force": true}\' "$BALENA_SUPERVISOR_ADDRESS/v1/update?apikey=$BALENA_SUPERVISOR_API_KEY"');
	  } else {
		shell_exec("sudo /sbin/shutdown -r now");
	  } // END IF
	} // END IF
	
	if (($Html_Page=="Admin") && ($Html_SubPage=="ShutDown")) {
	  // Reboot System
	  echo("<h2 class='title' align='middle'>".$msg["MAIN"]["shutdown"][$Lang]."!<br><br>");
	  echo("<br>&nbsp;</h2>");
	  if ($Linux_Mode=="balena.io") { 
		shell_exec('curl -X POST --header "Content-Type:application/json" --data \'{"force": true}\' "$BALENA_SUPERVISOR_ADDRESS/v1/update?apikey=$BALENA_SUPERVISOR_API_KEY"');
	  } else {
	    shell_exec("sudo /sbin/shutdown -h now");
	  } // END IF
	} // END IF
	
	if (($Html_Page=="") OR ($Html_Page=="Status")) {
	  ?>
	  <div id="PCInt" style="z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 965px; height: 560px; border-width: 2px; border-style: solid; border-color: #000000;">
	  <iframe width="966" height="560" src="../www/pc.html"></iframe>
	  </div>
	  
	  <script type="text/javascript">
	    window.onload=function showOverlay() {
		  var divID = 'PCInt';
		  var o = document.getElementById(divID);
		  SurImpose('main',divID);
		}

		function SurImpose(Ref,Obj) {
		  oElement = document.getElementById(Ref);
		  ToMove =  document.getElementById(Obj);
		  var iReturnValue = 0; 
		  while( oElement != null ) {
			iReturnValue += oElement.offsetTop;
			oElement = oElement.offsetParent;
		  }
		  ToMove.style.top = (iReturnValue-10)+"px";
		  oElement = document.getElementById('header');
		  iReturnValue = 0; 
		  while( oElement != null ) {
			iReturnValue += oElement.offsetLeft;
			oElement = oElement.offsetParent;
		  }
		  ToMove.style.left = (iReturnValue-5)+"px";
		  return true;
		}

	  </script>
	  <?php
	} //End If
	?>

	</div>
		<!-- End Content -->
		<img src="./images/content-bottom.gif" alt="content top" class="content-wrap" />

	<?php
	//echo("HTML Page = $Html_Page");
	if ($Html_Page=="CamConfig2")     {
	?>
	<script src="./js/jquery.lavalamp.1.3.3-min.js" type="text/javascript" charset="utf-8"></script>
	<script src="./js/jquery.cycle.all.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="./js/superfish.js" type="text/javascript" charset="utf-8"></script>   
	<script src="./js/jquery.easing.1.3.js" type="text/javascript" charset="utf-8"></script>

	<SCRIPT type="text/javascript">
	  function ConfirmAction(action,msg) {
        if (confirm("<?php echo($msg["MAIN"]["javacfrm1"][$Lang]);?> "+msg+" <?php echo($msg["MAIN"]["javacfrm2"][$Lang]);?> ?")) {
           document.forms["admin"].elements["SubMenu"].value = action;
		   document.forms["admin"].submit();
        }
      }
	</SCRIPT>

	<script type="text/javascript">
	//<![CDATA[
	 
	jQuery(function(){

			jQuery.noConflict();
		
			jQuery('ul.sf-menu').superfish({
				delay:       200,                            // one second delay on mouseout 
				animation:   {'marginLeft':'0px',opacity:'show',height:'show'},  // fade-in and slide-down animation 
				speed:       'fast',                          // faster animation speed 
				autoArrows:  true,                           // disable generation of arrow mark-up 
				onBeforeShow:      function(){ this.css('marginLeft','20px'); },
				dropShadows: false                            // disable drop shadows 
			});
			
			jQuery('ul.sf-menu ul > li').addClass('noLava');
			jQuery('ul.sf-menu > li').addClass('top-level');
			
			jQuery('ul.sf-menu > li > a.sf-with-ul').parent('li').addClass('sf-ul');
			
			jQuery("ul.sf-menu > li > ul").prev("a").attr("href","#");		
			if (!(jQuery("#footer_widgets .block_b").length == 0)) {
				jQuery("#footer_widgets .block_b").each(function (index, domEle) {
					// domEle == this
					if ((index+1)%3 == 0) jQuery(domEle).after("<div class='clear'></div>");
				});
			};
			
			/* search form */
			
			jQuery('#search').toggle(
				function () {jQuery('#searchbox').animate({opacity:'toggle', marginLeft:'-210px'},500);},
				function () {jQuery('#searchbox').animate({opacity:'toggle', marginLeft:'-200px'}, 500);}
			);
			
			var $searchinput = jQuery("#header #searchbox input");
			var $searchvalue = $searchinput.val();
			
			$searchinput.focus(function(){
				if (jQuery(this).val() == $searchvalue) jQuery(this).val("");
			}).blur(function(){
				if (jQuery(this).val() == "") jQuery(this).val($searchvalue);
			});
			
		
			jQuery('ul.sf-menu li ul').append('<li class="bottom_bg noLava"></li>');
			
			var active_subpage = jQuery('ul.sf-menu ul li.current-cat, ul.sf-menu ul li.current_page_item').parents('li.top-level').prevAll().length;
			var isHome = 1; 
			
			if (active_subpage) jQuery('ul.sf-menu').lavaLamp({ startItem: active_subpage });
			else if (isHome === 1) jQuery('ul.sf-menu').lavaLamp({ startItem: 0 });
			else jQuery('ul.sf-menu').lavaLamp();
				
			
						
				/* featured slider */
				
				jQuery('#spotlight').cycle({
					timeout: 0,
					speed: 1000, 
					fx: 'cover'
				});
				
				var $featured_item = jQuery('div.featitem');
				var $slider_control = jQuery('div#f_menu');
				var ordernum;
				var pause_scroll = false;
				var $featured_area = jQuery('div#featured_content');			
		 
				function gonext(this_element){
					$slider_control
					.children("div.featitem.active")
					.removeClass('active');
					this_element.addClass('active');
					ordernum = this_element.find("span.order").html();
					jQuery('#spotlight').cycle(ordernum - 1);
				} 
				
				$featured_item.click(function() {
					clearInterval(interval);
					gonext(jQuery(this)); 
					return false;
				});
				
				jQuery('a#previous, a#next').click(function() {
					clearInterval(interval);
					if (jQuery(this).attr("id") === 'next') {
						auto_number = $slider_control.children("div.featitem.active").prevAll().length+1;
						if (auto_number === $featured_item.length) auto_number = 0;
					} else {
						auto_number = $slider_control.children("div.featitem.active").prevAll().length-1;
						if (auto_number === -1) auto_number = $featured_item.length-1;
					};
					gonext($featured_item.eq(auto_number));
					return false;
				});

							
					$featured_area.mouseover(function(){
						pause_scroll = true;
					}).mouseout(function(){
						pause_scroll = false;
					});
					
				
				var auto_number;
				var interval;
				
				$featured_item.bind('autonext', function autonext(){
					if (!(pause_scroll)) gonext(jQuery(this)); 
					return false;
				});
				
								interval = setInterval(function () {
						auto_number = $slider_control.find("div.featitem.active span.order").html();
						if (auto_number == $featured_item.length) auto_number = 0;
						$featured_item.eq(auto_number).trigger('autonext');
					}, 6000);
						
			});
	//]]>
	</script>
	<script type="text/javascript">
		  /* <![CDATA[ */
		  jQuery('div.gallery').easyslider({
				style:'fadein',
				showloading:true,
				replacegallery:false,
				gallerystyle:'default'
		  });
		  /* ]]> */
	</script>
<?php
	} // END IF
} // End IF	
echo("</body>".CRLF);
echo("</html>".CRLF);
