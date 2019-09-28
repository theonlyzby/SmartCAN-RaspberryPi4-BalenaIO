<script type="text/javascript">

function receptionmodule(cle, valeur) {

  if ( cle == 'modenuit' ) {
    modenuit(valeur);
  }

  if ( cle == 'LAMP' ) {
    allumage(valeur);
  }

}

function allumage(data) {
  tab = data.split(',');
//  manufacturer = tab[0];
//  carte = tab[1];
  lamp   = tab[0];
  icon   = tab[1];
  valeur = tab[2];
  if ( valeur == 0 ) {
    img = './images/outputs/'+icon+'_off.png';
  } else {
    img = './images/outputs/'+icon+'_on.png';
  }
//  $('#' + manufacturer + '_' + carte + '_' + sortie).attr({ src: img });
  $('#LAMP_' + lamp).attr({ src: img });
  $('#traitement').css('display', 'none');
}


</script>
