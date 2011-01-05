<?php header('Content-type: text/html; charset=ISO-8859-1'); ?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>Wetter-Diagramme <?php echo ($_GET['jahr'] == "all" ? "Gesamt" : $_GET['jahr']); ?></h1>
<center><?php if ($_GET['jahr'] == "all"): ?> <img height="242"
	width="782" src="lib/draw/weather.php?all=all&m=m" /><br />
<?php else: ?> <img height="242" width="782"
	src="lib/draw/weather.php?y=<?php echo($_GET['jahr']); ?>&m=m" /><br />
<br />
<img height="242" width="782"
	src="lib/draw/weather.php?y=<?php echo($_GET['jahr']); ?>" /><br />
<?php endif; ?> <br />
<?php
include_once('../config/functions.php');
connect();

for ($j = $config['startjahr']; $j <= date("Y"); $j++)
echo('
	<a href="#" onClick="diagramm(\'wetter\',\''.$j.'\')" style="margin-right:20px;">'.$j.'</a>');
?> <a href="#" onClick="diagramm('wetter','all')"
	style="margin-right: 20px;">Gesamt</a></center>
