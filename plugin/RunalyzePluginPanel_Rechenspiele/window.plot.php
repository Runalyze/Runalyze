<?php
/**
 * Window: plot for calculations
 * @package Runalyze\Plugins\Panels
 */


if (isset($_GET['y'])){
	$timerange=$_GET['y'];
} else {
	$timerange = 'lasthalf';
}

if (isset($_GET['m'])){
	$perfmodel=$_GET['m'];
} else {
	$perfmodel = 'tsb';
}

$TimeRangeLabels = [
	'all' => __('All years'),
	'lasthalf' => __('Last half year'),
	'lastyear' => __('Last year')
];

$CurrentYear = (isset($TimeRangeLabels[$timerange])) ? $TimeRangeLabels[$timerange] : $timerange;

$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y=all&m='.$perfmodel;
$Submenu = '<li'.('all' == $timerange ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$TimeRangeLabels['all'].'</a>').'</li>';

$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y=lasthalf&m='.$perfmodel;
$Submenu .= '<li'.('lasthalf' == $timerange ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$TimeRangeLabels['lasthalf'].'</a>').'</li>';

$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y=lastyear&m='.$perfmodel;
$Submenu .= '<li'.('lastyear' == $timerange ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$TimeRangeLabels['lastyear'].'</a>').'</li>';

for ($j = date('Y'); $j >= START_YEAR; $j--)  {
	$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y='.$j.'&m='.$perfmodel;
	$Submenu .= '<li'.($j == $timerange ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$j.'</a>').'</li>';
}

$ModelLabels = [
	'tsb' => __('TSB'),
	'banister' => 'Banister'
];

$CurrentModel = $ModelLabels[$perfmodel];

$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y='.$timerange.'&m=tsb';
$perfmodelsmenu = '<li'.('tsb' == $perfmodel ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$ModelLabels['tsb'].'</a>').'</li>';

$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y='.$timerange.'&m=banister';
$perfmodelsmenu .= '<li'.('banister' == $perfmodel ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$ModelLabels['banister'].'</a>').'</li>';


?>
<div class="panel-heading">
	<div class="panel-menu">
		<ul>
			<li class="with-submenu"><span class="link"><?php echo $CurrentModel; ?></span><ul class="submenu"><?php echo $perfmodelsmenu ?></ul></li>
			<li class="with-submenu"><span class="link"><?php echo $CurrentYear; ?></span><ul class="submenu"><?php echo $Submenu; ?></ul></li>
		</ul>
	</div>
	<h1><?php _e('Shape'); ?></h1>
</div>

<div class="panel-content">
	<?php
	echo Plot::getDivFor('form'.$timerange.$perfmodel, 800, 450);
	include FRONTEND_PATH.'../plugin/RunalyzePluginPanel_Rechenspiele/Plot.Form.php';
	?>
</div>
