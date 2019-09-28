<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Include ALL Admin Language files in this folder
//foreach (glob("./lang/*.php") as $filename) {
//    if ($filename!="./lang/admin.main.php") { include $filename; }
//} // END FOR

// Messages
  // EN
$msg["MAIN"]["lang"]["en"] = "English"; // Declares English Language Variables
$msg["MAIN"]["title"]["en"] = "SmartCAN Admin";
$msg["MAIN"]["forbidden"]["en"] = "Forbidden Access";
$msg["MAIN"]["bepatient"]["en"] = "Be patient";
$msg["MAIN"]["reload"]["en"]    = "<noscript>Thanks to reload this pages within 120</noscript></div> Seconds";
$msg["MAIN"]["shutdown"]["en"]  = "System SHUTDOWN";
$msg["MAIN"]["Rebooting"]["en"]  = "System REBOOTing";
$msg["MAIN"]["javacfrm1"]["en"]  = "Are you sure you want to";
$msg["MAIN"]["javacfrm2"]["en"]  = "the system";
$msg["MAIN"]["next"]["en"]  = "Next";
$msg["MAIN"]["Pass"]["en"]  = "Pass";
$msg["MAIN"]["update"]["en"]  = "Update";
$msg["MAIN"]["Incorrect"]["en"]  = "INCORRECT!";
$msg["MAIN"]["Check"]["en"] = "Check";
$msg["MAIN"]["Password"]["en"] = "Password";
$msg["MAIN"]["Language"]["en"] = "Language";
$msg["MAIN"]["ChangeSaved"]["en"] = "Change Saved !";
$msg["MAIN"]["BePatient"]["en"] = "Be patient ... ";
$msg["MAIN"]["Seconds"]["en"] = "Seconds";
$msg["MAIN"]["Identifier"]["en"] = "Level";
$msg["MAIN"]["Description"]["en"] = "Description";
$msg["MAIN"]["RuSure"]["en"] = "Are you sure ?";
$msg["MAIN"]["Name"]["en"] = "Name";
$msg["MAIN"]["Choose"]["en"] = "Choose";
$msg["MAIN"]["Select"]["en"] = "Select";
$msg["MAIN"]["Module"]["en"] = "Module";
$msg["MAIN"]["Output"]["en"] = "Output";
$msg["MAIN"]["Delay"]["en"] = "Delay";
$msg["MAIN"]["Memory"]["en"] = "Memory";
$msg["MAIN"]["Icon"]["en"] = "Icon";
$msg["MAIN"]["Save"]["en"] = "Save";
$msg["MAIN"]["NotInstalled"]["en"] = "NOT Installed";
$msg["MAIN"]["Installed"]["en"] = "Installed";
$msg["MAIN"]["Status"]["en"] = "Status";
$msg["MAIN"]["Stopped"]["en"] = "Stopped";
$msg["MAIN"]["Actif"]["en"] = "ACTIF";
$msg["MAIN"]["NOK"]["en"] = "NOK";
$msg["MAIN"]["Path"]["en"] = "Path";
$msg["MAIN"]["DBerror"]["en"] = "DB Error";
$msg["MAIN"]["DBempty"]["en"] = "DB Empty";
$msg["MAIN"]["FileCopyError"]["en"] = "File Copy ERROR";
$msg["MAIN"]["IncorrectFileType"]["en"] = "INCORRECT File Type";
$msg["MAIN"]["NoFileError"]["en"] = "No File !";
$msg["MAIN"]["UseOnlyPNG"]["en"] = "Use ONLY .png file";
$msg["MAIN"]["FileDeleteError"]["en"] = "Can NOT delete Files";
$msg["MAIN"]["OtherLevels"]["en"] = "Other Levels";
$msg["MAIN"]["EditLevel"]["en"] = "Edit Level";
$msg["MAIN"]["ChangeLevelImage"]["en"] = "Modify level Image";
$msg["MAIN"]["AddLevel"]["en"] = "Add a Level";
$msg["MAIN"]["Close"]["en"] = "Close";
$msg["MAIN"]["LevelImage"]["en"] = "Level Image ( .png Format, 500x355 Pixels)";
$msg["MAIN"]["Modify"]["en"] = "Modify";
$msg["MAIN"]["LevelName"]["en"] = "Level Name";
$msg["MAIN"]["Add"]["en"] = "Add";
$msg["MAIN"]["EmptyLevelName"]["en"] = "Empty level Name";
$msg["MAIN"]["UseOnlyAZ"]["en"] = "Use names including ONLY A-Z , a-z , 0-9, _ ou -";
$msg["MAIN"]["LevelAlreadyExists"]["en"] = "This Level already Exists";
$msg["MAIN"]["EmptyDescription"]["en"] = "Empty Description";


  // FR
$msg["MAIN"]["lang"]["fr"] = "Fran&ccedil;ais"; // Declares French Language Variables
$msg["MAIN"]["title"]["fr"] = "Admin SmartCAN";
$msg["MAIN"]["forbidden"]["fr"] = "Acc&egrave;s Interdit";
$msg["MAIN"]["bepatient"]["fr"] = "Soyez patient";
$msg["MAIN"]["reload"]["fr"]    = "<noscript>Merci de recharger cette page dans 120</noscript></div> Secondes";
$msg["MAIN"]["shutdown"]["fr"]  = "System SHUTDOWN";
$msg["MAIN"]["Rebooting"]["fr"]  = "System REBOOTing";
$msg["MAIN"]["javacfrm1"]["fr"]  = "Etes-vous certain de vouloir";
$msg["MAIN"]["javacfrm2"]["fr"]  = "le Systeme"; // \350
$msg["MAIN"]["next"]["fr"]  = "Suivant";
$msg["MAIN"]["Pass"]["fr"]  = "Passer";
$msg["MAIN"]["update"]["fr"]  = "Update";
$msg["MAIN"]["Incorrect"]["fr"]  = "INCORRECT!";
$msg["MAIN"]["Check"]["fr"] = "V&eacute;rification";
$msg["MAIN"]["Password"]["fr"] = "Mot de passe";
$msg["MAIN"]["Language"]["fr"] = "Langue";
$msg["MAIN"]["ChangeSaved"]["fr"] = "Modification Enregistr&eacute;e !";
$msg["MAIN"]["BePatient"]["fr"] = "Soyez patient ... ";
$msg["MAIN"]["Seconds"]["fr"] = "Secondes";
$msg["MAIN"]["Identifier"]["fr"] = "Niveau";
$msg["MAIN"]["Description"]["fr"] = "Description";
$msg["MAIN"]["RuSure"]["fr"] = "Etes vous certain ?";
$msg["MAIN"]["Name"]["fr"] = "Nom";
$msg["MAIN"]["Choose"]["fr"] = "Choisir";
$msg["MAIN"]["Select"]["fr"] = "S&eacute;lectionnez";
$msg["MAIN"]["Module"]["fr"] = "Module";
$msg["MAIN"]["Output"]["fr"] = "Sortie";
$msg["MAIN"]["Delay"]["fr"] = "D&eacute;lai";
$msg["MAIN"]["Memory"]["fr"] = "M&eacute;moire";
$msg["MAIN"]["Icon"]["fr"] = "Icone";
$msg["MAIN"]["Save"]["fr"] = "Sauver";
$msg["MAIN"]["NotInstalled"]["fr"] = "PAS Install&eacute;";
$msg["MAIN"]["Installed"]["fr"] = "Install&eacute;";
$msg["MAIN"]["Status"]["fr"] = "Etat";
$msg["MAIN"]["Stopped"]["fr"] = "Arr&ecirc;t&eacute;";
$msg["MAIN"]["Actif"]["fr"] = "ACTIF";
$msg["MAIN"]["NOK"]["fr"] = "NOK";
$msg["MAIN"]["Path"]["fr"] = "Chemin";
$msg["MAIN"]["DBerror"]["fr"] = "Erreur DB";
$msg["MAIN"]["DBempty"]["fr"] = "DB Vide";
$msg["MAIN"]["FileCopyError"]["fr"] = "Probl&egrave;me de copie de Fichier";
$msg["MAIN"]["IncorrectFileType"]["fr"] = "Type de fichier INCORRECT";
$msg["MAIN"]["NoFileError"]["fr"] = "Pas de fichier !";
$msg["MAIN"]["UseOnlyPNG"]["fr"] = "Utilisez UNIQUEMENT des fichiers .png";
$msg["MAIN"]["FileDeleteError"]["fr"] = "Impossible d'effacer les Fichiers";
$msg["MAIN"]["OtherLevels"]["fr"] = "Autres Niveaux";
$msg["MAIN"]["EditLevel"]["fr"] = "Editer Niveau";
$msg["MAIN"]["ChangeLevelImage"]["fr"] = "Modifier l'image du niveau";
$msg["MAIN"]["AddLevel"]["fr"] = "Ajouter un Niveau";
$msg["MAIN"]["Close"]["fr"] = "Fermer";
$msg["MAIN"]["LevelImage"]["fr"] = "Image du Niveau (Format .png , 500x355 Pixels)";
$msg["MAIN"]["Modify"]["fr"] = "Modifier";
$msg["MAIN"]["LevelName"]["fr"] = "Nom du Niveau";
$msg["MAIN"]["Add"]["fr"] = "Ajouter";
$msg["MAIN"]["EmptyLevelName"]["fr"] = "Nom du niveau Vide";
$msg["MAIN"]["UseOnlyAZ"]["fr"] = "Utilisez des noms incluant Uniquement A-Z , a-z , 0-9, _ ou -";
$msg["MAIN"]["LevelAlreadyExists"]["fr"] = "Ce niveau existe";
$msg["MAIN"]["EmptyDescription"]["fr"] = "Description Vide";

  // NL
$msg["MAIN"]["lang"]["nl"] = "Neederlands"; // Declares NL Language Variables
$msg["MAIN"]["title"]["nl"] = "SmartCAN Beheer";
$msg["MAIN"]["forbidden"]["nl"] = "Verboden Toegang";
$msg["MAIN"]["bepatient"]["nl"] = "Wees Geduldig";
$msg["MAIN"]["reload"]["nl"]    = "<noscript>Deze pagina binnen 120 </noscript></div> seconden herladen, AUB";
$msg["MAIN"]["shutdown"]["nl"]  = "System SHUTDOWN";
$msg["MAIN"]["Rebooting"]["nl"]  = "System REBOOTing";
$msg["MAIN"]["javacfrm1"]["nl"]  = "Ben je zeker dat jij een ";
$msg["MAIN"]["javacfrm2"]["nl"]  = "van het systeem wilt uitvoeren";
$msg["MAIN"]["next"]["nl"]  = "Volgende";
$msg["MAIN"]["Pass"]["nl"]  = "Pass";
$msg["MAIN"]["update"]["nl"]  = "Bijwerken";
$msg["MAIN"]["Incorrect"]["nl"]  = "ONJUIST!";
$msg["MAIN"]["Check"]["nl"] = "Check";
$msg["MAIN"]["Password"]["nl"] = "Wachtwoord";
$msg["MAIN"]["Language"]["nl"] = "Taal";
$msg["MAIN"]["ChangeSaved"]["nl"] = "Verandering Gered !";
$msg["MAIN"]["BePatient"]["nl"] = "Wees geduldig ... ";
$msg["MAIN"]["Seconds"]["nl"] = "Seconden";
$msg["MAIN"]["Identifier"]["nl"] = "Niveau";
$msg["MAIN"]["Description"]["nl"] = "Beschrijving";
$msg["MAIN"]["RuSure"]["nl"] = "Ben je zeker ?";
$msg["MAIN"]["Name"]["nl"] = "Naam";
$msg["MAIN"]["Choose"]["nl"] = "Kies";
$msg["MAIN"]["Select"]["nl"] = "Selecteer";
$msg["MAIN"]["Module"]["nl"] = "Module";
$msg["MAIN"]["Output"]["nl"] = "Uitgang";
$msg["MAIN"]["Delay"]["nl"] = "Vertraging";
$msg["MAIN"]["Memory"]["nl"] = "Geheug";
$msg["MAIN"]["Icon"]["nl"] = "Icoon";
$msg["MAIN"]["Save"]["nl"] = "Opslaan";
$msg["MAIN"]["NotInstalled"]["nl"] = "NIET Geinstalleerd";
$msg["MAIN"]["Installed"]["nl"] = "Geinstalleerd";
$msg["MAIN"]["Status"]["nl"] = "Toestand";
$msg["MAIN"]["Stopped"]["nl"] = "Gestopt";
$msg["MAIN"]["Actif"]["nl"] = "ACTIEF";
$msg["MAIN"]["NOK"]["nl"] = "NOK";
$msg["MAIN"]["Path"]["nl"] = "Path";
$msg["MAIN"]["DBerror"]["nl"] = "DB Foutmelding";
$msg["MAIN"]["DBempty"]["nl"] = "DB Leeg";
$msg["MAIN"]["FileCopyError"]["nl"] = "File Copy FOUT";
$msg["MAIN"]["IncorrectFileType"]["nl"] = "VERKEERD File Type";
$msg["MAIN"]["NoFileError"]["nl"] = "Geen File !";
$msg["MAIN"]["UseOnlyPNG"]["nl"] = "Gebruik ALLEEN .png file";
$msg["MAIN"]["FileDeleteError"]["nl"] = "Kan bestanden NIET verwijderen";
$msg["MAIN"]["OtherLevels"]["nl"] = "Andere Niveaus";
$msg["MAIN"]["EditLevel"]["nl"] = "Niveau Bewerken";
$msg["MAIN"]["ChangeLevelImage"]["nl"] = "Niveau Beeld veranderen";
$msg["MAIN"]["AddLevel"]["nl"] = "Nivaeu Toevoegen";
$msg["MAIN"]["Close"]["nl"] = "Sluiten";
$msg["MAIN"]["LevelImage"]["nl"] = "Nivaeu Beeld ( .png Formaat, 500x355 Pixels)";
$msg["MAIN"]["Modify"]["nl"] = "Aanpassen";
$msg["MAIN"]["LevelName"]["nl"] = "Niveau Naam";
$msg["MAIN"]["Add"]["nl"] = "Toevoegen";
$msg["MAIN"]["EmptyLevelName"]["nl"] = "Leeg Niveau Naam";
$msg["MAIN"]["UseOnlyAZ"]["nl"] = "Gebruik namen met allean A-Z , a-z , 0-9, _ ou -";
$msg["MAIN"]["LevelAlreadyExists"]["nl"] = "Deze Niveau Besta al";
$msg["MAIN"]["EmptyDescription"]["nl"] = "Leeg Beschrijving";


// Top Menus
  // EN
$msg["TOPMENU"][1]["en"]  = "Status"; 
$msg["TOPMENU"][2]["en"]  = "&nbsp;";
$msg["TOPMENU"][3]["en"]  = "Thermics";
$msg["TOPMENU"][4]["en"]  = "Lamps and Plugs";
$msg["TOPMENU"][5]["en"]  = "Temperatures";
$msg["TOPMENU"][6]["en"]  = "Vibes";
$msg["TOPMENU"][7]["en"]  = "Surveillance";
$msg["TOPMENU"][8]["en"]  = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$msg["TOPMENU"][9]["en"]  = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$msg["TOPMENU"][10]["en"] = "Modules";
$msg["TOPMENU"][11]["en"] = "Admin";
$msg["INSTALLMOD"][11]["en"] = "Install Module";
$msg["ADMINMENU"][1]["en"]        = "Reboot System";
$msg["ADMINMENU"][2]["en"]        = "Shutdown System";
$msg["ADMINMENU"][4]["en"]        = "Graphs & Temp Logs";
$msg["ADMINMENU"][5]["en"]        = "Variables";
$msg["ADMINMENU"][6]["en"]        = "Backup & Restore";
$msg["ADMINMENU"][7]["en"]        = "About SmartCAN";
$msg["ADMINMENU"][8]["en"]        = "LogOut";
$msg["CONFIRM"]["REBOOT"]["en"]   = "reboot";
$msg["CONFIRM"]["SHUTDOWN"]["en"] = "Shutdown";

  // FR
$msg["TOPMENU"][1]["fr"]  = "Status"; 
$msg["TOPMENU"][2]["fr"]  = "&nbsp;";
$msg["TOPMENU"][3]["fr"]  = "Thermie";
$msg["TOPMENU"][4]["fr"]  = "Lampes et Prises";
$msg["TOPMENU"][5]["fr"]  = "Temp&eacute;ratures";
$msg["TOPMENU"][6]["fr"]  = "Ambiances";
$msg["TOPMENU"][7]["fr"]  = "Surveillance";
$msg["TOPMENU"][8]["fr"]  = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$msg["TOPMENU"][9]["fr"]  = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$msg["TOPMENU"][10]["fr"] = "Modules";
$msg["TOPMENU"][11]["fr"] = "Admin";
$msg["INSTALLMOD"][11]["fr"] = "Install Module";
$msg["ADMINMENU"][1]["fr"]        = "Reboot System";
$msg["ADMINMENU"][2]["fr"]        = "Shutdown System";
$msg["ADMINMENU"][4]["fr"]        = "Graphes - Temp&eacute;ratures";
$msg["ADMINMENU"][5]["fr"]        = "Variables";
$msg["ADMINMENU"][6]["fr"]        = "Backup & Restore";
$msg["ADMINMENU"][7]["fr"]        = "A Propos de SmartCAN";
$msg["ADMINMENU"][8]["fr"]        = "LogOut";
$msg["CONFIRM"]["REBOOT"]["fr"]   = "red&eacute;marrer";
$msg["CONFIRM"]["SHUTDOWN"]["fr"] = "arr&ecirc;ter";

  // NL
$msg["TOPMENU"][1]["nl"]  = "Toestand"; 
$msg["TOPMENU"][2]["nl"]  = "&nbsp;";
$msg["TOPMENU"][3]["nl"]  = "Thermiek";
$msg["TOPMENU"][4]["nl"]  = "Lampen/Stopcontacten";
$msg["TOPMENU"][5]["nl"]  = "Temperaturen";
$msg["TOPMENU"][6]["nl"]  = "Sferen";
$msg["TOPMENU"][7]["nl"]  = "Bewaking";
$msg["TOPMENU"][8]["nl"]  = "&nbsp;";
$msg["TOPMENU"][9]["nl"]  = "&nbsp;";
$msg["TOPMENU"][10]["nl"] = "Modulen";
$msg["TOPMENU"][11]["nl"] = "Beheer";
$msg["INSTALLMOD"][11]["nl"] = "Installeer Module";
$msg["ADMINMENU"][1]["nl"]        = "Systeem Erladen";
$msg["ADMINMENU"][2]["nl"]        = "Systeem Stopzetten";
$msg["ADMINMENU"][4]["nl"]        = "Grafieken en Temp Logboek";
$msg["ADMINMENU"][5]["nl"]        = "Variablen";
$msg["ADMINMENU"][6]["nl"]        = "Backup & Restore";
$msg["ADMINMENU"][7]["nl"]        = "Over SmartCAN";
$msg["ADMINMENU"][8]["nl"]        = "LogOut";
$msg["CONFIRM"]["REBOOT"]["nl"]   = "erladen";
$msg["CONFIRM"]["SHUTDOWN"]["nl"] = "Stopzetten";



// END Top Menu




?>