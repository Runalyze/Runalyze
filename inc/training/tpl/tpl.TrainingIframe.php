<div class="toolbar without-nav open">
	<div class="toolbar-content toolbar-line">
		<strong>
			<?php echo $this->Training->DataView()->getTitleWithDate(); ?>
		</strong>
		<span class="right">
			<?php if ($this->Training->isPublic()): ?>
				<?php echo SharedLinker::getToolbarLinkTo($this->Training->id()); ?>
			<?php endif; ?>
		</span>

		<br class="clear">
	</div>
</div>



<div id="training-table" class="databox left">
	<?php $this->displayTrainingTable(); ?>

	<div id="training-charts-and-map">
		<?php if ($this->Training->hasPositionData()): ?>
			<?php $this->displayRoute(); ?>
		<?php endif; ?>
	</div>
</div>