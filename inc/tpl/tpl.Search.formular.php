<form id="search" class="ajax" action="inc/tpl/window.search.php" method="post">
	<span class="right">
		<select name="lorem">
			<option value="ipsum">... vordefinierte Suchen</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[typid]=is&val[typid][0]=3','',null);">alle Intervalltrainings</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[typid]=is&val[typid][0]=4','',null);">alle Tempodauerl&auml;ufe</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[typid]=is&val[typid][0]=7','',null);">alle Langen L&auml;ufe</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[bahn]=is&val[bahn]=1','',null);">alle Bahnl&auml;ufe</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[trainingspartner]=isnot&val[trainingspartner]=','',null);">alle Trainings mit Trainingspartner</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[temperatur]=lt&val[temperatur]=0','',null);">alle Trainings bei Minusgraden</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','inc/tpl/window.search.php?get=true&opt[dauer]=gtis&val[dauer]=120','',null);">alle Trainingseinheiten &uuml;ber zwei Stunden</option>
		</select>
	</span>

	<strong>Zeitraum:</strong>
		<span class="spacer">von</span>
		<input type="text" size="10" name="time-gt" value="<?php echo (isset($_POST['time-gt']) && $_POST['time-gt'] != '') ? $_POST['time-gt'] : date("d.m.Y", START_TIME) ?>" />
		bis
		<input type="text" size="10" name="time-lt" value="<?php echo (isset($_POST['time-lt']) && $_POST['time-lt'] != '') ? $_POST['time-lt'] : date("d.m.Y") ?>" />

	<strong style="padding-left:200px;">Sortierung:</strong>
		<span class="spacer">nach</span>
		<select name="order">
			<option value="time"<?php       echo Helper::Selected($_POST['order'] == 'time'); ?>>Datum</option>
			<option value="distanz"<?php    echo Helper::Selected($_POST['order'] == 'distanz'); ?>>Distanz</option>
			<option value="dauer"<?php      echo Helper::Selected($_POST['order'] == 'dauer'); ?>>Dauer</option>
			<option value="pace"<?php       echo Helper::Selected($_POST['order'] == 'pace'); ?>>Pace</option>
			<option value="hm"<?php         echo Helper::Selected($_POST['order'] == 'hm'); ?>>H&ouml;henmeter</option>
			<option value="puls"<?php       echo Helper::Selected($_POST['order'] == 'puls'); ?>>Puls</option>
			<option value="temperatur"<?php echo Helper::Selected($_POST['order'] == 'temperatur'); ?>>Temperatur</option>
			<option value="vdot"<?php       echo Helper::Selected($_POST['order'] == 'vdot'); ?>>VDOT</option>
		</select>
		<select name="sort">
			<option value="ASC"<?php  echo Helper::Selected($_POST['sort'] == 'ASC'); ?>>aufsteigend</option>
			<option value="DESC"<?php echo Helper::Selected($_POST['sort'] != 'ASC'); ?>>absteigend</option>
		</select>
			<br />

	<strong>Sportart:</strong>
<?php
$sports = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'sports` WHERE `online`=1 ORDER BY `id` ASC');
foreach ($sports as $sport) {
	$checked = Helper::Checked((!$submit && $sport['id'] == MAINSPORT) || (isset($_POST['sport'][$sport['id']]) && $_POST['sport'][$sport['id']] != false));
	echo('
		<input class="spacer" type="checkbox" name="sport['.$sport['id'].']"'.$checked.' /> '.$sport['name']);
}

echo('<br />');

$conditions = array();
$conditions[] = array('name' => 'schuhid', 'text' => 'Schuh', 'table' => ''.PREFIX.'schuhe', 'multiple' => false);
$conditions[] = array('name' => 'wetterid', 'text' => 'Wetter', 'table' => ''.PREFIX.'wetter', 'multiple' => true);
$conditions[] = array('name' => 'kleidung', 'text' => 'Kleidung', 'table' => ''.PREFIX.'kleidung', 'multiple' => true);
$conditions[] = array('name' => 'typid', 'text' => 'Trainingstyp', 'table' => ''.PREFIX.'typ', 'multiple' => true);

foreach ($conditions as $condition) {
	$multiple      = ($condition['multiple'] !== false) ? ' multiple="multiple"' : '';
	$selected_egal = Helper::Selected(!isset($_POST['val']) || !isset($_POST['val'][$condition['name']]) || $_POST['val'][$condition['name']][0] == 'egal' || $_POST['val'][$condition['name']] == '');

	echo('
		<div class="right">
			<strong>'.$condition['text'].'</strong><br />
			<input type="hidden" name="opt['.$condition['name'].']" value="is" />
			<select name="val['.$condition['name'].'][]"'.$multiple.' size="5">
				<option value="egal"'.$selected_egal.'>--- egal</option>');

	$options = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.$condition['table'].'` ORDER BY `id` ASC');
	foreach ($options as $option) {
		$selected        = Helper::Selected(isset($_POST['val']) && isset($_POST['val'][$condition['name']]) && in_array($option['id'], $_POST['val'][$condition['name']]));
		echo('
		<option value="'.$option['id'].'"'.$selected.'>'.$option['name'].'</option>');
	}

	echo('
			</select>
		</div>');
}
?>

<table class="left">
<?php
$inputs = array();
$inputs[] = array('name' => 'distanz', 'text' => 'Distanz <small>(km)</small>', 'typ' => 'int');
$inputs[] = array('name' => 'strecke', 'text' => 'Strecke', 'typ' => 'text');
$inputs[] = array('name' => 'hm', 'text' => 'H&ouml;henmeter', 'typ' => 'int');
$inputs[] = array('name' => 'dauer', 'text' => 'Dauer <small>(min)</small>', 'typ' => 'time');
$inputs[] = array('name' => 'bemerkung', 'text' => 'Bemerkung', 'typ' => 'text');
$inputs[] = array('name' => 'temperatur', 'text' => 'Temperatur <small>(&deg;C)</small>', 'typ' => 'int');
$inputs[] = array('name' => 'puls', 'text' => 'Puls <small>(bpm)</small>', 'typ' => 'int');
$inputs[] = array('name' => 'trainingspartner', 'text' => 'Trainingspartner', 'typ' => 'text');
$inputs[] = array('name' => 'kalorien', 'text' => 'Kalorien', 'typ' => 'int');

foreach ($inputs as $i => $input) {
	if (!isset($_POST['val']) || !isset($_POST['val'][$input['name']]))
		$value = '';
	else
		$value = $_POST['val'][$input['name']];
	if (!isset($_POST['opt']) || !isset($_POST['opt'][$input['name']]))
		$opt = 'is';
	else
		$opt = $_POST['opt'][$input['name']];

	if ($i%3 == 0)
		echo('<tr>');

	echo('
		<td>'.$input['text'].'</td>
		<td>
			<select name="opt['.$input['name'].']">
				<option value="is"'.Helper::Selected($opt == 'is').'>=</option>');

	if ($input['typ'] == 'int' || $input['typ'] == 'time')
		echo('
				<option value="gt"'.Helper::Selected($opt == 'gt').'>&gt;</option>
				<option value="gtis"'.Helper::Selected($opt == 'gtis').'>&gt;=</option>');

	if ($input['typ'] == 'int' || $input['typ'] == 'time')
		echo('
				<option value="ltis"'.Helper::Selected($opt == 'ltis').'>&lt;=</option>
				<option value="lt"'.Helper::Selected($opt == 'lt').'>&lt;</option>');

	if ($input['typ'] == 'text')
		echo('
				<option value="isnot"'.Helper::Selected($opt == 'isnot').'>!=</option>
				<option value="like"'.Helper::Selected($opt == 'like').'>~</option>');

	echo('
			</select>
		</td>
		<td><input type="text" name="val['.$input['name'].']" value="'.Helper::Umlaute($value).'" size="'.($input['typ'] != 'text' ? 1 : 10).'" /></td>');

	if (($i+1)%3 == 0 || ($i-1) == sizeof($inputs))
		echo('</tr>');
}
?>
</table>


	<div class="c" style="clear:both;">
		<input style="margin-top: 10px;" type="submit" value="Suchen!" />
	</div>
</form>