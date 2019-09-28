<?PHP
error_reporting(E_ALL);
ini_set('display_errors', '1');
// Includes
//include_once('../../www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function ModConfig (Admin COnfig of the Module
function InstallMod() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.InstallModule.php";

  // Action Requested via Form?  
  $action = html_postget("action");
  
  // Action Request?	
  $fileOK="";
  if ($action!="") {
    if (basename($_FILES['PackageFile']['type'])=="x-gzip") {
      //echo("UPLOAD!-=".basename($_FILES['PackageFile']['name'])."=-");
      $uploadfile = PATHUPLOAD . basename($_FILES['PackageFile']['name']);
	  if (move_uploaded_file($_FILES['PackageFile']['tmp_name'], $uploadfile)) {
		//echo("tar zxvf ".$uploadfile." -C ". PATHBASE);
		shell_exec("chmod u+s ".$uploadfile." 2>&1");
		//echo(shell_exec("sudo /bin/tar -zxf ".$uploadfile." -C ". PATHBASE . "  2>&1"));
		if (!shell_exec("sudo /bin/tar zxvf ".$uploadfile." -C ". PATHBASE . "  2>&1")) {
		  $fileOK="NOK";
		} else {
		  // SQL File present in Extracted files
		  $ScanDir  = scandir(PATHUPLOAD);
		  $ndir=0;
		  while (isset($ScanDir[$ndir+2])) {
		    //echo("Dir=".$ScanDir[$ndir+2]."<br>");
			if (substr($ScanDir[$ndir+2],-4)==".sql") { importDB(PATHUPLOAD."/".$ScanDir[$ndir+2]); unlink(PATHUPLOAD."/".$ScanDir[$ndir+2]); }
			$ndir++;
		  } // END WHILE
		  $fileOK="OK";
		  system("rm ".$uploadfile);
		} // ENDIF
	 } else {
        echo "<font size=4 color=red><b>".$msg["MAIN"]["FileCopyError"][$Lang]."!!!</b></font>\n";
     } // ENDIF
    } else {
      echo "<font size=4 color=red><b>".$msg["MAIN"]["IncorrectFileType"][$Lang]."!!!</b></font>\n";
    } // ENDIF
  } // ENDIF

  // Start Build Page ...
  echo("<h2 class='title'> ".$msg["INSTALLMODULE"]["InstallModule"][$Lang]." </h2>");
  echo("<div class='post_info'>&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
		
  echo("<style>" . CRLF);
  echo("<" . CRLF);
  echo("img" . CRLF);
  echo("{" . CRLF);
  echo("position:relative;" . CRLF);
  echo("}" . CRLF);
  echo("</style>" . CRLF);

  if ($fileOK=="OK") {
    echo ("<h2>".$msg["INSTALLMODULE"]["InstallSucessful"][$Lang]."!</h2>");
	echo("<script type=\"text/javascript\">" . CRLF);
	echo("setTimeout(function () { window.location.href = \"/smartcan/admin/index.php?page=Modules&SubMenu=Install\"; }, 1500);setTimeout(function () { window.location.reload(); }, 122000);" . CRLF);
	echo("</script>" . CRLF);
  } // ENDIF
  if ($fileOK=="NOK") {
    echo ("<h2><font color=red><b>".$msg["INSTALLMODULE"]["IncompressError"][$Lang]."!!!</font></h2>");
  } // ENDIF
  
  echo("<form name='ChangeVariables' id='ChangeVariables' enctype='multipart/form-data' action='./index.php?page=Modules&SubMenu=Install' method='post'>" . CRLF);
  echo("<input type='hidden' name='action' id ='action' value=''/><input type='hidden' name='MAX_FILE_SIZE' value='30000000' />" . CRLF);
  echo("<table>" . CRLF);

  // File Upload
  echo("<tr><td>&nbsp;</td><td>&nbsp;</td></tr>");
  echo("<tr><td colspan=2 align=middle><b>".$msg["INSTALLMODULE"]["ModuleToLoad"][$Lang].":</b></td></tr>");
  echo("<tr><td>&nbsp;</td><td><input name='PackageFile' type='file' /></td></tr>");
  
  echo("<tr><td colspan=2 align=middle><a href='javascript:submitform(\"Upload\")'><img src='./images/upload.png' width='64px' heigth='64px' /></a></td></tr>");
  
  echo("</table>" . CRLF);
  echo("</form>" . CRLF);

  echo("<br><div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

  
  echo("<body>" . CRLF);
  echo("<div id='data'></div>" . CRLF);
  echo("</body>" . CRLF);
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
  

  echo("</ul></div>" . CRLF);
  echo("<div class='postcontent'>" . CRLF);
  echo("<div class='clear'></div>" . CRLF);
  echo("</div>" . CRLF);
  echo("<input type='hidden' name='action' value='' />" . CRLF);
  echo("</div>" . CRLF);
  echo("<script type='text/javascript'>" . CRLF);
  echo("function submitform(action) {" . CRLF);
  echo("  //alert('submit + Action='+action);" . CRLF);
  echo("  document.ChangeVariables.action.value = action;" . CRLF);
  echo("  document.ChangeVariables.submit();" . CRLF);
  echo("}" . CRLF);
  echo("</script>" . CRLF);

  //mysqli_close($DB);
} // End of Function InstallMod
