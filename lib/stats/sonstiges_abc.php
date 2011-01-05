<h1>H&ouml;henmeter</h1>

<table cellspacing="0" width="100%" class="small">
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
	<tr class="space">
		<td colspan="13">
		</td>
	</tr>
<?php
for ($jahr = $config['startjahr']; $jahr <= date("Y"); $jahr++):
	if (mysql_num_rows(mysql_query('SELECT 1 FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$jahr.'" AND `laufabc`!=0 LIMIT 1')) > 0):
?>
	<tr class="a<?php echo($jahr%2+1); ?> r">
		<td class="b l">
			<?php echo $jahr; ?>
		</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$monat_db = mysql_query('SELECT SUM(`laufabc`) as `abc`, SUM(1) as `num`, 1 as `group` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$jahr.'" AND MONTH(FROM_UNIXTIME(`time`))="'.$m.'" GROUP BY `group` LIMIT 1');
	while ($monat = mysql_fetch_assoc($monat_db)) {
		$link = '<span class="link" onclick="submit_suche(\'opt[laufabc]=is&val[laufabc]=1&time-gt=01.'.$m.'.'.$jahr.'&time-lt=00.'.($m+1).'.'.$jahr.'\')" title="'.$monat['abc'].'x">'.round(100*$monat['abc']/$monat['num']).' &#37;</span>';
		echo('
		<td>
			'.($monat['abc'] != 0 ? $link	: '&nbsp;').'
		</td>');
	}
	if (mysql_num_rows($monat_db) == 0)
		echo('
		<td>&nbsp;</td>');
}
?>
<?php
	endif;
endfor;
?>
	</tr>
	<tr class="space">
		<td colspan="13">
		</td>
	</tr>
</table>