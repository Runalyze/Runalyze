<?php
// TODO: CleanCode
// -> Mysql-Query to class
$Dataset = new Dataset();
$Dataset->loadCompleteDataset();

$max_colspan = 1 + $Dataset->column_count;

// MYSQL-QUERY
$where = 'WHERE ';
if (isset($_POST['sport']) && sizeof($_POST['sport']) > 0) {
	$where .= '`sportid` IN(';
	foreach ($_POST['sport'] as $sportid => $value)
		if ($value == 'on')
			$where .= $sportid.',';
	$where .= '-1) AND ';
}


if (isset($_POST['val']) && is_array($_POST['val'])) {
	foreach ($_POST['val'] as $name => $value) {
		$value = str_replace('+', ' ', $value);

		if (is_numeric($name))
			$name = $conditions[$name]['name'];

		if (($value != '' || $_POST['opt'][$name] == 'isnot') && $value != 'egal' && (isset($value[0]) && $value[0] != 'egal')) {
			if ($name == 'dauer')
				$value *= 60;
			elseif (!is_array($value))
				$value = Helper::Umlaute($value);
			switch ($_POST['opt'][$name]) {
				case 'is':    $opt = '=';  break;
				case 'gt':    $opt = '>';  break;
				case 'gtis':  $opt = '>='; break;
				case 'lt':    $opt = '<';  break;
				case 'ltis':  $opt = '<='; break;
				case 'isnot': $opt = '!='; break;
				case 'like':  $opt = ' LIKE '; $value = '%'.$value.'%'; break;
				default:      $opt = '=';
			}
			if ($name == 'clothes') {
				foreach ($value as $clothes_name)
					$where .= ' FIND_IN_SET('.$clothes_name.',`clothes`) AND ';
			} elseif (is_array($value)) {
				$where .= '`'.$name.'` IN('.implode(',', $value).', -1) AND ';
			} else
				$where .= '`'.$name.'`'.$opt.'"'.$value.'" AND ';
		}
	}
}

if (isset($_POST['time-gt']) && isset($_POST['time-lt'])) {
	$time_gt_dat = explode('.', $_POST['time-gt']);
	$time_lt_dat = explode('.', $_POST['time-lt']);
	$time_gt = mktime(0,  0,  0,  $time_gt_dat[1], $time_gt_dat[0], $time_gt_dat[2]);
	$time_lt = mktime(23, 59, 59, $time_lt_dat[1], $time_lt_dat[0], $time_lt_dat[2]);
	$where .= '`time` BETWEEN '.$time_gt.' AND '.$time_lt;
} else
	$where = substr($where, 0, -5);
if (!isset($_POST['seite']))
	$_POST['seite'] = 1;
$limit = $_POST['seite']*CONF_RESULTS_AT_PAGE - CONF_RESULTS_AT_PAGE;

$trainings = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'training` '.$where.' ORDER BY `'.$_POST['order'].'` '.$_POST['sort'].' LIMIT '.$limit.', '.CONF_RESULTS_AT_PAGE);
$num_all   = Mysql::getInstance()->num('SELECT * FROM `'.PREFIX.'training` '.$where.' ORDER BY `'.$_POST['order'].'` '.$_POST['sort']);

if (isset($_POST['send_to_multiEditor'])) {
	$IDs = array();
	foreach ($trainings as $training)
		$IDs[] = $training['id'];

	$_GET['ids'] = implode(',', $IDs);
	$MultiEditor = Plugin::getInstanceFor('RunalyzePluginTool_MultiEditor');
	$MultiEditor->display();
	echo '</div>';
	exit();
}

?>

<table style="width=100%;">
	<tr class="c">
		<td colspan="<?php echo($max_colspan); ?>">
<?php
$next = '';
$back = '';

if ($num_all > CONF_RESULTS_AT_PAGE) {
	$submit_search = '';

	foreach ($_POST as $var => $val) {
		if (is_array($val))
			foreach ($_POST[$var] as $inner_var => $inner_val) {
				if (is_array($inner_val))
					foreach ($inner_val as $i_var => $i_val)
						$submit_search .= $var.'['.$inner_var.']['.$i_var.']='.str_replace(' ', '+', $i_val).'&';
				else
					$submit_search .= $var.'['.$inner_var.']='.str_replace(' ', '+', $inner_val).'&';
			}
		elseif ($var != 'seite')
			$submit_search .= $var.'='.str_replace(' ', '+', $val).'&';
	}

	if ($num_all > $_POST['seite']*CONF_RESULTS_AT_PAGE) {
		$name   = Icon::get(Icon::$ARR_NEXT, 'Seite vor');
		$data   = $submit_search.'seite='.($_POST['seite']+1);
		$next = Ajax::link($name, DATA_BROWSER_SEARCHRESULT_ID, 'call/window.search.php?pager=true&get=true&'.$data);
	}

	if ($_POST['seite'] > 1) {
		$name   = Icon::get(Icon::$ARR_BACK, 'Seite zur&uuml;ck');
		$data   = $submit_search.'seite='.($_POST['seite']-1);
		$back = Ajax::link($name, DATA_BROWSER_SEARCHRESULT_ID, 'call/window.search.php?pager=true&'.$data);
	}
}

	echo($back.' Insgesamt wurden '.$num_all.' Trainings gefunden. '.$next);

?>
		</td>
	</tr>
	<tr class="space">
		<td colspan="<?php echo($max_colspan); ?>">
		</td>
	</tr>
<?php
foreach ($trainings as $i => $training) {
	$date = date("d.m.Y", $training['time']);
	$link = Ajax::trainingLink($training['id'], $date, true);
	echo('
	<tr class="a'.($i%2+1).' r">
		<td class="l"><small>'.$link.'</small></td>');

	$Dataset->setTrainingId($training['id']);
	$Dataset->displayTableColumns();

	echo('</tr>');
}
?>
</table>