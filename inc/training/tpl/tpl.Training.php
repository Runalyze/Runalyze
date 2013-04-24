<?php $this->displayTitle(); ?>

<div class="toolbar toHeader withoutNav open">
	<div class="toolbar-content toolbar-line">
		<span class="right" style="margin-top:3px;">
			<?php foreach ($this->CheckableLabels as $Label): ?>
				<?php if ($Label['show']): ?>
			<label id="training-view-toggler-<?php echo $Label['key']; ?>" class="checkable" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('<?php echo $Label['key']; ?>');">
				<i class="checkbox-icon checked"></i> <?php echo $Label['label']; ?>
			</label>
					<?php if (!$Label['visible']) echo Ajax::wrapJSasFunction('$("#training-view-toggler-'.$Label['key'].'").click();'); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</span>

		<?php foreach ($this->ToolbarLinks as $Link) echo $Link.NL; ?>

		<br class="clear" />
	</div>
</div>

<div id="training-display">
	<?php if ($this->Training->hasPositionData() || !$this->PlotList->isEmpty()): ?>
	<div id="training-plots-and-map" class="dataBox">
		<div id="training-plots" class="toolbar-box-content">
			<div class="toolbar-line">
				<?php $this->PlotList->displayLabels(); ?>
				<?php if ($this->Training->hasPositionData()): ?>
				<label id="training-view-toggler-map" class="checkable" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('map');"><i class="toggle-icon-map checked"></i> Karte</label>
				<?php endif; ?>

				<?php $this->PlotList->displayJScode(); ?>
				<?php if (!CONF_TRAINING_SHOW_MAP) echo Ajax::wrapJSasFunction('$("#training-view-toggler-map").click();'); ?>
			</div>


			<?php if ($this->Training->hasPositionData() && CONF_TRAINING_MAP_BEFORE_PLOTS): ?>
			<div id="training-map" class="training-map-before plot-container">
				<?php $this->displayRoute(); ?>
			</div>
			<?php endif; ?>

			<?php $this->PlotList->displayAllPlots(); ?>
		</div>

		<?php if ($this->Training->hasPositionData() && !CONF_TRAINING_MAP_BEFORE_PLOTS): ?>
		<div id="training-map">
			<?php $this->displayRoute(); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>


	<div id="training-table" class="dataBox left">
		<?php $this->displayTrainingTable(); ?>
	</div>

	<?php $this->displayTrainingData(); ?>
</div>

<br class="clear" />