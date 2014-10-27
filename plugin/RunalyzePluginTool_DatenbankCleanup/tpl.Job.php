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

<div id="cleanup-job-result" class="padding-5">
	<p class="text-headline">
		<?php _e('Cleanup is running ...'); ?>
	</p>

	<div class="padding-5">
		<?php
		$ProgressBar = new ProgressBar(1, ProgressBarSingle::$COLOR_GREEN);
		$ProgressBar->setInline();
		$ProgressBar->setAnimated();
		$ProgressBar->display();
		?>
	</div>

	<div class="c">
		100 &#37;
	</div>

	<p class="text"></p>

	<?php
	$Job = $this->Job();
	foreach ($this->Job()->messages() as $message) {
		echo HTML::okay($message);
	}
	?>

	<p class="text"></p>

	<p class="text">
		<?php echo $this->getActionLink('&laquo; back to form'); ?>
	</p>
</div>

<script>
$(function(){
	$("#cleanup-job-result .progress-bar").width('100%');
});
</script>