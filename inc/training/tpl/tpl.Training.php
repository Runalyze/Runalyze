<h1>
	<small class="right"><?php $this->Training->displayDate(); ?></small>

	<?php $this->Training->displayTitle(); ?>

	<br class="clear" />
</h1>

<?php $PlotsList = new TrainingPlotsList($this->Training); ?>
<div class="toolbar toHeader open">
	<div class="toolbar-content toolbar-line">
		<span class="right" style="margin-top:3px;">
			<?php if ($this->Training->hasPaceData()): ?>
				<label id="training-view-toggler-zones" class="checkable" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('zones');"><i class="checkbox-icon checked"></i> Zonen</label>
			<?php endif; ?>
			<?php if ($this->Training->hasPaceData()): ?>
				<label id="training-view-toggler-rounds" class="checkable" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('rounds');"><i class="checkbox-icon checked"></i> Rundenzeiten</label>
			<?php endif; ?>
			<?php if (!$PlotsList->isEmpty() || $this->Training->hasPositionData()): ?>
				<label id="training-view-toggler-graphics" class="checkable" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('graphics');"><i class="checkbox-icon checked"></i> Karte &amp; Diagramme</label>
			<?php endif; ?>

			<?php if (!CONF_TRAINING_SHOW_ZONES) echo Ajax::wrapJSasFunction('$("#training-view-toggler-zones").click();'); ?>
			<?php if (!CONF_TRAINING_SHOW_ROUNDS) echo Ajax::wrapJSasFunction('$("#training-view-toggler-rounds").click();'); ?>
			<?php if (!CONF_TRAINING_SHOW_GRAPHICS) echo Ajax::wrapJSasFunction('$("#training-view-toggler-graphics").click();'); ?>
		</span>

		<?php if (!Request::isOnSharedPage()): ?>
			<?php echo Ajax::window('<a class="labeledLink" href="call/call.Training.edit.php?id='.$this->Training->id().'">'.Icon::$EDIT.' Bearbeiten</a> ','small'); ?>
			<?php echo Ajax::window('<a class="labeledLink" href="'.ExporterView::$URL.'?id='.$this->Training->id().'">'.Icon::$DOWNLOAD.' Exportieren</a> ','small'); ?>
		<?php endif; ?>
		<?php if ($this->Training->isPublic()): ?>
			<?php echo SharedLinker::getToolbarLinkTo($this->Training->id()); ?>
		<?php endif; ?>

		<br class="clear" />
	</div>
	<div class="toolbar-nav">
		<div class="toolbar-opener" style=""></div>
	</div>
</div>

<div id="training-display">
	<?php if ($this->Training->hasPositionData() || !$PlotsList->isEmpty()): ?>
	<div id="training-plots-and-map" class="dataBox">
		<div id="training-plots" class="toolbar-box-content">
			<div class="toolbar-line">
				<?php $PlotsList->displayLabels(); ?>
				<?php if ($this->Training->hasPositionData()): ?>
				<label id="training-view-toggler-map" class="checkable" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('map');"><i class="toggle-icon-map checked"></i> Karte</label>
				<?php endif; ?>

				<?php $PlotsList->displayJScode(); ?>
				<?php if (!CONF_TRAINING_SHOW_MAP) echo Ajax::wrapJSasFunction('$("#training-view-toggler-map").click();'); ?>
			</div>


			<?php if ($this->Training->hasPositionData() && CONF_TRAINING_MAP_BEFORE_PLOTS): ?>
			<div id="training-map" class="training-map-before plot-container">
				<?php $this->displayRoute(); ?>
			</div>
			<?php endif; ?>

			<?php $PlotsList->displayAllPlots(); ?>
		</div>

		<?php if ($this->Training->hasPositionData() && !CONF_TRAINING_MAP_BEFORE_PLOTS): ?>
		<div id="training-map">
			<?php $this->displayRoute(); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>


	<div id="training-table" class="dataBox left">
		<?php $this->Training->displayTable(); ?>
	</div>

	<?php $this->displayTrainingData(); ?>
</div>