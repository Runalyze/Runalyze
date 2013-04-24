<div class="toolbar withoutNav open">
	<div class="toolbar-content toolbar-line">
		<strong>
			<?php echo $this->Training->DataView()->getTitleWithDate(); ?>
		</strong>
		<span class="right">
			<?php if ($this->Training->isPublic()): ?>
				<?php echo SharedLinker::getToolbarLinkTo($this->Training->id()); ?>
			<?php endif; ?>
		</span>

		<br class="clear" />
	</div>
</div>



<div id="trainingTable" class="dataBox left">
	<?php $this->displayTrainingTable(); ?>

	<div id="trainingChartsAndMap">
		<?php if ($this->Training->hasPositionData()): ?>
			<?php $this->displayRoute(); ?>
		<?php endif; ?>
	</div>
</div>