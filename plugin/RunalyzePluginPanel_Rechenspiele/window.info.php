<?php
/**
 * Window: plot for calculations
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

new Frontend();
?>
<div class="panel-heading">
	<h1><?php _e('VDOT'); ?></h1>
</div>
<div class="panel-content">
	<p class="text">
		<?php _e('VDOT is the shortform of <em>V-dot-O2Max</em>, the maximum rate of oxygen flow.'); ?>
		<?php _e('This value is a good measurement for the possible performance of a runner.'); ?>
		<?php _e('Due to some additional factors, one talks about a <em>pseudo V-dot-O2Max</em>, which is equal for all runners at the same level.'); ?>
	</p>

	<p class="file">
		see <em><a href="http://runningtimes.com/Article.aspx?ArticleID=7482" title="Finding Your VDOT | Running Times">Treshold Training: Finding Your VDOT</a> by Jack Daniels, Ph.D.</em>
	</p>
</div>

<div class="panel-heading panel-sub-heading"><h1><?php _e('Your Shape: an estimation of your VDOT value'); ?></h1>
</div>
<div class="panel-content">
	<p class="text">
		<?php _e('Jack Daniels\' famous Running formula serves some tables estimating the VDOT value. '.
				'These formulas are used to estimate your VDOT and predict your race performances.'); ?>
	</p>

	<p class="text">
		<?php _e('Your personal VDOT value is estimated based on the ratio of heart rate and pace in every of your runs.'); ?>
		<?php _e('The average over a given period is considered as your current shape.') ?>
	</p>

	<p class="text">
		<?php _e('Each VDOT value of an activity is marked with an arrow, '.
				'to show if this value is (much) higher than your current shape, '.
				'equal to it or (much) lower:'); ?><br>
		<i class="vdot-icon vdot-up-2"></i>
		<i class="vdot-icon vdot-up"></i>
		<i class="vdot-icon vdot-normal"></i>
		<i class="vdot-icon vdot-down"></i>
		<i class="vdot-icon vdot-down-2"></i>
	</p>

	<p class="warning">
		<?php _e('Warning: Of course, pauses, walking and other factors can heavily influence the ratio of heart rate and pace.'); ?>
		<?php _e('Activities with known influences or strange results can be ignored for the calculation of your shape.'); ?>
	</p>
</div>

<div class="panel-heading panel-sub-heading">
	<h1><?php _e('TRIMP/ATL/CTL/TSB'); ?></h1>
</div>
<div class="panel-content">
	<p class="text">
		<?php _e('The TRIMP-concept gives a possibility to describe the load of a single activity.'); ?>
		<?php _e('For a single activity the TRIMP is calculated based on the duration and your heart rate.'); ?>
	</p>

	<p class="text">
		<?php _e('Your <strong>Actual Training Load</strong>, ATL, also fatigue, is based on the sum of your last TRIMP values in a short period, e.g. for seven days.'); ?>
		<?php _e('In contrast, your <strong>Chronical Training Load</strong>, CTL, also fitness level, is based on the sum of your last TRIMP values in a long period, e.g. for fourty days.'); ?>
		<?php _e('The difference of these values is called <strong>Training Stress Balance</strong>, TSB. It describes if you are recovering (positive) or training hard (negative).'); ?>
	</p>

	<p class="file">
		see <em><a href="http://www.netzathleten.de/fitness/richtig-trainieren/item/1481-trainingstagebuch-sinnvoll-oder-nicht" title="Das TRIMP-Konzept">Das TRIMP-Konzept</a> (german, netzathleten.de)</em>
	</p>
</div>