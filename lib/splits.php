<?php header('Content-type: text/html; charset=ISO-8859-1'); ?>
	<img id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>Zwischenzeiten</h1>
<?php
include_once('../config/functions.php');
connect();

$db = mysql_query('SELECT `id`, `splits`, `bemerkung` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($db);

$dat['splits'] = str_replace("\r\n", "-", $dat['splits']);
$splits = explode("-", $dat['splits']);

$tempo_soll = explode("in ", $dat['bemerkung']);
$tempo_soll = explode(",", $tempo_soll[1]);
$tempo_ist = 0;
?>
<img src="lib/draw/splits.php?id=<?php echo $dat['id']; ?>" /><br />			
<table cellspacing="0" style="width:480px;">
	<tr>
<?php
for ($i = 0; $i < count($splits); $i++) {
	$split = explode("|", $splits[$i]);
	$zeit_dat = explode(":", $split[1]);
	$distanz[] = $split[0];
	$zeit[] = round(($zeit_dat[0]*60 + $zeit_dat[1]));
	$tempo_ist += $zeit[$i]/$split[0];

	$border = ($i+1)%3 != 0 ? ' style="border-right:1px solid #CCC;"' : '';

	echo('<td class="a'.($i%2+1).' b">'.km($split[0]).'</td><td class="a'.($i%2+1).'">'.dauer($zeit[$i]).'</td><td class="a'.($i%2+1).'"'.$border.'><small>'.pace($split[0],$zeit[$i]).'/km</small></td>');

	if (($i+1)%3 == 0) echo('</tr><tr>'.NL);
	if ($i == (count($splits)-1)) echo('<td class="a'.($i%2+1).'" colspan="'.(9 - 3*($i+1)%3).'" />');
}

$tempo_ist /= count($splits);

close();
?>
	</tr>
</table>
<br />
<?php
$tempo_zu = "";
$tempo_soll = explode(":", $tempo_soll[0]);
if ($tempo_soll[1] != ''):
	$tempo_soll = 60*$tempo_soll[0] + $tempo_soll[1];
	if ($tempo_soll >= ($tempo_ist + 10))
		$tempo_zu = "schnell";
	elseif ($tempo_soll <= ($tempo_ist - 10))
		$tempo_zu = "langsam";
	if ($tempo_zu != "")
		echo ('<span class="right"><img src="img/warning.png" alt="Falsches Tempo!" /> Du warst zu <em>'.$tempo_zu.'</em>.</span>');
?>
<strong>Vorgabe:</strong> <?php echo pace(1,$tempo_soll); ?>/km<br />
<?php
endif;
?>
<strong>Ergebnis:</strong> <?php echo pace(1,$tempo_ist); ?>/km<br />