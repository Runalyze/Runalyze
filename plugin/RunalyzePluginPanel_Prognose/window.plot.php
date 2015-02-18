<?php
/**
 * Window: prognosis plot
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Activity\Distance;

require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Prognose');
$distances = $Plugin->getDistances();

if (!isset($_GET['distance'])) {
	$distance = (in_array(10, $distances)) ? 10 : trim($distances[0]);
} else {
	$distance = (float)$_GET['distance'];
}

$Submenu = '';
foreach ($distances as $km) {
	$km = trim($km);
	$link = 'plugin/RunalyzePluginPanel_Prognose/window.plot.php?distance='.$km;
	$Submenu .= '<li'.($km == $distance ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.Distance::format($km, Distance::FORMAT_AUTO).'</a>').'</li>';
}
?>
<div class="panel-heading">
	<div class="panel-menu">
		<ul>
			<li class="with-submenu"><span class="link"><?php _e('Choose distance'); ?></span><ul class="submenu"><?php echo $Submenu; ?></ul></li>
		</ul>
	</div>
	<h1><?php _e('Prognosis calculator: form trend'); ?></h1>
</div>

<div class="panel-content">
	<?php
	echo Plot::getDivFor('formverlauf_'.str_replace('.', '_', $distance), 800, 450);
	include FRONTEND_PATH.'../plugin/RunalyzePluginPanel_Prognose/Plot.Form.php';
	?>

	<p class="info">
		<?php _e('The average VDOT value per month is used.'); ?>
	</p>

	<p class="info">
		<?php _e('The basic endurance adjustment is <strong>not</strong> used for these calculations.'); ?>
	</p>
</div>