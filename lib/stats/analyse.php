<?php
if (!$include_sports) {
	header('Content-type: text/html; charset=ISO-8859-1');
	include_once('../../config/functions.php');
	connect();
}

if ($_GET['jahr'] == -1) {
	$i = 0;
	$jahr = "Jahresvergleich";
}

else {
	$i = ($_GET['jahr']=='undefined' || !isset($_GET['jahr'])) ? date("Y") : $_GET['jahr'];
	$jahr = $i;
}
?>
<h1>Training <?php echo($jahr); ?></h1>

<small class="right">
<?php
for ($x = $config['startjahr']; $x <= date("Y"); $x++) {
	echo('
	<a class="ajax" href="lib/stats/analyse.php?jahr='.$x.'" target="tab_content">'.$x.'</a> |');
}
?>
	<a class="ajax" href="lib/stats/analyse.php?jahr=-1" target="tab_content">Jahresvergleich</a>
</small>

<br class="clear" />

<table cellspacing="0" width="100%" class="small r">
	<tr class="b">
		<td />
<?php if ($_GET['jahr'] != -1): ?>
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
<?php
else:
	for ($j = $config['startjahr']; $j <= date("Y"); $j++)
		echo('		<td>'.$j.'</td>'.NL);
	echo('		<td>Gesamt</td>'.NL);
endif;
?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php
$i = 0;
$typ_db = mysql_query('SELECT * FROM `ltb_typ` ORDER BY `RPE` ASC');
while ($typ = mysql_fetch_assoc($typ_db)):
	$i++;
?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b" title="<?php echo($typ['name']); ?>"><?php echo($typ['abk']); ?></td>
<?php
if ($_GET['jahr'] != -1):
	for ($m = 1; $m <= 12; $m++) {
		$m_db = mysql_query('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
		$m_dat = mysql_fetch_assoc($m_db);
		$dat_db = mysql_query('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `typid`='.$typ['id'].' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$jahr.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
		$dat = mysql_fetch_assoc($dat_db);
		echo ($dat['num'] != 0) ? '<td title="'.$dat['num'].'x - '.km($dat['distanz']).'">'.(round(1000*$dat['distanz']/$m_dat['distanz'])/10).' &#37;</td>' : '<td>&nbsp;</td>';
	}
else:
	for ($j = $config['startjahr']; $j <= date("Y"); $j++) {
		$j_db = mysql_query('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$j);
		$j_dat = mysql_fetch_assoc($j_db);
		$dat_db = mysql_query('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `typid`='.$typ['id'].' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$j);
		$dat = mysql_fetch_assoc($dat_db);
		echo ($dat['num'] != 0) ? '<td title="'.$dat['num'].'x - '.km($dat['distanz']).'">'.(round(1000*$dat['distanz']/$j_dat['distanz'])/10).' &#37;</td>' : '<td>&nbsp;</td>';
	}

	$all_db = mysql_query('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1');
	$all_dat = mysql_fetch_assoc($all_db);
	$dat_db = mysql_query('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `typid`='.$typ['id'].' && `sportid`=1');
	$dat = mysql_fetch_assoc($dat_db);
	echo ($dat['num'] != 0) ? '<td title="'.$dat['num'].'x - '.km($dat['distanz']).'">'.(round(1000*$dat['distanz']/$all_dat['distanz'])/10).' &#37;</td>' : '<td>&nbsp;</td>';
endif;
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endwhile; ?>
</table>