<div class="toolbar open">
	<div class="toolbar-content toolbar-line">
		<strong>
			<?php echo $this->Training->getTitle().': '.$this->Training->getDate(); ?>
		</strong>
		<span class="right">
			<?php if ($this->Training->isPublic()): ?>
				<?php echo SharedLinker::getToolbarLinkTo($this->Training->id()); ?>
			<?php endif; ?>
		</span>

		<br class="clear" />
	</div>
	<div class="toolbar-nav">
		<!--<div class="toolbar-opener" style=""></div>-->
	</div>
</div>



<div id="trainingTable" class="dataBox left">
	<?php $this->Training->displayIframeTable(); ?>
</div>



<div id="trainingChartsAndMap">
	<?php if ($this->Training->hasPositionData()): ?>
		<?php $this->displayRoute(); ?>
	<?php endif; ?>
</div>