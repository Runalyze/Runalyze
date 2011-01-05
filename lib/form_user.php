<?php header('Content-type: text/html; charset=ISO-8859-1');
include_once('../config/functions.php');
connect();

$db = mysql_query('SELECT * FROM `ltb_user` ORDER BY `time` DESC LIMIT 1');
$dat = mysql_fetch_assoc($db);
?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>K&ouml;rper-Daten eingeben</h1>
<form action="?action=do" method="post"><input type="hidden" name="type"
	value="user" /> <input type="hidden" name="time"
	value="<?php echo(time()); ?>" /> <input type="text" name="gewicht"
	value="<?php echo($dat['gewicht']); ?>" size="5" /> <small>Gewicht</small><br />
<?php if ($config['use_koerperfett'] == 1): ?> <input type="text"
	name="fett" value="<?php echo($dat['fett']); ?>" size="5" /> <small>&#37;
Fett</small><br />
<input type="text" name="wasser" value="<?php echo($dat['wasser']); ?>"
	size="5" /> <small>&#37; Wasser</small><br />
<input type="text" name="muskeln"
	value="<?php echo($dat['muskeln']); ?>" size="5" /> <small>&#37;
Muskeln</small><br />
<?php endif; ?> <?php if ($config['use_ruhepuls'] == 1): ?> <br />
<input type="text" name="puls_ruhe"
	value="<?php echo($dat['puls_ruhe']); ?>" size="5" /> <small>Ruhepuls</small><br />
<input type="text" name="puls_max"
	value="<?php echo($dat['puls_max']); ?>" size="5" /> <small>Maximalpuls</small><br />
<?php endif; ?> <?php if ($config['use_blutdruck'] == 1): ?> <br />
<input type="text" name="blutdruck_min"
	value="<?php echo($dat['blutdruck_min']); ?>" size="5" /> <small>zu</small>
<input type="text" name="blutdruck_max"
	value="<?php echo($dat['blutdruck_max']); ?>" size="5" /> <small>Blutdruck</small><br />
<?php endif; ?> <input type="submit" value="Eintragen" /></form>
