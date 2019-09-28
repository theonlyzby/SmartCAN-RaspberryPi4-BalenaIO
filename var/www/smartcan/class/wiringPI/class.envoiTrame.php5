<?php

class wiringPI_envoiTrame {

  /* TOGGLE Raspberry GPIO Output */
  function GPIO_Set($var1,$var2,$intensite,$progression) {
	if ((($intensite>0) && ($var1=="HIGH")) || (($intensite==0) && ($var1=="low")) ) { $Value = "1"; } else { $Value = "0"; }
	//$intensite="0x00"; 
	if ($intensite>0) { $intensite="0x32"; }
    // Set Value ON or OFF
	$retstatus = exec('gpio -1 write '.$var2.' '.$Value, $Output, $retval);
        echo("var1=$var1,var2=$var2,intens=$intensite,prog=$progression / exec('gpio -1 write '.$var2.' '.$Value)". CRLF);
	// Return Status if OK
	if ($retstatus=="") {
	  $trigger = new trigger();
	  $trigger->OUTtrigger("wiringPI", $var1, $var2, $intensite);
	} // END IF

  } // END FUNCTION
  
  
  /* TOGGLE Raspberry GPIO Output */
  function GPIO_Toggle($var1,$var2,$intensite,$progression) {
    // Determine GPIO Output Status ON or OFF
	$retstatus = exec("gpio -1 read $var2", $Output, $retval);
        echo('exec("gpio -1 read $var2", $Output, $retval)' . CRLF);
	$NewValue="0"; $intensite="0x0"; if ($retstatus=="0") { $NewValue = "1"; $intensite="0x32";}
	// Set new Value (Opposite)
	$retstatus = exec("gpio -1 write ".$var2." ".$NewValue, $Output, $retval);
	// Return Status if OK
	if ($retstatus=="") {
	  $trigger = new trigger();
	  $trigger->OUTtrigger("wiringPI", $var1, $var2, $intensite);
	} // END IF
	
	
  } // END FUNCTION
  
} // END CLASS
?>
