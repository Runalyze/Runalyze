<?php header('Content-type: text/html; charset=ISO-8859-1');
include_once('../config/functions.php');
connect();
?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>Neuen Schuh erstellen</h1>

<form action="?action=do" method="post"><input type="hidden" name="type"
	value="schuh" /> <input type="hidden" name="time"
	value="<?php echo(time()); ?>" /> <input type="text" name="name"
	size="50" /> <small>Name</small> <br />
<input type="text" name="marke" size="15" /> <small>Marke</small> <br />
<input type="text" name="kaufdatum"
	value="<?php echo(date("d.m.Y")); ?>" size="15" /> <small>Kaufdatum</small>
<br />
<input type="submit" value="Eintragen" /></form>

<br />
<br />

<h1>Schuhe bearbeiten</h1>

<form action="?action=do" method="post"><input type="hidden" name="type"
	value="schuh_unuse" /> <select name="schuhid">
	<?php
	$db = mysql_query('SELECT * FROM `ltb_schuhe` WHERE `inuse`=1 ORDER BY `id` ASC');
	while($schuh = mysql_fetch_array($db)) {
		echo('
		<option value="'.$schuh['id'].'">'.$schuh['name'].'</option>'.NL);
	}
	?>
</select> <input type="submit" value="Nicht mehr nutzen" /></form>
