<?php
// Script to log User Notification Token into DB

// Source: https://www.itwonders-web.com/blog/push-notification-using-firebase-demo-tutorial
// https://www.gstatic.com/firebasejs/7.2.0/


// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Includes
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
include_once($base_URI.'/www/smartcan/www/conf/config.php');

// Connect DB
$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_select_db($DB,mysqli_DB);

// Decode json POST input
$json = file_get_contents('php://input'); 
$request = json_decode($json, true);

// Output in text file (debug)
//$file = './test.txt';
// Open the file to get existing content
//$current = file_get_contents($file);

//$current .= $json;
//$current .= $request["Token"];

$User = "user";
if (isset($_COOKIE['member_login'])) {
  $User = $_COOKIE['member_login'];
}

//$current .= " Alias = " . $_COOKIE['member_login'];
//$current .= "\n Agent: " . $_SERVER['HTTP_USER_AGENT'] ."\n";
// Fetch DB to find user and Language
$sql = "SELECT * FROM `users` WHERE Alias='" . $User . "';";
$query = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($query, MYSQLI_BOTH);
$ID = $row['ID'];
$Lang = $row['Lang'];
// Determine if already present => Update
$sql = "SELECT COUNT(*) AS County FROM `users_notification` WHERE `Alias`='" . $User . "' AND `User_Agent`='" . $_SERVER['HTTP_USER_AGENT'] . "';";
$query = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($query, MYSQLI_BOTH);
if ($row['County']!="0") {
  // Update user_notification table
  $sql = "UPDATE `users_notification` SET `Lang` = '".$Lang."', `User_Agent` = '".$_SERVER['HTTP_USER_AGENT']."' WHERE `Alias` = '".$User."' AND `Token` = '".$request["Token"]."';";
  //$current .= $sql . "\n";
  $query = mysqli_query($DB,$sql);
} else {
  // Create into user_notification table
  $sql = "INSERT INTO `users_notification` (`Alias`, `Lang`, `User_Agent`, `Token`) VALUES ('".$User."', '".$Lang."', '".$_SERVER['HTTP_USER_AGENT']."', '".$request["Token"]."');";
  //$current .= $sql . "\n";
  $query = mysqli_query($DB,$sql);
} // END IF

//$current .=  "\n";

// Write the contents back to the file
//file_put_contents($file, $current);

?>
