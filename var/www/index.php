<html>
<head>
 <meta http-equiv="content-Type" content="text/html; charset=utf-8" />
 <meta http-equiv="Cache-Control" content="no-cache">
 <meta http-equiv="Pragma" content="no-cache">
 <meta http-equiv="Expires" content="0">

 <?php
 // CONFIGURATIONS ET DEPENDANCES
 include_once './smartcan/www/conf/config.php';
 // Connect DB
 $DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
 mysqli_set_charset('utf8',$DB); 
 mysqli_select_db($DB,mysqli_DB);

 // Landing page?
 $sql          = "SELECT * FROM `ha_settings` WHERE `variable` = 'landing_page';";
 $query        = mysqli_query($DB,$sql);
 $row          = mysqli_fetch_array($query, MYSQLI_BOTH);
 $Landing_Page = $row['value'];
 if ($Landing_Page=="1") {
   // YES
   // 
   // First Visit? => Admin Page
   $sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
   $query            = mysqli_query($DB,$sql);
   $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
   $First_Use_Admin = $row['value'];
 
   // Page content
   echo("<title>SmartCAN</title>".$CRLF);
   if ($First_Use_Admin!="0") {
     // Redirect to Admin Page
	 echo("<meta http-equiv=\"refresh\" content=\"0; URL=/smartcan/admin\">".$CRLF);
   } else {
     // Redirect to User Page after 15 Sec
     echo("<meta http-equiv=\"refresh\" content=\"15; URL=/smartcan/www\">".$CRLF);
   } // END IF
 } // END IF
 ?>
</head>
  <body bgcolor="black">
    <?php
	if ($Landing_Page=="1") {
	  // YES
	  ?>
    <div class="conteneur" style="z-index: 1;">
      <div class="entete">
        <h2 align="middle" style="color: #00B7EB;"><br><br><br><br><br>
		<?php
		$PHPMySQL_URL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$PHPMySQL_URL .= "s";}
		$PHPMySQL_URL .= "://";
		$PHPMySQL_URL .= $_SERVER["HTTP_HOST"].":".(intval($_SERVER["SERVER_PORT"])+1);
		//echo("URL = ".$PHPMySQL_URL."<br>");
		
		if ($First_Use_Admin!="1") {		
		  ?>
          Dans 15 secondes, vous allez &ecirc;tre automatiquement redirig&eacute;s vers l'Interface utilisateur de <a href="/smartcan/www"><font color="FFFFFF">SmartCAN</font></a><br>
          <br><br><br><br><br><br>
          A moins que vous pr&eacute;f&eacute;riez?<br><br>
          <a href="/smartcan/admin"><font color="FFFFFF">l'Interface d'Administration de SmartCAN</font></a><br><br>
          <a href="<?php echo($PHPMySQL_URL);?>"><font color="FFFFFF">phpMyAdmin</font></a><br>

		  <?php
		} else {
		  echo("Redirection vers l'interface de configuration en cours...<br><br><b>Utilisez un profil Administrateur! ;-)</b><br>");
		} // END IF
		?>
        </h2>

    </div></div>
	<?php
	} else {
	  // NO
	  echo("&nbsp;");
	} // END IF
	?>
</body>

</html>