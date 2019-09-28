<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Messages
   // EN
$msg["BACKUPRESTORE"]["back2default"]["en"] = "Destination back to DEFAULT";
$msg["BACKUPRESTORE"]["ftptested"]["en"] = "FTP Tested - ok";
$msg["BACKUPRESTORE"]["ftpNOK"]["en"] = "FTP Tested - NOK!";
$msg["BACKUPRESTORE"]["sftptested"]["en"] = "SFTP Tested - ok";
$msg["BACKUPRESTORE"]["sftpNOK"]["en"] = "SFTP Tested - NOK!";
$msg["BACKUPRESTORE"]["moveftpOK"]["en"] = "FTP ok";
$msg["BACKUPRESTORE"]["movesftpOK"]["en"] = "SFTP ok";
$msg["BACKUPRESTORE"]["restore"]["en"] = "Restore";
$msg["BACKUPRESTORE"]["sftpError"]["en"] = "SFTP Error!";
$msg["BACKUPRESTORE"]["SQLrestored"]["en"] = "SQL File restored: ";
$msg["BACKUPRESTORE"]["CopyError"]["en"] = "File Copy ERROR!!!";
$msg["BACKUPRESTORE"]["IncorrectFile"]["en"] = "INCORRECT File Type!!!";
$msg["BACKUPRESTORE"]["TitleBackup"]["en"] = "Backup";
$msg["BACKUPRESTORE"]["DBrestoreOK"]["en"] = "DB Restore successfully!";
$msg["BACKUPRESTORE"]["DecompressNOK"]["en"] = "DECOMPRESSION ERROR!!!";
$msg["BACKUPRESTORE"]["BackupDest"]["en"] = "Backup Target (Directory):";
$msg["BACKUPRESTORE"]["BackupMsg1"]["en"] = "Leave Empty if local and/or Manual Backup</font>, files will then be storedon /var/www/backups";
$msg["BACKUPRESTORE"]["BackupMsg2"]["en"] = "Format: ftp://user:password@IP_NAS/directory<br>or&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sftp://user:password@IP_NAS/directory";
$msg["BACKUPRESTORE"]["AutoBackup"]["en"] = "Automatic Backup?";
$msg["BACKUPRESTORE"]["DailyBackup"]["en"] = "(Every Day at 4AM)";
$msg["BACKUPRESTORE"]["WeeklyBackup"]["en"] = "(Every Week, on Friday 4AM)";
$msg["BACKUPRESTORE"]["MonthlyBackup"]["en"] = "(Every first of the Month at 4AM)";
$msg["BACKUPRESTORE"]["ManualBackup"]["en"] = "Manual Backup";
$msg["BACKUPRESTORE"]["AvailableBackup"]["en"] = "Available Backup Files:";
$msg["BACKUPRESTORE"]["NoBackupFile"]["en"] = "None!";
$msg["BACKUPRESTORE"]["BackupUpload"]["en"] = "Backup File Upload";
$msg["BACKUPRESTORE"]["ConfirmRestore"]["en"] = "Are you sure you want to Restore";
$msg["BACKUPRESTORE"]["SQLQueryNOK"]["en"] = "Error performing query";

   // FR
$msg["BACKUPRESTORE"]["back2default"]["fr"] = "Destination r&eacute;initialis&eacute;e &agrave; la valeur par DEFAUT";
$msg["BACKUPRESTORE"]["ftptested"]["fr"] = "FTP Test&eacute; - ok";
$msg["BACKUPRESTORE"]["ftpNOK"]["fr"] = "FTP Test&eacute; - NOK!";
$msg["BACKUPRESTORE"]["sftptested"]["fr"] = "SFTP Test&eacute; - ok";
$msg["BACKUPRESTORE"]["sftpNOK"]["fr"] = "SFTP Test&eacute; - NOK!";
$msg["BACKUPRESTORE"]["moveftpOK"]["fr"] = "FTP ok";
$msg["BACKUPRESTORE"]["movesftpOK"]["fr"] = "SFTP ok";
$msg["BACKUPRESTORE"]["restore"]["fr"] = "Restore";
$msg["BACKUPRESTORE"]["sftpError"]["fr"] = "Erreur SFTP!";
$msg["BACKUPRESTORE"]["SQLrestored"]["fr"] = "Fichier SQL restor&eacute;: ";
$msg["BACKUPRESTORE"]["CopyError"]["fr"] = "ERREUR de copie!!!";
$msg["BACKUPRESTORE"]["IncorrectFile"]["fr"] = "Type de fichier INCORRECT!!!";
$msg["BACKUPRESTORE"]["TitleBackup"]["fr"] = "Backup";
$msg["BACKUPRESTORE"]["DBrestoreOK"]["fr"] = "DB Restor&eacute;e avec succ&egrave;s!";
$msg["BACKUPRESTORE"]["DecompressNOK"]["fr"] = "ERREUR de DECOMPRESSION!!!";
$msg["BACKUPRESTORE"]["BackupDest"]["fr"] = "Destination du Backup (Directory):";
$msg["BACKUPRESTORE"]["BackupMsg1"]["fr"] = "Laissez vide si local et/ou archivage Manuel</font>, les fichiers seront stock&eacute;s alors sur /var/www/backups";
$msg["BACKUPRESTORE"]["BackupMsg2"]["fr"] = "Format: ftp://user:password@IP_NAS/directory<br>ou&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sftp://user:password@IP_NAS/directory";
$msg["BACKUPRESTORE"]["AutoBackup"]["fr"] = "Backup Automatique?";
$msg["BACKUPRESTORE"]["DailyBackup"]["fr"] = "(Tous les jours &agrave; 4h du matin)";
$msg["BACKUPRESTORE"]["WeeklyBackup"]["fr"] = "(Toutes les semaines, le Vendredi &agrave; 4h du matin)";
$msg["BACKUPRESTORE"]["MonthlyBackup"]["fr"] = "(Tous les premiers du mois &agrave; 4h du matin)";
$msg["BACKUPRESTORE"]["ManualBackup"]["fr"] = "Backup Manuel";
$msg["BACKUPRESTORE"]["AvailableBackup"]["fr"] = "Fichiers de backup disponibles:";
$msg["BACKUPRESTORE"]["NoBackupFile"]["fr"] = "Aucun!";
$msg["BACKUPRESTORE"]["BackupUpload"]["fr"] = "Upload d'un fichier de backup";
$msg["BACKUPRESTORE"]["ConfirmRestore"]["fr"] = "Etes vous certain de vouloir restorer";
$msg["BACKUPRESTORE"]["SQLQueryNOK"]["fr"] = "Error d'ex&eacute;cution SQL";

   // NL
$msg["BACKUPRESTORE"]["back2default"]["nl"] = "File besteming terug naar SATNDAARD";
$msg["BACKUPRESTORE"]["ftptested"]["nl"] = "FTP Getest - ok";
$msg["BACKUPRESTORE"]["ftpNOK"]["nl"] = "FTP Getest - NOK!";
$msg["BACKUPRESTORE"]["sftptested"]["nl"] = "SFTP Getest - ok";
$msg["BACKUPRESTORE"]["sftpNOK"]["nl"] = "SFTP Getest - NOK!";
$msg["BACKUPRESTORE"]["moveftpOK"]["nl"] = "FTP ok";
$msg["BACKUPRESTORE"]["movesftpOK"]["nl"] = "SFTP ok";
$msg["BACKUPRESTORE"]["restore"]["nl"] = "Restore";
$msg["BACKUPRESTORE"]["sftpError"]["nl"] = "SFTP Overtreding!";
$msg["BACKUPRESTORE"]["SQLrestored"]["nl"] = "SQL File hersteld: ";
$msg["BACKUPRESTORE"]["CopyError"]["nl"] = "File Copy OVERTERDING!!!";
$msg["BACKUPRESTORE"]["IncorrectFile"]["nl"] = "INCORRECTE File Type!!!";
$msg["BACKUPRESTORE"]["TitleBackup"]["nl"] = "Backup";
$msg["BACKUPRESTORE"]["DBrestoreOK"]["nl"] = "DB Restore successvol!";
$msg["BACKUPRESTORE"]["DecompressNOK"]["nl"] = "DECOMPRESSIE Overtreding!!!";
$msg["BACKUPRESTORE"]["BackupDest"]["nl"] = "Backup Bestemming (Directory):";
$msg["BACKUPRESTORE"]["BackupMsg1"]["nl"] = "Leeg laten voor locaal en/of Manueel Backup</font>, fileszulen dan binnen /var/www/backups opslaan zijn";
$msg["BACKUPRESTORE"]["BackupMsg2"]["nl"] = "Formaat: ftp://user:password@IP_NAS/directory<br>or&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sftp://user:password@IP_NAS/directory";
$msg["BACKUPRESTORE"]["AutoBackup"]["nl"] = "Automatisch Backup?";
$msg["BACKUPRESTORE"]["DailyBackup"]["nl"] = "(Elke Dag om 4AM)";
$msg["BACKUPRESTORE"]["WeeklyBackup"]["nl"] = "(Elke Week, Vrijdag om 4AM)";
$msg["BACKUPRESTORE"]["MonthlyBackup"]["nl"] = "(Ieder eerst dag van de maand om 4AM)";
$msg["BACKUPRESTORE"]["ManualBackup"]["nl"] = "Manueel Backup";
$msg["BACKUPRESTORE"]["AvailableBackup"]["nl"] = "Beschikbaar Backup Files:";
$msg["BACKUPRESTORE"]["NoBackupFile"]["nl"] = "Geen!";
$msg["BACKUPRESTORE"]["BackupUpload"]["nl"] = "Backup File Upload";
$msg["BACKUPRESTORE"]["ConfirmRestore"]["nl"] = "Ben je zeker dat je de Restore wilt uitvoeren";
$msg["BACKUPRESTORE"]["SQLQueryNOK"]["nl"] = "DB Query Overtreding";


?>