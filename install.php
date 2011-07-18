<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Installation: Runalyze</title>
</head>

<body>

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel" style="display:block;">
	<h1>Installation von Runalyze</h1>

	Herzlich Willkommen bei Runaylze, deinem neuen Lauftagebuch.<br />
	<br />
	Eine vollautomatisierte Installation ist derzeit noch nicht m&ouml;lich,
	sodass du die folgenden Schritte manuell durchf&uuml;hren musst:<br />
	<br />
	<strong>1. Schritt: Datenbank</strong><br />
	In der Konfigurationsdatei <code>inc/config.inc.php</code> musst du deine MySql-Datenbank-Verbindung angeben:<br />
	<code style="display:block;padding-left:20px;">
		$host = 'localhost';<br />
		$database = 'runalyze';<br />
		$username = 'runalyze';<br />
		$password = 'runalyze';
	</code>
	<br />
	<strong>2. Schritt: Daten hochladen</strong><br />
	Als n&auml;chstes muss die Datenbank bef&uuml;llt werden. Dazu &ouml;ffnest du am besten den
	<code>phpMyAdmin</code> im Browser und navigierst zu der Datenbank f&uuml;r Runalyze.<br />
	<em>Importieren</em> &raquo; <code>inc/install/structure.sql</code><br />
	<em>Importieren</em> &raquo; <code>inc/install/runalyze_empty.sql</code><br />
	<br />
	<strong>3. Schritt: Konfiguration</strong><br />
	Wenn du nun <code>index.php</code> aufrufst, sollte dein neues Lauftagebuch erscheinen.<br />
	Auf der unteren Leiste findest du ganz links den Link zur Konfiguration.
	Dort kannst du nun die entsprechenden Einstellungen vornehmen - und schon kann es losgehen.<br />
	<br />
	Viel Spa&szlig; beim Eintragen*!<br />
	<br />
	<br />
	Um Missbrauch zu verhindern, kannst du nun diese Datei <code>install.php</code> l&ouml;schen.<br />
	<br />
	<small>
		Wenn noch irgendwelche Probleme auftauchen, kannst du diese am besten in unserem
		<a href="https://sourceforge.net/apps/trac/runalyze/newticket">Ticket-System</a> melden.
		Wir k&uuml;ern uns dann schnellstm&ouml;glich darum.
	</small><br />
	<br />
	<small>
		* Ein Massenimporter ist noch in der Entwicklung.
		Wenn du uns deine Daten als Excel- bzw. CSV-Datei zukommen l&auml;sst <small>(mail&#64;laufhannes.de)</small>,
		bereiten wir dir deine bisherigen Trainings aber auch auf zum direkten Import. 
	</small>
</div>

<div id="copy">

	<span class="right">
		&copy; Programmierung und Design von
		<a id="copy" href="http://www.laufhannes.de/" title="Laufhannes" target="_blank">laufhannes.de</a>
	</span>

	<span class="left b">
		Runalyze
	</span>

</div>

</body>
</html>