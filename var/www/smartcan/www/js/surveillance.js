<script type="text/javascript">
  function showcam() {
	alert("YES!");
	//document.getElementById(“getcam”).html="<img src='http://172.27.10.83/smartcan/www/cam-submit.php' />";
  }
  
  function SubmitForm(action,val1,val2) {
    //alert("Submit + Action="+action);
	document.Surveillance.action.value = action;
	document.Surveillance.val1.value    = val1;
	document.Surveillance.val2.value    = val2;
	document.Surveillance.submit();

}

</script>
