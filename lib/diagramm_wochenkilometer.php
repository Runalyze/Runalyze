<?php header('Content-type: text/html; charset=ISO-8859-1'); ?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>Wochenkilometer <?php echo($_GET['jahr']); ?></h1>

<img
	src="lib/draw/wochenkilometer.php?jahr=<?php echo($_GET['jahr']); ?>" />
<br />
<br />

<center><?php
include_once('../config/functions.php');
connect();

for ($j = $config['startjahr']; $j <= date("Y"); $j++) {
	echo('<a href="#" onClick="diagramm(\'wochenkilometer\',\''.$j.'\')" style="margin-right:20px;">'.$j.'</a>');
}
?></center>
