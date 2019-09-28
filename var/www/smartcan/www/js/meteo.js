<script type="text/javascript">

function receptionmodule(cle,valeur) {

  if ( cle == 'fete' ) {
    $('#fete').text(valeur);
  }

}

$(document).ready(function() {
  $('#meteo').jdigiclock({
    imagesPath: './lib/meteo/images/',
    weatherLocationCode: '968019',
	weatherMetric: 'c',
    weatherUpdate: '30'
  });
});

</script> 
