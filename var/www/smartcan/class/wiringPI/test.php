<?php

$retstatus = exec("sudo gpio -1 read 12", $Output, $retval);

echo("1. sudo gpio -1 read 12" .chr(10).chr(13));
echo("     retstatus=$retstatus, Output=$Output[0], retval=$retval, " .chr(10).chr(13));

$NewValue="0"; $intensite=0; if ($Output[0]=="0") { $NewValue = "1"; $intensite=50; echo("New Value=1= " .chr(10).chr(13));}

$retstatus = exec("sudo gpio -1 write 12 $NewValue", $Output, $retval);
echo("2. sudo gpio -1 write 12 $NewValue" .chr(10).chr(13));
echo("     retstatus=$retstatus, Output=$Output[0], retval=$retval, intensite=$intensite" .chr(10).chr(13));

	// Return Status if OK
	if ($retstatus=="") {
	
	  echo("New Intensity=$intensite" .chr(10).chr(13));
	}

?>