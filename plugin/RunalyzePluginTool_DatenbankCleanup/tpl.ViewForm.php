<?php
/**
 * This file contains the form template
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

/**
 * @var RunalyzePluginTool_DatenbankCleanup
 */
$this;

if (!($this instanceof RunalyzePluginTool_DatenbankCleanup)) {
	throw new RuntimeException('Template called in wrong context.');
}
?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="cleanup" class="ajax no-automatic-reload">

	<div class="padding-5">
		<label>
			<input type="radio" name="mode" value="general" checked>
			<strong><?php _e('General cleanup'); ?></strong>
		</label>

		<div class="radio-content" data-radio="general">
			<div class="margin-10 padding-5">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="internals">
						<?php _e('Refresh internal constants'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="shoes">
						<?php _e('Recalculate your equipment statistics'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="vdot-corrector">
						<?php _e('Recalculate your VDOT correction factor'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="vdot">
						<?php _e('Recalculate your VDOT shape'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="endurance">
						<?php _e('Recalculate your basic endurance'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="trimp">
						<?php _e('Recalculate maximal TRIMP values'); ?>
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="padding-5">
		<label>
			<input type="radio" name="mode" value="loop">
			<strong><?php _e('Loop through activities'); ?></strong>
		</label>

		<div class="radio-content transparent-70" data-radio="loop">
			<div class="margin-10">
				<p class="info">
					<?php _e('These recalculations are only needed if you changed the corresponding settings.'); ?><br>
					<?php _e('This task may take some time, since we have to loop through all your activities.'); ?>
				</p>
			</div>

			<div class="margin-10 padding-5">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="activity-elevation">
						<?php _e('Recalculate elevation'); ?>
					</label>
					<div class="checkbox">
						<label>
							<input type="checkbox" name="activity-elevation-overwrite">
							<?php _e('Overwrite manual values'); ?>
						</label>
					</div>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="activity-vdot">
						<?php _e('Recalculate VDOT values'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="activity-jdpoints">
						<?php _e('Recalculate JD training points'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="activity-trimp">
						<?php _e('Recalculate TRIMP values'); ?>
					</label>
				</div>
			</div>

			<div class="margin-10 padding-5" style="display:none;">
				<label class="transparent-70">
					<input type="checkbox" id="toggle-disabled" name="toggle-disabled" checked disabled>
					<?php _e('Allow to force recalculations if settings did not change'); ?>
				</label>
			</div>
		</div>
	</div>

	<input type="submit" name="submit" value="<?php _e('Start cleanup'); ?>">

</form>

<script>
$(function(){
	$("#cleanup input[type=radio]").change(function(){
		if ($(this).is(':checked')) {
			var val = $(this).attr('value');
			$("#cleanup .radio-content").each(function(){
				$(this).toggleClass('transparent-70', $(this).attr('data-radio') != val);
			});
		}
	});
});
</script>