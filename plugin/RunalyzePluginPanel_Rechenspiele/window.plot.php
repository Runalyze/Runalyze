<?php
/**
 * Window: plot for calculations
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = 'lasthalf';//date("Y");
//else
//	$_GET['y'] = (int)$_GET['y'];

$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y=all';
$Submenu = '<li'.('all' == $_GET['y'] ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.__('All years').'</a>').'</li>';
$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y=lasthalf';
$Submenu .= '<li'.('lasthalf' == $_GET['y'] ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.__('Last half year').'</a>').'</li>';
for ($j = date('Y'); $j >= START_YEAR; $j--)  {
	$link = 'plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y='.$j;
	$Submenu .= '<li'.($j == $_GET['y'] ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.$j.'</a>').'</li>';
}
?>
<div class="panel-heading">
	<div class="panel-menu">
		<ul>
			<li class="with-submenu"><span class="link"><?php _e('Choose year'); ?></span><ul class="submenu"><?php echo $Submenu; ?></ul></li>
		</ul>
	</div>
	<h1><?php _e('Shape'); ?></h1>
</div>

<div class="panel-content">
	<?php
	echo Plot::getDivFor('form'.$_GET['y'], 800, 450);
	include FRONTEND_PATH.'../plugin/RunalyzePluginPanel_Rechenspiele/Plot.Form.php';
	?>
</div>