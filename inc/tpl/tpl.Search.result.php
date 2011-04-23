<?php
$Dataset = new Dataset();
$Dataset->loadCompleteDataset();

$max_colspan = 1 + $Dataset->column_count;

// MYSQL-QUERY
$where = 'WHERE ';
if (sizeof($_POST['sport']) > 0) {
	$where .= '`sportid` IN(';
	foreach ($_POST['sport'] as $sportid => $value)
		if ($value == 'on')
			$where .= $sportid.',';
	$where .= '-1) AND ';
}


if (is_array($_POST['val'])) {
	foreach ($_POST['val'] as $name => $value) {
		if (is_numeric($name))
			$name = $conditions[$name]['name'];

		if (($value != '' || $_POST['opt'][$name] == 'isnot') && $value != 'egal' && $value[0] != 'egal') {
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
			if ($name == 'kleidung') {
				foreach ($value as $clothes_name)
					$where .= ' FIND_IN_SET('.$clothes_name.',`kleidung`) AND ';
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
$limit = $_POST['seite']*15 - 15;

$trainings = Mysql::getInstance()->fetch('SELECT * FROM `ltb_training` '.$where.' ORDER BY `'.$_POST['order'].'` '.$_POST['sort'].' LIMIT '.$limit.', 15', false, true);
$num_all   = Mysql::getInstance()->num('SELECT * FROM `ltb_training` '.$where.' ORDER BY `'.$_POST['order'].'` '.$_POST['sort'], false, true);
?>

<table cellspacing="0" width="100%">
	<tr class="c">
		<td colspan="<?php echo($max_colspan); ?>">
<?php
// TODO Use jQuery-Paginator
if ($num_all > 15) {
	$submit_search = '';

	foreach ($_POST as $var => $val) {
		if (is_array($val))
			foreach ($_POST[$var] as $inner_var => $inner_val) {
				if (is_array($inner_val))
					foreach ($inner_val as $i_var => $i_val)
						$submit_search .= $var.'['.$inner_var.']['.$i_var.']='.$i_val.'&';
				else
					$submit_search .= $var.'['.$inner_var.']='.$inner_val.'&';
			}
		elseif ($var != 'seite')
			$submit_search .= $var.'='.$val.'&';
	}

	if ($num_all > $_POST['seite']*15) {
		$name   = Icon::get(Icon::$ARR_NEXT, 'Seite vor');
		$data   = $submit_search.'seite='.($_POST['seite']+1);
		$next = Ajax::link($name, DATA_BROWSER_SEARCHRESULT_ID, 'inc/tpl/window.search.php?pager=true&get=true&'.$data);
	}

	if ($_POST['seite'] > 1) {
		$name   = Icon::get(Icon::$ARR_BACK, 'Seite zur&uuml;ck');
		$data   = $submit_search.'seite='.($_POST['seite']-1);
		$back = Ajax::link($name, DATA_BROWSER_SEARCHRESULT_ID, 'inc/tpl/window.search.php?pager=true&'.$data);
	}
} else {
	$next = '';
	$back = '';
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
	echo('
	<tr class="a'.($i%2+1).' r">
		<td class="l"><small>'.date("d.m.Y", $training['time']).'</small></td>');

	$Dataset->setTrainingId($training['id']);
	$Dataset->displayTableColumns();

	echo('</tr>');
}
?>
</table>