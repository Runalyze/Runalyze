<div class="panel-heading">
	<?php if (!Request::isOnSharedPage()): ?>
	<div class="panel-menu">
		<ul>
			<?php foreach ($this->ToolbarLinks as $Link): ?><li><?php echo $Link ?></li><?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<h1><?php echo $this->Training->DataView()->getTitleWithComment(); ?></h1>

	<?php if (!Request::isOnSharedPage()): ?>
	<div class="hover-icons"><span class="link" onclick="Runalyze.reloadCurrentTab();"><?php echo Icon::$REFRESH_SMALL; ?></span></div>
	<?php endif; ?>
</div>

<!--
<?php
$Values = array(
	new BoxedValue($this->Training->getDistance(), 'km', 'Distanz'),
	new BoxedValue($this->Training->DataView()->getTimeString(), '', 'Dauer'),
	new BoxedValue($this->Training->DataView()->getElapsedTimeString(), '', 'Gesamtdauer'),
	new BoxedValue($this->Training->getPace(), '/km', 'Pace'),
	new BoxedValue($this->Training->getPulseAvg(), 'bpm', '&oslash; Puls'),
	new BoxedValue($this->Training->getPulseMax(), 'bpm', 'max. Puls'),
	new BoxedValue($this->Training->getCalories(), 'kcal', 'Kalorien'),
	new BoxedValue($this->Training->getCurrentlyUsedVdot(), '', 'VDOT', $this->Training->DataView()->getVDOTicon()),
	new BoxedValue($this->Training->getJDintensity(), '', 'TP'),
	new BoxedValue($this->Training->getTrimp(), '', 'TRIMP'),
	new BoxedValue($this->Training->getElevation(), 'm', 'H&ouml;henmeter')
);

$ValuesString = '';
foreach ($Values as &$Value)
	$ValuesString .= $Value->getCode();

BoxedValue::wrapValues($ValuesString);
?>

<div class="panel-heading panel-inner-heading">
	<h2>Zwischenzeiten</h2>
</div>
<div class="panel-content">
	<?php
		$Rounds = new RoundsSplits($this->Training);
		$Rounds->display();
	?>
</div>

<div class="panel-heading panel-inner-heading">
	<h2>Weitere Infos</h2>
</div>
-->

<div class="panel-content r" style="clear:both;">
	<?php foreach ($this->CheckableLabels as $Label): ?>
		<?php if ($Label['show']): ?>
			<label id="training-view-toggler-<?php echo $Label['key']; ?>" class="checkable margin-5" onclick="$(this).children('i').toggleClass('checked');Runalyze.toggleView('<?php echo $Label['key']; ?>');">
				<i class="fa fa-fw checkbox-icon checked"></i> <?php echo $Label['label']; ?>
			</label>
			<?php if (!$Label['visible']) echo Ajax::wrapJSasFunction('$("#training-view-toggler-'.$Label['key'].'").click();'); ?>
		<?php endif; ?>
	<?php endforeach; ?>
</div>

<div class="panel-content">
	<div id="training-display" class="clearfix">
		<?php if ($this->Training->hasPositionData() || !$this->PlotList->isEmpty()): ?>
		<div id="training-plots-and-map" class="databox">
			<div id="training-plots" class="toolbar-box-content">
				<div class="toolbar-line navigation-line">
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


		<div id="training-table" class="databox left">
			<?php $this->displayTrainingTable(); ?>
		</div>

		<?php $this->displayTrainingData(); ?>
	</div>

	<br class="clear">
</div>