<?php
/**
 * Window for weather plots
 * @package Runalyze\Plugins\Stats
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = date("Y");
else
	$_GET['y'] = (int)$_GET['y'];

$Submenu = '';
for ($j = date('Y'); $j >= START_YEAR; $j--)  {
	$link = 'plugin/RunalyzePluginStat_Wetter/window.php?y='.$j;
	$Submenu .= '<li'.($j == $_GET['y'] ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$j.'</a>').'</li>';
}
?>
<div class="panel-heading">
	<div class="panel-menu">
		<ul>
			<li class="with-submenu"><span class="link"><?php _e('Choose year'); ?></span><ul class="submenu"><?php echo $Submenu; ?></ul></li>
		</ul>
	</div>
	<h1><?php _e('Temperatures'); ?></h1>
</div>

<div class="panel-content">
	<?php
	echo Plot::getDivFor('average', 780, 240);
	include FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wetter/Plot.Average.php';
	?>
</div>
<div class="panel-content">
	<?php
	echo Plot::getDivFor('year'.$_GET['y'], 780, 240);
	include FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wetter/Plot.Year.php';
	?>
</div>