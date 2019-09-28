<?php
include_once '../../lang/www.thermostat.php';
$Lang = "en"; if (isset($_GET["lang"])) { $Lang = $_GET["lang"];}
?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=.8, maximum-scale=.8, user-scalable=no"/>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<title>Nest Thermostat for SmartCAN - Original Source: http://homeautomategorilla.blogspot.be</title>

<style>

body, div {
  -moz-user-select: none;
  user-select: none;  
  -webkit-user-select: none;
  -webkit-tap-highlight-color: rgba(0,0,0,0);    
}

a img {		border: none;
  	  }
a {		color: #FFF;
  }

 /* Fontes utilisées */  
@font-face {
    font-family: "N_E_B";
    src: url(N_E_B.TTF) format("truetype");
    }
	
@font-face {
    font-family: "MANDATOR";
    src: url(MANDATOR.TTF) format("truetype");
    }
	
.desc {
 position:relative;
 left:22;
 top:22;
 }
 /* Le grand cercle noir glossy */
.full-circle {
 position:relative;
 left:22;
 top:22;
 border: 3px solid #333;
 height: 350px;
 width: 350px;

 -moz-border-radius:350px;
 border-radius:350px;
 -webkit-border-radius: 350px;
 
  
 /* Permet de mettre un dégradé sur le cercle en fonction de tous les navigateurs */
 background: #eaeaea; /* Old browsers */
 background: -webkit-radial-gradient(top left, ellipse cover, #eaeaea 0%,#eaeaea 11%,#0e0e0e 61%); /* Chrome10+,Safari5.1+ */
 background: -moz-radial-gradient(top left, ellipse cover,  #eaeaea 0%, #eaeaea 11%, #0e0e0e 61%); /* FF3.6+ */
 background: -webkit-gradient(radial, top left, 0px, top left, 100%, color-stop(0%,#eaeaea), color-stop(11%,#eaeaea), color-stop(61%,#0e0e0e)); /* Chrome,Safari4+ */
 background: -o-radial-gradient(top left, ellipse cover,  #eaeaea 0%,#eaeaea 11%,#0e0e0e 61%); /* Opera 12+ */
 background: -ms-radial-gradient(top left, ellipse cover,  #eaeaea 0%,#eaeaea 11%,#0e0e0e 61%); /* IE10+ */
 background: radial-gradient(top left, ellipse cover,  #eaeaea 0%,#eaeaea 11%,#0e0e0e 61%); /* W3C */
 filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eaeaea', endColorstr='#0e0e0e',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
 
 /* Permet de ne pas pouvoir être sélectionné */
 -webkit-touch-callout: none;
 -webkit-user-select: none;
 -khtml-user-select: none;
 -moz-user-select: none;
 -ms-user-select: none;
 user-select: none;
}

/* Fond chromé, j'utilise ici une image d'un disque brossé */
.fond {
  position:relative;
  background-image: url(fond.png);
  background-repeat: no-repeat;
  width: 400px;
  height: 400px;
  /*left: 40%;
  /*top:20%;
  /* Permet de replir la totalité de la zone */
  background-size:cover;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* La petite feuille nest, affichée en cas d'économie d'énergie, ici lorsqu'on baisse la t° ou le ventilo */
.feuille {
  position:relative;
  top:-220px;
  left:90px;
  background-image: url(leaf.png);
  background-repeat: no-repeat;
  width: 32px;
  height: 32px;
  z-index:auto;
  /* non affichée par défaut */
  opacity:0;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  /* Alignement */
  text-align: center;
}

/* La petite flamme, affichée en cas de demande de chauffage immédiate, disparait en cliquant desssus */
.fire {
  position:relative;
  top:-205px;
  left:90px;
  background-image: url(fire.png);
  background-repeat: no-repeat;
  width: 32px;
  height: 32px;
  z-index:auto;
  /* non affichée par défaut */
  opacity:0;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  text-align: center;
}

/* La valeur NEST affichée, soit Température, soit % ventilo soit demande chauffage immédiat */
.nestValue {
position:relative;
  top:-100;
  left:-10;
  font-family: "MANDATOR", Verdana, Tahoma;
  font-size:60px;
  color:#ffffff;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  text-align: center;
}

/* En cas de demande immédiate de chauffage, durée de demande ici 2h  */
.hour {
  position:relative;
  top:-160;
  left:60;
  font-family: "MANDATOR", Verdana, Tahoma;
  font-size:20px;
  color:#ffffff;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;

  opacity:0;
  z-index:500;
}

/* En cas de demande immédiate de chauffage, durée de demande ici 4h  */
.hour2 {
  position:relative;
  top:-206;
  left:120;
  font-family: "MANDATOR", Verdana, Tahoma;
  font-size:20px;
  color:#ffffff;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;

  opacity:0;
  z-index:500;
}

* En cas de demande immédiate de chauffage, durée de demande ici 2h  */
.consigne {
  position:relative;
 
  font-family: "MANDATOR", Verdana, Tahoma;
  font-size:10px;
  color:#ffffff;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  display:none;
  opacity:0;
  z-index:500;
}

/* Mode Nest: Température, Fan, ou demande chauffage */
.nestMode {
position:relative;
  top:-30;
  left:-10;
  font-family: "MANDATOR", Verdana, Tahoma;
  font-size:20px;
  color:#ffffff;
  /* Permet de ne pas pouvoir être sélectionné */
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;

  text-align: center;
}

/* Titre nest, en cliquant les fonctions apparaissent */
.nestTitle {
  position:relative;
  top:-60;
  left:85;
  font-family: "N_E_B", Verdana, Tahoma;
  font-size:30px;
  color:#ffffff;
  z-index:100;
}


/* Le cercle bleu interne */
.center-circle-cold{
 position:relative;
 left:65;
 top:35;
 border: 0px solid #333;
 height: 220px;
 width: 220px;
 -moz-border-radius:220px;
 border-radius:220px;
 -webkit-border-radius: 220px;

  
 /* Dégradé circulaire */
 background: #3e61a8; /* Old browsers */
 background: -webkit-radial-gradient(top left, ellipse cover, #fff9f9 10%,#0338ac 60%); /* Chrome10+,Safari5.1+ */
 background: -moz-radial-gradient(top left, ellipse cover,  #eaeaea 0%, #fff9f9 10%, #0338ac 60%); /* FF3.6+ */
 background: -webkit-gradient(radial, top left, 0px, top left, 100%, color-stop(0%,#eaeaea), color-stop(10%,#fff9f9), color-stop(60%,#0338ac)); /* Chrome,Safari4+ */
 background: -o-radial-gradient(top left, ellipse cover,  #eaeaea 0%,#fff9f9 10%,#0338ac 60%); /* Opera 12+ */
 background: -ms-radial-gradient(top left, ellipse cover,  #eaeaea 0%,#fff9f9 10%,#0338ac 60%); /* IE10+ */
 background: radial-gradient(top left, ellipse cover,  #eaeaea 0%,#fff9f9 10%,#0338ac 60%); /* W3C */
 filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eaeaea', endColorstr='#0338ac',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */

 /* Permet de ne pas pouvoir être sélectionné */
 -webkit-touch-callout: none;
 -webkit-user-select: none;
 -khtml-user-select: none;
 -moz-user-select: none;
 -ms-user-select: none;
 user-select: none;
}

/*----------------------------
	Barres de progression colorées
-----------------------------*/


#bars{
    
	height: 212px;
	margin: 0 auto;
	position: relative;
	top: 10px;
	left: 2px;
	width: 228px;
	-webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
     user-select: none;
}

.colorBar{
	width:15px;
	height:1px;
	position:absolute;
	opacity:0;
	background-color : #F4F4F4;
	-moz-transition:1s;
	-webkit-transition:1s;
	-o-transition:1s;
	transition:1s;
	-webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.colorBar.active{
	opacity:1;
}



</style>







<!-- <script src="./20121223-jquery.min.js" type="text/javascript"></script> !-->
<!-- <script src="jquery.min.js" type="text/javascript"></script> !-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>

<script type="text/javascript" src="./jqueryRotate.js"></script>

<style type="text/css"></style>


<script type="text/javascript">
// Fonction permettant de dessiner les bars de progression
$(document).ready(function(){
	var rad2deg = 180/Math.PI;
	var deg = 0;
	var bars = $('#bars');
	var j=0;
	for(var i=-20;i<82;i++){
		deg = i*3;
		//console.log(deg);
		// Creation des barres
		mytop =(-Math.sin(deg/rad2deg)*95+100);
		myleft = Math.cos((180 - deg)/rad2deg)*95+100;
		// On ajoute ici 82 barres en indiquant à chacune l'angle de rotation
	$('<div id=barre' + j + ' name=barre' + j + ' class="colorBar" style="-webkit-transform: rotate(' + deg + 'deg) scale(1.25, 0.5); -moz-transform: rotate(' + deg + 'deg) scale(1.25, 0.5); -ms-transform: rotate(' + deg + 'deg) scale(1.25, 0.5);transform: rotate('+ deg +'deg) scale(1.25, 0.5);top: '+ mytop + '; left: ' + myleft+ ' ; color:red" >')
		.appendTo(bars);	
		j++;
	}
	var colorBars = bars.find('.colorBar');
	var numBars = 0, lastNum = -1;
	// ici on les désactive toutes en utilisant la css active sur les éléments de 0 à 0. donc rien.
    colorBars.removeClass('active').slice(0, 0).addClass('active');
})
</script>

<script type="text/javascript">  
$(document).ready(function(){    

$("body").on("touchmove", false);
// Fonction principale, ici un tableau de couleurs dégradées
var grad = [
		'243594','2c358f','373487','44337e','513174',
		'5c306c','6b2f62','792e58','892d4d','9e2b3d',
		'b4292e','c9271f','e0250e'];

// Dernier angle calculé
var lastAngle=0;
// Savoir si le bouton de souris est pressé.
var mouseDown="";
// temperature confort
var temperatureConfort=window.parent.consigneconfort.innerHTML;
// temperature mini
var temperatureMini=window.parent.consigneminimum.innerHTML;
// temperature Affichage LCD
//var temperatureNest=window.parent.moyenne.innerHTML;
var temperatureNest=temperatureConfort;
// temperature actuelle sondes
var temperatureActuelle=window.parent.moyenne.innerHTML;

// ratio utilisé pour synchroniser les barres et le mode température
var ratioTemperature=3;
// couleur de fond pour la temperature (par défaut)
var couleurFondTemperature="#243594";
// couleur de fond pour autoaway
var couleurFondAutoAway="#000000";
// mode par défaut ici TEMP
if (window.parent.divabsence.innerHTML!="1") {
  var currentMode="";
  //console.log("PRESENT! "+window.parent.divabsence.innerHTML);
} else {
  var currentMode="HEAT NOW";
  //console.log("ABSENT! "+window.parent.divabsence.innerHTML);
} // ENDIF
// Savoir si on est en mode Demande de chauffage Now
var heatNow = "";

var consigne=$('#consigne');

var readyToSensor="NOK";
var modeForce="";
var modeH="";
var wd=new Array(7); <?php for ($i = 0; $i <= 7; $i++) { echo("wd[$i+1]='".substr($day_list[$Lang][$i],0,3)." '; "); }?>
 
refreshTemp();

if (currentMode!="HEAT NOW") {
  $("#nestMode" ).slideDown();
  $("#nestValue" ).slideDown();
  $("#hour" ).slideDown();
  $("#hour2" ).slideDown();
  $("#consignee" ).slideDown();
  $( "#nestMode" ).html("TEMP (" + temperatureConfort + "&deg;)");
  var FinChauffe = window.parent.divfinchauffe.innerHTML;
  if (FinChauffe!="") {
    if (FinChauffe.length==3) { FinChauffe = "0" + FinChauffe; }
	FinChauffe = FinChauffe.substr(0,2) + ":" + FinChauffe.substr(2,2);
    var TexteNext = "<font size='2'><br><?php echo ($msg["thermostat"]["End"][$Lang]);?>: " + FinChauffe + "</font>";
  } else {
    var ProchaineChauffe = window.parent.divprochainechauffe.innerHTML; 
	if (ProchaineChauffe!="") {
	  if (ProchaineChauffe.length==3) { ProchaineChauffe = "0" + ProchaineChauffe; }
	  var TexteNext = "<font size='2'><br><?php echo ($msg["thermostat"]["Next"][$Lang]);?>: " + wd[window.parent.divprochainechauffeD.innerHTML] + ProchaineChauffe.substr(0,2) + ":" + ProchaineChauffe.substr(2,2) + "</font>";
	} else { var TexteNext = ""; }
  } // END IF
  $( "#nestValue" ).html(temperatureActuelle + TexteNext);
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $( "#consigne" ).html("");
  $( "#consigne" ).css("opacity","0");
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $('#feuille').css("opacity","0");
  poseConsigne(0,"");
  majBarres(0,ratioTemperature);
  //temperatureNest=temperatureActuelle;
  setCouleurTemperature();
  majCouleurCercle(couleurFondTemperature);
  //console.log("Init - Mode force =" + modeForce + "=-");
  if ( modeForce == "" || modeForce == "false") {
    readyToSensor="OK";
	refreshTemp()
	sensorFresh();
  } else {
    document.getElementById('fire').style.backgroundImage = "url('fire.png')";
	$("#fire").css("opacity","0");
  } // END IF
} else {
  $("#nestMode" ).slideDown();
  $("#nestValue" ).slideDown();
  $("#hour" ).slideDown();
  $("#hour2" ).slideDown();
  $("#consignee" ).slideDown();
  $( "#nestMode" ).html("Auto");
  $( "#nestValue" ).html(temperatureActuelle);
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $( "#consigne" ).html("");
  $( "#consigne" ).css("opacity","0");
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $('#feuille').css("opacity","0");
  poseConsigne(0,"");
  majBarres(0,ratioTemperature);

  currentMode="Auto";
  $( "#nestValue" ).html("AWAY");
  setCouleurTemperature();
  majCouleurCercle(couleurFondAutoAway);
  $( "#consigne" ).html("");
  $( "#consigne" ).css("opacity","0");
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $('#feuille').css("opacity","1");
  majBarres(0,ratioTemperature);
  //console.log("Auto AWAY");
} // END IF



// Une petite tache de fond lancée toutes les 7 secondes pour raffraichir les données de puis le serveur
var refreshLoop = setInterval(function () {
    refreshTemp();
}, 5000);

function sensorFresh() {
	if ( readyToSensor == "OK" ) {
	  if (window.parent.divabsence.innerHTML!="1") {
		$("#nestMode" ).slideDown();
		$("#nestValue" ).slideDown();
		$("#hour" ).slideDown();
		$("#hour2" ).slideDown();
		$("#consignee" ).slideDown();
		$( "#nestMode" ).html("TEMP (" + temperatureConfort + "&deg;)");
		FinChauffe = window.parent.divfinchauffe.innerHTML;
		if (FinChauffe!="") {
		  if (FinChauffe.length==3) { FinChauffe = "0" + FinChauffe; }
		  FinChauffe = FinChauffe.substr(0,2) + ":" + FinChauffe.substr(2,2);
		  TexteNext = "<font size='2'><br><?php echo ($msg["thermostat"]["End"][$Lang]);?>: " + FinChauffe + "</font>";
		} else {
		  ProchaineChauffe = window.parent.divprochainechauffe.innerHTML; 
		  if (ProchaineChauffe!="") {
		    if (ProchaineChauffe.length==3) { ProchaineChauffe = "0" + ProchaineChauffe; }
		    TexteNext = "<font size='2'><br><?php echo ($msg["thermostat"]["Next"][$Lang]);?>: " + wd[window.parent.divprochainechauffeD.innerHTML] + ProchaineChauffe.substr(0,2) + ":" + ProchaineChauffe.substr(2,2) + "</font>";
		  } else { TexteNext = ""; }
		} // END IF
		$( "#nestValue" ).html(temperatureActuelle + TexteNext);
		//$( "#nestValue" ).html(temperatureActuelle);
		 $("#hour").css("opacity","0");
		  $("#hour2").css("opacity","0");
		  $( "#consigne" ).html("");
			$( "#consigne" ).css("opacity","0");
			$("#hour").css("opacity","0");
			$("#hour2").css("opacity","0");
			$('#feuille').css("opacity","0");
			poseConsigne(0,"");
			majBarres(0,ratioTemperature);
			//temperatureNest=temperatureActuelle;
			setCouleurTemperature();
			majCouleurCercle(couleurFondTemperature);
			//console.log("Mode force " + modeForce);
			if ( modeForce == "" || modeForce == "false") {
			  $("#fire").css("opacity","0");
			  if (modeH==='BOILER') {
	            document.getElementById('fire').style.backgroundImage = "url('water.png')";
	            $("#fire").css("opacity","1");
	          }
			} else{
			  document.getElementById('fire').style.backgroundImage = "url('fire.png')";
		      $("#fire").css("opacity","1");
			} // END IF
	  } else {
		$("#nestMode" ).slideDown();
		$("#nestValue" ).slideDown();
		$("#hour" ).slideDown();
		$("#hour2" ).slideDown();
		$("#consignee" ).slideDown();
		$( "#nestMode" ).html("Auto");
		$( "#nestValue" ).html(temperatureActuelle);
		$("#hour").css("opacity","0");
		$("#hour2").css("opacity","0");
		$( "#consigne" ).html("");
		$( "#consigne" ).css("opacity","0");
		$("#hour").css("opacity","0");
		$("#hour2").css("opacity","0");
		$('#feuille').css("opacity","0");
		poseConsigne(0,"");
		majBarres(0,ratioTemperature);

		currentMode="Auto";
		$( "#nestValue" ).html("AWAY");
		setCouleurTemperature();
		majCouleurCercle(couleurFondAutoAway);
		$( "#consigne" ).html("");
		$( "#consigne" ).css("opacity","0");
		$("#hour").css("opacity","0");
		$("#hour2").css("opacity","0");
		$('#feuille').css("opacity","1");
		majBarres(0,ratioTemperature);
		//console.log("Auto AWAY");
	  } // END IF
	} // END IF
	if ( mouseDown != "ok" ) {
	  readyToSensor="OK";
	}else {
	  readyToSensor="NOK";
	}
	if ((currentMode!="Auto") && (readyToSensor=="OK")) { currentMode=""; }
}

//Une petite tache de fond pour faire disparaitre l'affichage et remettre la temperature actuelle (sondes)
var sensorLoop = setInterval(function () {
	sensorFresh();
}, 10000);




// Calibrage des rotations, pour l'affichage uniquement, rien de fonctionnel
for(var i=0;i<3600;i++){
$('#full-circle').rotate(Math.round(i));  

}



function refreshTemp()
{
	//console.log("Refresh Temp");
	//console.log("Mode="+currentMode+"Mouse="+readyToSensor);
	if ((currentMode=="") && (currentMode!="Auto") && (readyToSensor=="OK")) {
	  //console.log("Update Temp & Others");
	  getTempMini();
	  getTempConfort();
	  getTempActuelle();
	  getModeForce();
	  var FinChauffe = window.parent.divfinchauffe.innerHTML;
      if (FinChauffe!="") {
        if (FinChauffe.length==3) { FinChauffe = "0" + FinChauffe; }
	    FinChauffe = FinChauffe.substr(0,2) + ":" + FinChauffe.substr(2,2);
        var TexteNext = "<font size='2'><br><?php echo ($msg["thermostat"]["End"][$Lang]);?>: " + FinChauffe + "</font>";
      } else {
        var ProchaineChauffe = window.parent.divprochainechauffe.innerHTML; 
	    if (ProchaineChauffe!="") {
	      if (ProchaineChauffe.length==3) { ProchaineChauffe = "0" + ProchaineChauffe; }
	      var TexteNext = "<font size='2'><br><?php echo ($msg["thermostat"]["Next"][$Lang]);?>: " + wd[window.parent.divprochainechauffeD.innerHTML] + ProchaineChauffe.substr(0,2) + ":" + ProchaineChauffe.substr(2,2) + "</font>";
	    } else { var TexteNext = ""; }
      } // END IF
      $( "#nestValue" ).html(temperatureActuelle + TexteNext);
	  setCouleurTemperature();
	} // END IF
	
	
	
}
	
	
// Permet de positionner la temperature de consigne sur les barres
function poseConsigne(numBar,val)
{
        var rad2deg = 180/Math.PI;
		deg = numBar*15;
		//console.log(deg);
		// Creation des barres
		mytop =(-Math.sin(deg/rad2deg)*95+100);
		myleft = Math.cos((180 - deg)/rad2deg)*95+100;	
		
		//console.log("myleft=" + Math.round(myleft));
		var colorbar = $('#barre' + numBar)
		
		if  ( colorbar != null )
		{
		var colorbarOffset = colorbar.offset();
		//console.log("LEFT: " + colorbar.left);
		if ( colorbarOffset != null )
		{
		consigne.css("position","absolute");
		consigne.css("left",colorbarOffset.left);
		consigne.css("top",colorbarOffset.top);
		//for(var i=0;i<102;i++){
		//var colorbarTmp = $('#barre' + i);
		//colorbarTmp.css("height",1);
		//}
		//colorbar.css("height",4);
		}
		//console.log("NUMBAR: " + numBar);
		}
		consigne.css("font-family","MANDATOR");
		consigne.html(val);
};


function setCouleurTemperature()
{
  if ( (modeForce=='false') ) {
    //console.log("SetCouleur modeForce=" + modeForce + ", fin de chauffe=" + FinChauffe + ", modeH=" + modeH);
    // '243594','2c358f','373487','44337e','513174','5c306c','6b2f62','792e58','892d4d','9e2b3d','b4292e','c9271f','e0250e'
    //couleurFondTemperature=couleurFondAutoAway;
	if (FinChauffe!="") { couleurFondTemperature='#243594'; } else { couleurFondTemperature=couleurFondAutoAway; }
	$("#fire").css("opacity","0");
	if (modeH=='BOILER') {
	  document.getElementById('fire').style.backgroundImage = "url('water.png')";
	  $("#fire").css("opacity","1");
	} else {
	  document.getElementById('fire').style.backgroundImage = "url('fire.png')";
	  $("#fire").css("opacity","0");
	}
  } else {
	// Choix de la couleur de fond en fonction de la temperature
	var temp=Math.round(temperatureMini-temperatureNest+12);
	//console.log("temperature Nest " + temperatureNest);
	//console.log("temperature round " + temp);
	//$("#fire").css("opacity","1");
	document.getElementById('fire').style.backgroundImage = "url('fire.png')";
	couleurFondTemperature='#e0250e';
  } // ENDIF
}

// Définition de la fonction pour gestion de temperature
function manageTemperature(e) {
    var offset = $('#full-circle').offset();
    var width=$('#full-circle').width();
    var height=$('#full-circle').height();
    var center_x = (offset.left) + (width/2);
    var center_y = (offset.top) + (height/2);
    var mouse_x = e.pageX; var mouse_y = e.pageY;
	var bars = $('#bars');
	var centerCircle = $('#center-circle-cold');
	var colorBars = bars.find('.colorBar');
	var feuille = $('#feuille');

	//console.log("width="+ width + " height="+height + " center_x=" + center_x + " center_y=" + center_y + " offsetLeft=" + offset.left + " this.offsetTop="+ offset.top);
	
	 setCouleurTemperature();
     // Arrondi au Dixième
     temperatureNest=Math.round(temperatureNest*2)/2;

     var radians = Math.atan2(mouse_x - center_x, mouse_y - center_y);
     degree = (radians * (540 / Math.PI) * -1) + 180; 
	 
	 // On regarde le dernier angle pour savoir si on est en mode + ou -
	 if ( degree - lastAngle > 0) {
	   //console.log("lastAngle=" + lastAngle + " degree=" + degree + "+");
	   if ( degree - lastAngle > 1.5) {temperatureNest+=0.5; }
	   //feuille.css("opacity","0");
	   //$( "#nestMode" ).html(currentMode);
	 } else {
	   //console.log("lastAngle=" + lastAngle + " degree=" + degree + "-");
	   if ( degree - lastAngle < -1.5) { temperatureNest-=0.5; }
       //feuille.css("opacity","1");
	   //$( "#nestMode" ).html(currentMode);
	 } // END IF
	 temperatureNest=Math.round(temperatureNest*2)/2;
	 majCouleurCercle(couleurFondTemperature);
	 poseConsigne(ratioTemperature*temperatureNest,temperatureNest);
	 majBarres(temperatureNest,ratioTemperature);
	 lastAngle=degree;
	 $( "#nestValue" ).html(temperatureNest );
	 
};


// Fonction mettant à jour les barresn en passant la valeur et le ratio
function majBarres(value,ratio)
{
var bars = $('#bars');
var colorBars = bars.find('.colorBar');
colorBars.removeClass('active').slice(0, Math.round(value*ratio)).addClass('active');

}

function getTempConfort()
{
// Pour rendre dynamique la recherche appliquer le même raisonnement que pour la fonction getTempActuelle
//DATAS Ici on récupère un valeur aléatoire pour la démo, mais sinon en décommentant en  dessous et positionnant votre 
	// url la température sera récupérée en AJAX
	//temperatureConfort=window.parent.temp_nest;
	temperatureConfort= window.parent.consigneconfort.innerHTML;
	//console.log("Temp Confort="+temperatureConfort);
	//var requestTempConfort=$.ajax({
	//	  url: "ObjectAction?action=getTempConfort&objectName=Chauffage&objectClass=Manager",
	//	  dataType: "html",
	//	  async: true
	//	});
	//requestTempConfort.done(function(msg) {
	//	temperatureConfort=msg;
	//	});
}

function getTempActuelle()
{
    //DATAS Ici on récupère un valeur aléatoire pour la démo, mais sinon en décommentant en  dessous et positionnant votre 
	// url la température sera récupérée en AJAX
	//temperatureActuelle=Math.floor((Math.random()*20)+1);
	
	temperatureActuelle = window.parent.moyenne.innerHTML; //window.parent.temp_house;
	//console.log("NEST Moyenne="+temperatureActuelle);
}

function getModeForce()
{
	//console.log("Moyenne="+window.parent.moyenne.innerHTML);
	//console.log("Chaudiere="+window.parent.divchaudiere.innerHTML);
	if (window.parent.divchaudiere.innerHTML=="ON") {
	  modeForce='true';
	  modeH    ='HEATER';
	} else {
	  if (window.parent.divchaudiere.innerHTML=="BOILER") {
	    modeForce='false';
	    modeH    ='BOILER';
	  } else {
	    if (window.parent.divchaudiere.innerHTML=="OFF") {
	      modeForce='false'; 
		  modeH    ='HEATER';
	    }
	  }
	} // ENDIF
}

function setTempConfort()
{
	  window.parent.xajax_updateConsigne(temperatureConfort);
	  //alert("Update Consigne CONFORT!");
	  //window.parent.traitement();
	  TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
}
	
function setTempMini()
{
	  window.parent.xajax_updateConsigneMini(temperatureMini);
	  //alert("Update Consigne MINI!");
	  //window.parent.traitement();
	  TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
}

function getTempMini()
{
	//temperatureMini=10;
	temperatureMini = window.parent.consigneminimum.innerHTML;
	//console.log("Temp Min="+temperatureMini);
}

// Fonction mettant à jour le degradé sur cercle central en passant la couleur de fond à obtenir
function majCouleurCercle(couleurFond)
{
	//console.log("maj couleur cercle " + couleurFond);
       var centerCircle = $('#center-circle-cold');
	   centerCircle.css("background", "-webkit-radial-gradient(top left, ellipse cover, #fcf7f7 10%," + couleurFond + " 60%)"); /* Chrome 10 */
	   centerCircle.css("background", "-moz-radial-gradient(top left, ellipse cover, #fcf7f7 10%," + couleurFond + " 60%)"); /* FF */
	   centerCircle.css("background", "-webkit-gradient(radial, top left, 0px, top left, 100%, color-stop(10%,fff9f9), color-stop(60%,"+ couleurFond +"))"); /* Safari */
	   centerCircle.css("background", "-o-radial-gradient(top left, ellipse cover, #fcf7f7 10%," + couleurFond + " 60%)"); /* Opera 12+ */
	   centerCircle.css("background", "-ms-radial-gradient(top left, ellipse cover, #fcf7f7 10%," + couleurFond + " 60%)"); /* IE10+ */
       centerCircle.css("background", "radial-gradient(top left, ellipse cover, #fcf7f7 10%," + couleurFond + " 60%)"); /* W3C */
}

$('#full-circle').mousedown(function(e){
  // Lorsqu'on appuie sur le bouton de gauche, on autorise le thermostat à bouger
  mouseDown="ok";
  
});

$('*').mousedown(function(e){
	readyToSensor="NOK";
	});

$('*').mouseup(function(e){
  // Lorsqu'on relache le bouton de gauche, on n'autorise plus le thermostat à bouger
  if (mouseDown!="") {
    //alert("Relache, mouseDown="+mouseDown);
    mouseDown="";
    release();
  }
  
});

function release() {
	if ((currentMode == "TEMP CONFORT" ) && (Number(temperatureConfort)!=Number(temperatureNest))) {
	  temperatureConfort=temperatureNest;
	  //console.log("Release TEMP CONFORT");
	  //alert("Release TEMP CONFORT="+Number(temperatureConfort)+"<> Nest="+Number(temperatureNest)+", MouseDown="+mouseDown);	
	  setTempConfort();
	  refreshTemp();
	  //TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
	}
	if ((currentMode == "TEMP MINI" ) && (Number(temperatureMini)!=Number(temperatureNest))) {
	  temperatureMini=temperatureNest;
	  //console.log("Release TEMP MINI");
	  //alert("Release TEMP MINI="+temperatureMini+"<> Nest="+temperatureNest+"MouseDown="+mouseDown);
	  setTempMini();
	  refreshTemp();
	  //TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
	}
	//currentMode="";
}

// Lorsque l'on clique sur l'icone Config
//$('#nestValue').click(function(){ 
//  if ($( "#nestValue" ).html()=="Config") {
//    //console.log("Config");
//	parent.showOverlay("ConfigDIV","day[0][0]");
//  } // END IF
//});

// Lorsque l'on clique sur l'icone 2h, on sauvegarde et on fait tout disparaitre
$('#hour').click(function(){ 
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $("#fire").css("opacity","1");
  heatNow=1;
   $( "#nestValue" ).html("1h");
   window.parent.xajax_HeatNow(1);
   TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
});

// Lorsque l'on clique sur l'icone 4h, on sauvegarde et on fait tout disparaitre
$('#hour2').click(function(){ 
  $("#hour").css("opacity","0");
  $("#hour2").css("opacity","0");
  $("#fire").css("opacity","1");
  heatNow=2
   $( "#nestValue" ).html("2h");
   window.parent.xajax_HeatNow(2);
   TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
});

// Lorsque l'on clique sur l'icone fire, le mode de demande immédiate de chauffage disparait
$('#fire').click(function(){ 
$("#fire").css("opacity","0");
heatNow="";
  window.parent.xajax_autoBack();
  TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
});

// Lorsque l'on clique sur l'icone feuille, passe en mode presence (Absence-1)
$('#feuille').click(function(){ 
  $("#feuille").css("opacity","0");
  //console.log("absence=1");
  window.parent.xajax_autoBack();
  TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
});

// Lorsque l'on clique sur le texte nest, on switch d'un mode à l'autre
$('#nestTitle').click(function(){ 

  //console.log("clic" + " currentMode=" + currentMode);
  
  if ( currentMode == "HEAT NOW" )
   
   {
   currentMode="Auto";
	$( "#nestValue" ).html("AWAY");
	setCouleurTemperature();
	majCouleurCercle(couleurFondAutoAway);
	$( "#consigne" ).html("");
	$( "#consigne" ).css("opacity","0");
	$("#hour").css("opacity","0");
	$("#hour2").css("opacity","0");
	$('#feuille').css("opacity","1");
	$('#fire').css("opacity","0");
	majBarres(0,ratioTemperature);
	//console.log("Auto AWAY");
	TimeOUT_AWAY=setTimeout(function(){AUTO_Away()},5000);
   } else if ( currentMode == "Auto" ) 

   {
	    currentMode="TEMP CONFORT";
	    temperatureNest=temperatureConfort;
		$( "#nestMode" ).html(currentMode);
		$( "#nestValue" ).html(temperatureNest );
		setCouleurTemperature();
		majCouleurCercle(couleurFondTemperature);
		majBarres(temperatureNest,ratioTemperature);
		$( "#consigne" ).html("");
		$( "#consigne" ).css("opacity","1");
		$("#hour").css("opacity","0");
		$("#hour2").css("opacity","0");
		$('#feuille').css("opacity","0");
		clearTimeout(TimeOUT_AWAY);

	} else if ( currentMode == "TEMP CONFORT")
	
   {
    currentMode="TEMP MINI";
    temperatureNest=temperatureMini;
	$( "#nestMode" ).html(currentMode);
	$( "#nestValue" ).html(temperatureNest );
	setCouleurTemperature();
	majCouleurCercle(couleurFondTemperature);
	majBarres(temperatureNest,ratioTemperature);
	$( "#consigne" ).html("");
	$( "#consigne" ).css("opacity","1");
	$("#hour").css("opacity","0");
	$("#hour2").css("opacity","0");
	$('#feuille').css("opacity","0");
	
   } else if ( currentMode == "TEMP MINI")   
   
    {
    currentMode="&nbsp;";
	$( "#nestMode" ).html(currentMode);
	$( "#nestValue" ).html("Config");
	setCouleurTemperature();
	majCouleurCercle(couleurFondAutoAway);
	$( "#consigne" ).html("");
	$( "#consigne" ).css("opacity","0");
	$("#hour").css("opacity","0");
	$("#hour").css("opacity","0");
	$("#hour2").css("opacity","0");
	$('#feuille').css("opacity","0");
	//clearTimeout(TimeOUT_RELOAD);
	majBarres(0,ratioTemperature);
	TimeOUT_CONFIG=setTimeout(function(){parent.showOverlay("ConfigDIV","day[0][0]");},5000);
   } else if ( currentMode == "&nbsp;" || currentMode == "") 

   {
    currentMode="&nbsp;&nbsp;";
	$( "#nestMode" ).html(currentMode);
	$( "#nestValue" ).html("<span id=\"changetemp\" onClick=\"parent.go('heater')\"><img src='thermometer.png' width=48 height=48 align='center' /><br>Temp</span>");
	setCouleurTemperature();
	majCouleurCercle(couleurFondAutoAway);
	$( "#consigne" ).html("");
	$( "#consigne" ).css("opacity","0");
	$("#hour").css("opacity","0");
	$("#hour").css("opacity","0");
	$("#hour2").css("opacity","0");
	$('#feuille').css("opacity","0");
	majBarres(0,ratioTemperature);
	clearTimeout(TimeOUT_CONFIG);
	TimeOUT_TEMP=setTimeout(function(){parent.go('heater');},5000);
   } else if ( currentMode == "&nbsp;&nbsp;")  
   
   
   {
    currentMode="HEAT NOW";
	$( "#nestMode" ).html(currentMode);
	$( "#nestValue" ).html('for');
	setCouleurTemperature();
	majCouleurCercle(couleurFondTemperature);
	majBarres(0,ratioTemperature);
	$("#hour").css("opacity","1");
	$("#hour2").css("opacity","1");
	$( "#consigne" ).css("opacity","0");
	$('#feuille').css("opacity","0");
	clearTimeout(TimeOUT_TEMP);
   }   
   
  $( "#nestMode" ).html(currentMode);
  $("#nestMode" ).slideUp();
  $( "#nestMode" ).html(currentMode)
  $("#nestMode" ).slideDown();
});

function AUTO_Away() {
  //alert("TimeOUT!");
  window.parent.xajax_autoAway();
  TimeOUT_RELOAD=setTimeout(function(){window.parent.location.reload(true)},500);
}

// En fonction du mode, on fait varier les couleurs et les barres différement
$('#full-circle').mousemove(function(e){ 
  // Si on est autorisé à bouger	
  if ( mouseDown == "ok" )
   {
    if ( currentMode == "TEMP CONFORT" ||  currentMode == "TEMP MINI" )
	{
	manageTemperature(e);
	//alert("Move");
    } 
   }
});


$('#full-circle').bind( "touchstart", function(e){
  // Lorsqu'on touche l'écran, on autorise le thermostat à bouger
  mouseDown="ok";
  //alert("touchstart");
});

$('*').bind( "touchend", function(e){
  // Lorsqu'on relache l'écran, on n'autorise plus le thermostat à bouger
  mouseDown="";
  release();
  //alert("touchend");
});

$('#full-circle').bind( "touchmove", function(e){
	readyToSensor="NOK";
    var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
  // Si on est autorisé à bouger   
  if ( mouseDown == "ok" )
   {
    if ( currentMode == "TEMP MINI" || currentMode == "TEMP CONFORT" )
   {
   manageTemperature(touch);
   //alert("Touch Move");
    } 
    
 }
});
});
</script>

</head>
<body style="background: #000; color: #FFF;">	

<div id="nestTitle" class="fond">
	<div id="full-circle" class="full-circle">
		<div id="center-circle-cold" class="center-circle-cold">
			  <div id="bars">
              <p id="nestTitleSMALL" class="nestTitle">nest</p>
			  <p name="nestMode" id="nestMode" class="nestMode"></p>
			  <p name="nestValue" id="nestValue" class="nestValue"></p>
			  <p name="hour" id="hour" class="hour">1H</p>
			  <p name="hour2" id="hour2" class="hour2">2H</p>
			
			  <div id="feuille" class="feuille"></div>
			  <div id="fire" class="fire"></div>
		</div>
	</div>
</div>

</div>

  <div id="consigne" name="consigne" >

<script type="text/javascript">
//<![CDATA[
if (typeof _gstat != "undefined") _gstat.audience('','pagesperso-orange.fr');
//]]>
</script>
</body></html>