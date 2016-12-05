<?php
/**
 * Window: race result form
 * @package Runalyze\Plugins\Panels
 */

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginStat_Wettkampf');
?>
<div class="panel-heading">
	<h1><?php _e('Competition details'); ?></h1>
</div>

<div class="panel-content">
<?php echo $Plugin->raceResultForm($_GET['rid']);
?>
</div>
