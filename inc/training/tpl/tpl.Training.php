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