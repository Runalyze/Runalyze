<?php
/**
 * Window: race result form
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Activity\Distance;

require '../../inc/class.Frontend.php';

$Frontend = new Frontend();
$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginStat_Wettkampf');
?>
<div class="panel-heading">
	<h1><?php _e('Race result details'); ?></h1>
</div>

<div class="panel-content">
<?php echo $Plugin->raceResultForm($_GET['rid']);
?>
</div>