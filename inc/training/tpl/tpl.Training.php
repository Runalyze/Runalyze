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

<style>
.training-row:after {
	content: '.';
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}

#training-overview:after {
	content: '';
}

.training-row {
	position: relative;
	margin-left: 300px;
	border-left: 1px solid #eee;
}

.training-row.fullwidth {
	margin-left: 0;
	border-left: 0;
}

.training-row-info {
	float: left;
	width: 300px;
	margin-left: -301px;
	max-height: 200px;
	overflow-y: scroll;
}

.training-row-info-shadow {
	position: absolute;
	left: -301px;
	bottom: 0;
	height: 25px;
	width: 300px;
	box-shadow: inset 0 -20px 15px -10px #fff;
}

.training-row-info .boxed-value-outer.w100 .boxed-value-container,
.training-row-info .boxed-value-outer.w25:nth-child(4n) .boxed-value-container,
.training-row-info .boxed-value-outer.w33:nth-child(3n) .boxed-value-container,
.training-row-info .boxed-value-outer.w50:nth-child(2n) .boxed-value-container {
	border-right: 0;
}

.training-row-info .boxed-values {
	margin-bottom: 0;
}

#statistics-inner .training-row-info table.fullwidth {
	margin: 0;
}

.training-row-info table.fullwidth.zebra-style tbody tr:first-child {
	border-top: 0;
}

.training-row-info .zebra-style tbody tr {
	border-color: #eee;
}

.training-row-plot .flot {
	background: transparent;
}

.training-row-plot p {
	clear: none;
}

.panel-heading.panel-inner-heading {
	background: #f6f6f6;
	border-bottom: 1px solid #eee;
}

.panel-heading h2 {
	font-weight: bold;
	text-transform: none;
	letter-spacing: 1px;
}

.panel-heading .change-menu {
	float: right;
}

.panel-heading .change-menu a {
	display: inline-block;
	padding-left: 5px;
	margin-left: 10px;
	border-left: 2px solid #ccc;
	line-height: 1.2em;
	color: #666;
}

.panel-heading .change-menu a:hover {
	color: #000;
}

.panel-heading .change-menu a.triggered {
	border-color: #666;
	color: #333;
}
</style>

<?php
$Sections = array();
$Sections[] = new SectionOverview($this->Training);
$Sections[] = new SectionLaps($this->Training);
$Sections[] = new SectionHeartrate($this->Training);
$Sections[] = new SectionPace($this->Training);
$Sections[] = new SectionRoute($this->Training);
$Sections[] = new SectionMiscellaneous($this->Training);

foreach ($Sections as &$Section)
	$Section->display();


echo Ajax::wrapJSforDocumentReady( 'RunalyzePlot.resizeTrainingCharts();' );

/*
?>

<div class="panel-heading panel-sub-heading">
	<h1>Altes Design</h1>
</div>

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
<!--
			<?php if ($this->Training->hasPositionData() && !CONF_TRAINING_MAP_BEFORE_PLOTS): ?>
			<div id="training-map">
				<?php $this->displayRoute(); ?>
			</div>
			<?php endif; ?>
-->
		</div>
		<?php endif; ?>


		<div id="training-table" class="databox left">
			<?php $this->displayTrainingTable(); ?>
		</div>

		<?php $this->displayTrainingData(); ?>
	</div>

	<br class="clear">
</div>
 */