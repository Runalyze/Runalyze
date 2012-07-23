<h1>
	<small class="right"><?php $this->Training->displayDate(); ?></small>

	<?php $this->Training->displayTitle(); ?>

	<br class="clear" />
</h1>


<div class="toolbar toHeader open">
	<div class="toolbar-content toolbar-line">
		<span class="right" style="margin-top:3px;">
			<?php if ($this->Training->hasPaceData()): ?>
				<label class="checkable" onclick="$(this).children('i').toggleClass('checked');$('.training-zones').toggle();"><i class="checkbox-icon checked"></i> Zonen</label>
			<?php endif; ?>
			<?php if ($this->Training->hasPaceData()): ?>
				<label class="checkable" onclick="$(this).children('i').toggleClass('checked');$('#training-rounds').toggle();"><i class="checkbox-icon checked"></i> Rundenzeiten</label>
			<?php endif; ?>
			<?php if (count($this->getPlotTypesAsArray()) > 0 || $this->Training->hasPositionData()): ?>
				<label class="checkable" onclick="$(this).children('i').toggleClass('checked');$('#training-plots-and-map').toggle();"><i class="checkbox-icon checked"></i> Karte &amp; Diagramme</label>
			<?php endif; ?>
		</span>

		<?php if (!Request::isOnSharedPage()): ?>
			<?php echo Ajax::window('<a class="labeledLink editLink" href="call/call.Training.edit.php?id='.$this->Training->id().'">Bearbeiten</a> ','small'); ?>
			<?php echo Ajax::window('<a class="labeledLink exportLink" href="'.ExporterView::$URL.'?id='.$this->Training->id().'">Exportieren</a> ','small'); ?>
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
	<div id="training-plots-and-map" class="dataBox"><!--"toolbar asBox open">-->
		<?php
		$Plots = $this->getPlotTypesAsArray();
		if (!empty($Plots)):
		?>
		<div id="training-plots" class="toolbar-box-content">
			<div class="toolbar-line">
				<?php foreach ($Plots as $i => $Plot): ?>
				<label class="checkable" onclick="RunalyzePlot.toggleTrainingChart('<?php echo $Plot['key']; ?>');"><i id="toggle-<?php echo $Plot['key']; ?>" class="toggle-icon-<?php echo $Plot['key']; ?> checked"></i> <?php echo $Plot['name']; ?></label>
				<?php endforeach; ?>

				<label class="checkable" onclick="$(this).children('i').toggleClass('checked');$('#training-map').toggle();"><i class="toggle-icon-map checked"></i> Karte</label>
			</div>

			<?php echo Ajax::wrapJSforDocumentReady('RunalyzePlot.initTrainingNavitation();'); ?>

			<?php
			foreach (array_keys($Plots) as $i => $Key) {
				echo '<div id="plot-'.$Key.'" class="plot-container">';
				$this->displayPlot($Key, false);
				echo '</div>'.NL;
			}
			?>
		</div>
		<?php endif; ?>

		<div id="training-map">
			<?php if ($this->Training->hasPositionData()): ?>
				<?php $this->displayRoute(); ?>
			<?php endif; ?>
		</div>
	</div>


	<div id="training-table" class="dataBox left">
		<?php $this->Training->displayTable(); ?>
	</div>

	<?php $this->displayTrainingData(); ?>
</div>