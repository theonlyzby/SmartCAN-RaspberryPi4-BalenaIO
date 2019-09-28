<?php
$autoFocus = "autofocus";
$_XTemplate->assign('ERROR_MSG', $div_sess);
if(isset($_COOKIE["member_login"])) { $_XTemplate->assign('USER_NAME', $_COOKIE["member_login"]); $Remember = " checked "; $autoFocus = ""; }
if(isset($_COOKIE["member_password"])) { $_XTemplate->assign('PASSWORD', $_COOKIE["member_password"]); }
$_XTemplate->assign('AUTOFOCUS', $autoFocus);
$_XTemplate->assign('REMEMBER', $Remember);
?>
