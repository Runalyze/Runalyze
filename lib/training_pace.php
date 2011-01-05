<?php
include_once('../config/functions.php');
connect();

$db = mysql_query('SELECT `id`,`splits`,`arr_pace` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($db);

if ($dat['arr_pace'] != ''):
?>
<img src="http://localhost/ltb/lib/draw/training_pace.php?id=<?php echo $_GET['id']; ?>" /><br /><br />
<?php
endif;

// Splits
if ($dat['splits'] != ''):
$dat['splits'] = str_replace("\r\n", "-", $dat['splits']);
$splits = explode("-", $dat['splits']);
?>		
<strong>Zwischenzeiten:</strong><br />	
<img src="lib/draw/splits.php?id=<?php echo $dat['id']; ?>" /><br />			
<table cellspacing="0" style="width:480px;">
	<tr>
<?php
for ($i = 0; $i < count($splits); $i++) {
	$split = explode("|", $splits[$i]);
	$zeit_dat = explode(":", $split[1]);
	$distanz[] = $split[0];
	$zeit[] = round(($zeit_dat[0]*60 + $zeit_dat[1])/$split[0]);
		
	$border = ($i+1)%3 != 0 ? ' style="border-right:1px solid #CCC;"' : '';
		
	echo('
		<td class="a'.($i%2+1).' b">'.km($split[0]).'</td>
		<td class="a'.($i%2+1).'">'.dauer($zeit_dat[0]*60 + $zeit_dat[1]).'</td>
		<td class="a'.($i%2+1).'"'.$border.'><small>'.pace(1,$zeit[$i]).'/km</small></td>');
		
	if (($i+1)%3 == 0)
		echo('
	</tr>
	<tr>');
	if ($i == (count($splits)-1))
		echo('
		<td class="a'.($i%2+1).'" colspan="'.(9 - 3*($i+1)%3).'" />');
}
?>
	</tr>
</table>
<?php endif; ?>
<?php
close();
?>