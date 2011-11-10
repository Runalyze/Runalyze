<form id="search" class="ajax" action="call/window.search.php" method="post">
	<span class="right">
		<select name="lorem">
			<option value="ipsum">... vordefinierte Suchen</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[typeid]=is&val[typeid][0]=3','',null);">alle Intervalltrainings</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[typeid]=is&val[typeid][0]=4','',null);">alle Tempodauerl&auml;ufe</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[typeid]=is&val[typeid][0]=7','',null);">alle Langen L&auml;ufe</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[is_track]=is&val[is_track]=1','',null);">alle Bahnl&auml;ufe</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[partner]=isnot&val[partner]=','',null);">alle Trainings mit Trainingspartner</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[temperature]=lt&val[temperature]=0','',null);">alle Trainings bei Minusgraden</option>
			<option onclick="jLoadLink('<?php echo DATA_BROWSER_SEARCH_ID; ?>','call/window.search.php?get=true&opt[s]=gtis&val[s]=120','',null);">alle Trainingseinheiten &uuml;ber zwei Stunden</option>
		</select>
	</span>

	<strong>Zeitraum:</strong>
		<label>
			<span class="spacer">von</span>
			<input type="text" size="10" name="time-gt" value="<?php echo (isset($_POST['time-gt']) && $_POST['time-gt'] != '') ? $_POST['time-gt'] : date("d.m.Y", START_TIME) ?>" />
		</label>
		<label>
			bis
			<input type="text" size="10" name="time-lt" value="<?php echo (isset($_POST['time-lt']) && $_POST['time-lt'] != '') ? $_POST['time-lt'] : date("d.m.Y") ?>" />
		</label>

	<strong style="padding-left:200px;">Sortierung:</strong>
		<label>
			<span class="spacer">nach</span>
			<select name="order">
				<option value="time"<?php        echo HTML::Selected($_POST['order'] == 'time'); ?>>Datum</option>
				<option value="distance"<?php    echo HTML::Selected($_POST['order'] == 'distance'); ?>>Distanz</option>
				<option value="s"<?php           echo HTML::Selected($_POST['order'] == 's'); ?>>Dauer</option>
				<option value="pace"<?php        echo HTML::Selected($_POST['order'] == 'pace'); ?>>Pace</option>
				<option value="elevation"<?php   echo HTML::Selected($_POST['order'] == 'elevation'); ?>>H&ouml;henmeter</option>
				<option value="pulse"<?php       echo HTML::Selected($_POST['order'] == 'pulse'); ?>>Puls</option>
				<option value="temperature"<?php echo HTML::Selected($_POST['order'] == 'temperature'); ?>>Temperatur</option>
				<option value="vdot"<?php        echo HTML::Selected($_POST['order'] == 'vdot'); ?>>VDOT</option>
			</select>
		</label>
		<select name="sort">
			<option value="ASC"<?php  echo HTML::Selected($_POST['sort'] == 'ASC'); ?>>aufsteigend</option>
			<option value="DESC"<?php echo HTML::Selected($_POST['sort'] != 'ASC'); ?>>absteigend</option>
		</select>
			<br />

	<strong>Sportart:</strong>
<?php
$sports = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'sport` WHERE `online`=1 ORDER BY `id` ASC');
foreach ($sports as $sport) {
	$checked = HTML::Checked((!$submit && $sport['id'] == CONF_MAINSPORT) || (isset($_POST['sport'][$sport['id']]) && $_POST['sport'][$sport['id']] != false));
	echo('
		<label><input class="spacer" type="checkbox" name="sport['.$sport['id'].']"'.$checked.' /> '.$sport['name'].'</label>');
}

echo('<br />');

$conditions = array();
$conditions[] = array('name' => 'shoeid', 'text' => 'Schuh', 'table' => ''.PREFIX.'shoe', 'multiple' => false);
$conditions[] = array('name' => 'weatherid', 'text' => 'Wetter', 'table' => ''.PREFIX.'weather', 'multiple' => true);
$conditions[] = array('name' => 'clothes', 'text' => 'Kleidung', 'table' => ''.PREFIX.'clothes', 'multiple' => true);
$conditions[] = array('name' => 'typeid', 'text' => 'Trainingstyp', 'table' => ''.PREFIX.'type', 'multiple' => true);

foreach ($conditions as $condition) {
	$multiple      = ($condition['multiple'] !== false) ? ' multiple="multiple"' : '';
	$selected_egal = HTML::Selected(!isset($_POST['val']) || !isset($_POST['val'][$condition['name']]) || $_POST['val'][$condition['name']][0] == 'egal' || $_POST['val'][$condition['name']] == '');

	echo('
		<div class="right">
			<label for="select_'.$condition['name'].'"><strong>'.$condition['text'].'</strong></label><br />
			<input type="hidden" name="opt['.$condition['name'].']" value="is" />
			<select name="val['.$condition['name'].'][]"'.$multiple.' size="5" id="select_'.$condition['name'].'">
				<option value="egal"'.$selected_egal.'>--- egal</option>');

	$options = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.$condition['table'].'` ORDER BY `id` ASC');
	foreach ($options as $option) {
		$selected        = HTML::Selected(isset($_POST['val']) && isset($_POST['val'][$condition['name']]) && in_array($option['id'], $_POST['val'][$condition['name']]));
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
$inputs[] = array('name' => 'distance', 'text' => 'Distanz <small>(km)</small>', 'typ' => 'int');
$inputs[] = array('name' => 'route', 'text' => 'Strecke', 'typ' => 'text');
$inputs[] = array('name' => 'elevation', 'text' => 'H&ouml;henmeter', 'typ' => 'int');
$inputs[] = array('name' => 's', 'text' => 'Dauer <small>(min)</small>', 'typ' => 'time');
$inputs[] = array('name' => 'comment', 'text' => 'Bemerkung', 'typ' => 'text');
$inputs[] = array('name' => 'temperature', 'text' => 'Temperatur <small>(&deg;C)</small>', 'typ' => 'int');
$inputs[] = array('name' => 'pules', 'text' => 'Puls <small>(bpm)</small>', 'typ' => 'int');
$inputs[] = array('name' => 'partner', 'text' => 'Trainingspartner', 'typ' => 'text');
$inputs[] = array('name' => 'kcal', 'text' => 'Kalorien', 'typ' => 'int');

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
		<td><label for="input_var_'.$input['name'].'">'.$input['text'].'</label></td>
		<td>
			<select name="opt['.$input['name'].']">
				<option value="is"'.HTML::Selected($opt == 'is').'>=</option>');

	if ($input['typ'] == 'int' || $input['typ'] == 'time')
		echo('
				<option value="gt"'.HTML::Selected($opt == 'gt').'>&gt;</option>
				<option value="gtis"'.HTML::Selected($opt == 'gtis').'>&gt;=</option>');

	if ($input['typ'] == 'int' || $input['typ'] == 'time')
		echo('
				<option value="ltis"'.HTML::Selected($opt == 'ltis').'>&lt;=</option>
				<option value="lt"'.HTML::Selected($opt == 'lt').'>&lt;</option>');

	if ($input['typ'] == 'text')
		echo('
				<option value="isnot"'.HTML::Selected($opt == 'isnot').'>!=</option>
				<option value="like"'.HTML::Selected($opt == 'like').'>~</option>');

	echo('
			</select>
		</td>
		<td><input type="text" name="val['.$input['name'].']" id="input_var_'.$input['name'].'" value="'.Helper::Umlaute($value).'" size="'.($input['typ'] != 'text' ? 1 : 10).'" /></td>');

	if (($i+1)%3 == 0 || ($i-1) == sizeof($inputs))
		echo('</tr>');
}
?>
</table>


	<div class="c" style="clear:both;">
<?php
$Editor = Mysql::getInstance()->fetchSingle('SELECT id FROM '.PREFIX.'plugin WHERE `key`="RunalyzePluginTool_MultiEditor"');
if (isset($Editor['id'])):
?>
		<label class="small"><input type="checkbox" name="send_to_multiEditor" /> an den MultiEditor senden</label>
			<br />
<?php endif; ?>
		<input style="margin-top: 10px;" type="submit" value="Suchen!" />
	</div>
</form>