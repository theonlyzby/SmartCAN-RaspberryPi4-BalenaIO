<script type="text/javascript">

function receptionmodule(cle,valeur) {

  if ( cle == 'sonde' ) {
    sonde(valeur);
  }
  if ( cle == 'temperaturevoulue' ) {
    temperaturevoulue(valeur);
  }
  if ( cle == 'heater' ) {
    heater(valeur);
  }
  if ( cle == 'boiler' ) {
    boiler(valeur);
  }
  if ( cle == 'PERIODECHAUFFE' ) {
    periodechauffe(valeur);
  }
  if ( cle == 'FINCHAUFFE' ) {
    finchauffe(valeur);
  }
  if ( cle == 'PROCHAINECHAUFFE' ) {
    prochainechauffe(valeur);
  }
};

function sonde(data) {
  tab = data.split(',');
  idsonde = tab[0];
  valeur = tab[1];
  if (idsonde=="moyennemaison") { idsonde = "moyenne"; }
  $('#' + idsonde).text(valeur);
  //console.log("JS/"+idsonde+"="+valeur);
  xajax_moyenne();
}

function temperaturevoulue(data) {
  $('#temperature').text(data);
}

function heater(data) {
   $('#divchaudiere').text(data);
   //console.log("JS/Heater="+data);
}

function boiler(data) {
   if (data=="ON") {
	$('#divchaudiere').text("BOILER");
	//console.log("JS/Boiler="+data);
   }
}

function periodechauffe(data) {
	$('#divperiodechauffe').text(data);
}

function finchauffe(data) {
	$('#divfinchauffe').text(data);
}

function prochainechauffe(data) {
	$('#divprochainechauffe').text(data);
}

</script>