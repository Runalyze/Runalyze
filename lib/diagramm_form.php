<?php
header('Content-type: text/html; charset=ISO-8859-1');
$jahr = ($_GET['jahr']=='undefined' || !isset($_GET['jahr'])) ? date("Y") : $_GET['jahr'];
?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>Form <?php echo($jahr); ?></h1>

<img
	src="lib/draw/mued.php?jahr=<?php echo($jahr); ?>" />
<br />
<br />

<center><?php
include_once('../config/functions.php');
connect();

for ($j = $config['startjahr']; $j <= date("Y"); $j++)
echo('
	<span class="link" onClick="diagramm(\'form\',\''.$j.'\')" style="margin-right:20px;">'.$j.'</span>');
?></center>
