<?php
if (!$include_sports) {
	header('Content-type: text/html; charset=ISO-8859-1');
	include_once('../../config/functions.php');
	connect();
}

if ($_GET['jahr'] == -1) {
	$i = 0;
	$jahr = "Gesamt";
}

else {
	$i = ($_GET['jahr']=='undefined' || !isset($_GET['jahr'])) ? date("Y") : $_GET['jahr'];
	$jahr = $i;
}

$sportid = ($_GET['sport']=='undefined' || !isset($_GET['sport'])) ? 1 : $_GET['sport'];
$sport_db = mysql_query('SELECT * FROM `ltb_sports` WHERE `id`='.$sportid.' LIMIT 1');
$sport = mysql_fetch_assoc($sport_db);
?>
<h1><?php echo(sport($sportid).' '.$jahr); ?></h1>
 
<small class="left">
<?php
$first = true;
$sportlink_db = mysql_query('SELECT * FROM `ltb_sports` ORDER BY `id` ASC');
while ($sportlink = mysql_fetch_assoc($sportlink_db)) {
	if (!$first)
		echo(' |'.NL);
	else
		$first = false;
	echo('
	<a class="ajax" href="lib/stats/index.php?jahr='.$jahr.'&sport='.$sportlink['id'].'" target="tab_content">'.$sportlink['name'].'</a>'.NL);
}
?>
</small>

<small class="right">
<?php
for ($x = $config['startjahr']; $x <= date("Y"); $x++) {
	echo('	<a class="ajax" href="lib/stats/index.php?jahr='.$x.'&sport='.$sportid.'" target="tab_content">'.$x.'</a>'.NL);
	if ($x != date("Y"))
		echo(' |'.NL);
}
?>
</small>

<br class="clear" />

<table cellspacing="0" width="100%" class="small c">
	<tr class="b">
		<td />
		<td width="8%">Jan</td>
		<td width="8%">Feb</td>
		<td width="8%">Mrz</td>
		<td width="8%">Apr</td>
		<td width="8%">Mai</td>
		<td width="8%">Jun</td>
		<td width="8%">Jul</td>
		<td width="8%">Aug</td>
		<td width="8%">Sep</td>
		<td width="8%">Okt</td>
		<td width="8%">Nov</td>
		<td width="8%">Dez</td>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php
$i = 0;
# Kilometer
# Stunden
# (Pace)
# ((VDOT))
# ((TRIMP))
?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b">Stunden</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$dauer_db = mysql_query('SELECT SUM(`dauer`) as `dauer` FROM `ltb_training` WHERE `sportid`='.$sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
	$dauer = mysql_fetch_assoc($dauer_db);
	echo ($dauer['dauer'] != 0) ? '<td>'.dauer($dauer['dauer'],false).'</td>' : '<td />';
}
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php if ($sport['distanztyp'] != 0): $i++; ?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b">KM</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$km_db = mysql_query('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`='.$sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
	$km = mysql_fetch_assoc($km_db);
	echo ($km['distanz'] != 0) ? '<td>'.km($km['distanz'],0).'</td>' : '<td />';
}
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
<?php if ($sport['distanztyp'] != 0): $i++; ?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b">&Oslash;Tempo</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$pace_db = mysql_query('SELECT SUM(`dauer`) as `dauer`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`='.$sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
	$pace = mysql_fetch_assoc($pace_db);
	echo ($pace['dauer'] != 0) ? '<td>'.tempo($pace['distanz'],$pace['dauer'],$sportid).'</td>' : '<td />';
}
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
<?php if ($sportid == 1 && $config['show_rechenspiele'] != 0): $i++; ?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b">VDOT</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$VDOT = 0; $num = 0;
	$VDOT_db = mysql_query('SELECT `id` FROM `ltb_training` WHERE `sportid`='.$sportid.' && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
	while ($VDOT_dat = mysql_fetch_array($VDOT_db)) { $VDOT += jd_VDOT_bereinigt($VDOT_dat['id']); $num++; }	
	if ($num != 0) echo('<td>'.number_format(round($VDOT/$num,1),1).'</td>');
	else echo('<td />');
}
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
<?php if ($config['show_rechenspiele'] != 0): $i++; ?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b">TRIMP</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$trimp_db = mysql_query('SELECT SUM(`trimp`) as `trimp` FROM `ltb_training` WHERE `sportid`='.$sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
	$trimp = mysql_fetch_assoc($trimp_db);
	echo ($trimp['trimp'] != 0) ? '<td style="color:#'.belastungscolor($trimp['trimp']/15).';">'.$trimp['trimp'].'</td>' : '<td />';
}
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
</table>