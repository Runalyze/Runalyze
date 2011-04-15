<?php
header('Content-type: text/html; charset=ISO-8859-1');
include_once('config/functions.php');
include_once('config/dataset.php');
connect();
	
if (sizeof($_POST) > 0) {
	$submit = true;
	if (!isset($_POST['order'])) {
		$_POST['order'] = 'time';
		$_POST['sort'] = 'DESC';
	}
}
?>

	<span class="r"><img id="close" src="img/cross.png" onClick="closeSuche()" /></span>
<h1>Suche</h1>

<form action="javascript:submit_form_suche();" name="suche" id="form_suche" method="post">
	<span class="right">
		<select name="lorem">
			<option value="ipsum">... vordefinierte Suchen</option>
			<option onclick="submit_suche('opt[typid]=is&val[typid]=3')">alle Intervalltrainings</option>
			<option onclick="submit_suche('opt[typid]=is&val[typid]=4')">alle Tempodauerl&auml;ufe</option>
			<option onclick="submit_suche('opt[typid]=is&val[typid]=7')">alle Langen L&auml;ufe</option>
			<option onclick="submit_suche('opt[bahn]=is&val[bahn]=1')">alle Bahnl&auml;ufe</option>
			<option onclick="submit_suche('opt[trainingspartner]=isnot&val[trainingspartner]=')">alle Trainings mit Trainingspartner</option>
			<option onclick="submit_suche('opt[temperatur]=lt&val[temperatur]=0')">alle Trainings bei Minusgraden</option>
			<option onclick="submit_suche('opt[dauer]=gtis&val[dauer]=120')">alle Trainingseinheiten &uuml;ber zwei Stunden</option>
		</select>
	</span>

	<strong>Zeitraum:</strong>
		<span class="spacer">von</span>
		<input type="text" size="10" name="time-gt" value="<?php echo ($_POST['time-gt'] != '') ? $_POST['time-gt'] : date("d.m.Y",$config['time-start']) ?>" />
		bis
		<input type="text" size="10" name="time-lt" value="<?php echo ($_POST['time-lt'] != '') ? $_POST['time-lt'] : date("d.m.Y") ?>" />

	<strong style="padding-left:200px;">Sortierung:</strong>
		<span class="spacer">nach</span>
		<select name="order">
			<option value="time"<?php if ($_POST['order'] == "time") echo(' selected="selected"'); ?>>Datum</option>
			<option value="distanz"<?php if ($_POST['order'] == "distanz") echo(' selected="selected"'); ?>>Distanz</option>
			<option value="dauer"<?php if ($_POST['order'] == "dauer") echo(' selected="selected"'); ?>>Dauer</option>
			<option value="pace"<?php if ($_POST['order'] == "pace") echo(' selected="selected"'); ?>>Pace</option>
			<option value="hm"<?php if ($_POST['order'] == "hm") echo(' selected="selected"'); ?>>H&ouml;henmeter</option>
			<option value="puls"<?php if ($_POST['order'] == "puls") echo(' selected="selected"'); ?>>Puls</option>
			<option value="temperatur"<?php if ($_POST['order'] == "temperatur") echo(' selected="selected"'); ?>>Temperatur</option>
			<option value="vdot"<?php if ($_POST['order'] == "vdot") echo(' selected="selected"'); ?>>VDOT</option>
		</select>
		<select name="sort">
			<option value="ASC"<?php if ($_POST['sort'] == "ASC") echo(' selected="selected"'); ?>>aufsteigend</option>
			<option value="DESC"<?php if ($_POST['sort'] != "ASC") echo(' selected="selected"'); ?>>absteigend</option>
		</select>
			<br />

	<strong>Sportart:</strong>
<?php
$db = mysql_query('SELECT * FROM `ltb_sports` WHERE `online`=1 ORDER BY `id` ASC');
while ($sport = mysql_fetch_assoc($db)): ?>
		<input class="spacer" type="checkbox" name="sport[<?php echo($sport['id']); ?>]" <?php if (($_GET['submit'] != "true" && $sport['id'] == $global['hauptsport']) || $_POST['sport'][$sport['id']] != false) echo('checked="checked" '); ?>/>
			<?php echo($sport['name']); ?>
<?php endwhile; ?>
		<br />
<?php
$bedingungen = array();
$bedingungen[] = array('name' => 'schuhid', 'text' => 'Schuh', 'table' => 'ltb_schuhe', 'multiple' => false);
$bedingungen[] = array('name' => 'wetterid', 'text' => 'Wetter', 'table' => 'ltb_wetter');
$bedingungen[] = array('name' => 'kleidung', 'text' => 'Kleidung', 'table' => 'ltb_kleidung');
$bedingungen[] = array('name' => 'typid', 'text' => 'Trainingstyp', 'table' => 'ltb_typ');

foreach ($bedingungen as $bedingung):
?>
		<div class="right" style="margin-right:5px;">
			<strong><?php echo $bedingung['text']; ?></strong><br />
			<input type="hidden" name="opt[<?php echo $bedingung['name']; ?>]" value="is" />
			<select name="val[<?php echo $bedingung['name']; ?>]<?php echo ($bedingung['multiple'] !== false) ? '[]" multiple="multiple"' : '"'; ?> size="5">
				<option value="egal"<?php if ($_POST['val'][$bedingung['name']] == 'egal' || $_POST['val'][$bedingung['name']] == '') echo ' selected="selected"'; ?>>--- egal</option>
<?php
$db = mysql_query('SELECT `id`, `name` FROM `'.$bedingung['table'].'` ORDER BY `id` ASC');
while ($dat = mysql_fetch_assoc($db)):
	$selected = (in_array($dat['id'],explode(",",$_POST['val'][$bedingung['name']]))) ? ' selected="selected"' : '';
?>
				<option value="<?php echo $dat['id']; ?>"<?php echo $selected; ?>><?php echo $dat['name']; ?></option>
<?php endwhile; ?>
			</select>
		</div>
<?php
endforeach;

unset($bedingungen);
$bedingungen = array();
$bedingungen[] = array('name' => 'distanz', 'text' => 'Distanz <small>(km)</small>', 'typ' => 'int');
$bedingungen[] = array('name' => 'strecke', 'text' => 'Strecke', 'typ' => 'text');
$bedingungen[] = array('name' => 'hm', 'text' => 'H&ouml;henmeter', 'typ' => 'int');
$bedingungen[] = array('name' => 'dauer', 'text' => 'Dauer <small>(min)</small>', 'typ' => 'time');
$bedingungen[] = array('name' => 'bemerkung', 'text' => 'Bemerkung', 'typ' => 'text');
$bedingungen[] = array('name' => 'temperatur', 'text' => 'Temperatur <small>(&deg;C)</small>', 'typ' => 'int');
$bedingungen[] = array('name' => 'puls', 'text' => 'Puls <small>(bpm)</small>', 'typ' => 'int');
$bedingungen[] = array('name' => 'trainingspartner', 'text' => 'Trainingspartner', 'typ' => 'text');
$bedingungen[] = array('name' => 'kalorien', 'text' => 'Kalorien', 'typ' => 'int');
?>
<table class="left">
<?php
foreach ($bedingungen as $i => $bedingung):
	$value = '';
	if ($_POST['val'][$bedingung['name']] != '')
		$value = $_POST['val'][$bedingung['name']];

	if ($i%3 == 0):
?>
	<tr>
<?php
	endif;
?>
		<td><?php echo $bedingung['text']; ?></td>
		<td>
			<select name="opt[<?php echo $bedingung['name']; ?>]">
	<?php if ($bedingung['typ'] == 'int' || $bedingung['typ'] == "time"): ?>
				<option value="gt"<?php if ($_POST['opt'][$i] == "gt") echo(' selected="selected"'); ?>>&gt;</option>
				<option value="gtis"<?php if ($_POST['opt'][$i] == "gtis") echo(' selected="selected"'); ?>>&gt;=</option>
	<?php endif; ?>
				<option value="is"<?php if ($_POST['opt'][$i] == "is") echo(' selected="selected"'); ?>>=</option>
	<?php if ($bedingung['typ'] == 'int' || $bedingung['typ'] == "time"): ?>
				<option value="ltis"<?php if ($_POST['opt'][$i] == "ltis") echo(' selected="selected"'); ?>>&lt;=</option>
				<option value="lt"<?php if ($_POST['opt'][$i] == "lt") echo(' selected="selected"'); ?>>&lt;</option>
	<?php endif; ?>
	<?php if ($bedingung['typ'] == 'text'): ?>
				<option value="isnot"<?php if ($_POST['opt'][$i] == "isnot") echo(' selected="selected"'); ?>>!=</option>
				<option value="like"<?php if ($_POST['opt'][$i] == "like") echo(' selected="selected"'); ?>>~</option>
	<?php endif; ?>
			</select>
		</td>

		<td><input type="text" name="val[<?php echo $bedingung['name']; ?>]" value="<?php echo $value; ?>" size="<?php echo ($bedingung['typ'] != "text") ? 1 : 10; ?>" /></td>
<?php
	if (($i+1)%3 == 0 || ($i-1) == sizeof($bedingungen)):
?>
	</tr>
<?php
	endif;

endforeach;
?>
</table>

	<center style="clear:both;">
		<input type="submit" value="Suchen!" />
	</center>
</form>
<?php if ($submit): ?>
<?php
$set_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `position`!=0');
$num_dataset = mysql_num_rows($set_db);
$max_colspan = 1 + $num_dataset;

// MYSQL-QUERY
$i = 0;
$where = 'WHERE ';
if (sizeof($_POST['sport']) > 0) {
	$where .= '`sportid` IN(';
	foreach ($_POST['sport'] as $sportid => $value) {
		if ($value == 1) $where .= $sportid.',';
	}
	$where .= '99) AND ';
}

if (is_array($_POST['val']))
foreach ($_POST['val'] as $name => $value) {
	if ($value != '' && $value != "egal") {
		if ($name == "dauer")
			$value *= 60;
		switch ($_POST['opt'][$name]) {
			case 'is': $opt = '='; break;
			case 'gt': $opt = '>'; break;
			case 'gtis': $opt = '>='; break;
			case 'lt': $opt = '<'; break;
			case 'ltis': $opt = '<='; break;
			case 'isnot': $opt = '!='; break;
			case 'like': $opt = ' LIKE '; $value = '%'.$value.'%'; break;
			default: $opt = '=';
		}
		if ($name == "kleidung") {
			$kleidung = explode(",",$value);
			foreach ($kleidung as $stueck)
				$where .= ' FIND_IN_SET('.$stueck.',`kleidung`) AND ';
		}
		else
			$where .= '`'.$name.'`'.$opt.'"'.$value.'" AND ';
	}
}

if (isset($_POST['time-gt']) && isset($_POST['time-lt'])) {
	$time_gt_dat = explode('.',$_POST['time-gt']);
	$time_lt_dat = explode('.',$_POST['time-lt']);
	$time_gt = mktime(0,0,0,$time_gt_dat[1],$time_gt_dat[0],$time_gt_dat[2]);
	$time_lt = mktime(23,59,59,$time_lt_dat[1],$time_lt_dat[0],$time_lt_dat[2]);
	$where .= '`time` BETWEEN '.$time_gt.' AND '.$time_lt;
}
else $where = substr($where,0,-5);

if (!isset($_POST['seite'])) $_POST['seite'] = 1;
$limit = $_POST['seite']*15 - 15;
$db = mysql_query('SELECT * FROM `ltb_training` '.$where.' ORDER BY `'.$_POST['order'].'` '.$_POST['sort'].' LIMIT '.$limit.', 15');
$db_all = mysql_query('SELECT * FROM `ltb_training` '.$where.' ORDER BY `'.$_POST['order'].'` '.$_POST['sort']);
$num_all = mysql_num_rows($db_all);
?>

<table cellspacing="0" width="100%">
	<tr class="c">
		<td colspan="<?php echo($max_colspan); ?>">
<?php if ($num_all > 15): ?>
<?php
$submit_suche = '';

foreach ($_POST as $var => $val) {
	if (is_array($_POST[$var])) {
		foreach ($_POST[$var] as $var_2 => $val_2) {
			$submit_suche .= $var.'['.$var_2.']='.$val_2.'&';
		}
	}
	else
		$submit_suche .= $var.'='.$val.'&';
}
?>
<?php if ($num_all > $_POST['seite']*15): ?>
			<img class="link right" onclick="submit_suche('<?php echo($submit_suche); ?>&seite=<?php echo($_POST['seite']+1); ?>')" src="img/vor.png" />
<?php endif; ?>
<?php if ($_POST['seite'] > 1): ?>
			<img class="link left" onclick="submit_suche('<?php echo($submit_suche); ?>&seite=<?php echo($_POST['seite']-1); ?>')" src="img/zurueck.png" />
<?php endif; ?>
<?php endif; ?>
			Insgesamt wurden <?php echo($num_all); ?> Trainings gefunden.
		</td>
	</tr>
	<tr class="space">
		<td colspan="<?php echo($max_colspan); ?>">
		</td>
	</tr>
<?php
while($training = mysql_fetch_array($db)) {
	$i++;
	
	// Dataset
	$dataset = '';
	$set_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `position`!=0 AND `name`!="time" ORDER BY `position` ASC');
	while ($set = mysql_fetch_assoc($set_db))
		$dataset .= dataset($set['id'],true);
	echo mysql_error();

	echo('
	<tr class="a'.($i%2+1).$tr_style.' r">
		<td class="l"><small>'.date("d.m.Y", $training['time']).'</small></td>
		'.$dataset.'
	</tr>');
}
?>
</table>
<?php endif; ?>