<table class="small">

<?php if ($this->hasDistance()): ?>
	<tr>
		<td class="inlineHead">Distanz:</td>
		<td><?php echo $this->getDistanceStringWithFullDecimals(); ?></td>
	</tr>
<?php endif; ?>

	<tr>
		<td class="inlineHead">Zeit:</td>
		<td><?php echo $this->getTimeString(); ?></td>
	</tr>

<?php if ($this->hasDistance()): ?>
	<tr>
		<td class="inlineHead">Tempo:</td>
		<td><?php echo $this->getPace(); ?>/km
			<small>(<?php echo $this->getKmh(); ?> km/h)</small></td>
	</tr>
<?php endif; ?>

<?php if ($this->hasPulse()): ?>
	<tr>
		<td class="inlineHead">
			&oslash; Puls:<!--<br />
			max.&nbsp;Puls:--></td>
		<td><?php echo Running::PulseStringInBpm($this->get('pulse_avg')); ?>
			 <small>(<?php echo Running::PulseStringInPercent($this->get('pulse_avg')); ?>)</small><!--<br />
			<?php echo Running::PulseStringInBpm($this->get('pulse_max')); ?>
			(<?php echo Running::PulseStringInPercent($this->get('pulse_max')); ?>)--></td>
	</tr>
	<tr>
		<td class="inlineHead">max.&nbsp;Puls:</td>
		<td><?php echo Running::PulseStringInBpm($this->get('pulse_max')); ?>
			<small>(<?php echo Running::PulseStringInPercent($this->get('pulse_max')); ?>)</small></td>
	</tr>
<?php endif; ?>

	<tr>
		<td class="inlineHead">Kalorien:</td>
		<td><?php echo Helper::Unknown($this->get('kcal')); ?> kcal</td>
	</tr>

<?php if (CONF_RECHENSPIELE): ?>
	<tr>
		<td class="inlineHead">Trimp:</td>
		<td><?php echo $this->getTrimpString(); ?></td>
	</tr>
	<?php if ($this->Sport()->isRunning() && $this->getVDOT() > 0): ?>
	<tr>
		<td class="inlineHead">Vdot:</td>
		<td><?php echo $this->getVDOT(); ?> <?php echo $this->getVDOTicon(); ?></td>
	</tr>
	<?php endif; ?>
<?php endif; ?>

<?php if (!$this->Weather()->isEmpty() || $this->hasRoute() || !$this->Clothes()->areEmpty()): ?>
	<tr><td colspan="5">&nbsp;</td></tr>
<?php endif; ?>

<?php if (!$this->Weather()->isEmpty()): ?>
	<tr>
		<td class="inlineHead">Wetter:</td>
		<td><?php echo $this->Weather()->fullString(); ?></td>
	</tr>
<?php endif; ?>

<?php if ($this->hasRoute() || $this->hasElevation()): ?>
	<tr>
		<td class="inlineHead">Strecke:</td>
		<td><?php echo Helper::Unknown($this->get('route')); ?>
				<?php $berechnet = $this->GpsData()->calculateElevation(); ?>
			<?php if ($this->hasElevation() > 0 || $berechnet > 0): ?><br />
				<?php echo $this->get('elevation'); ?> H&ouml;henmeter<br />
				<?php if ($berechnet != $this->get('elevation')): ?>
					<?php echo $berechnet ?> H&ouml;henmeter (berechnet)<br />
				<?php endif; ?>
				&oslash; <?php echo number_format($this->get('elevation')/10/$this->get('distance'), 2, ',', '.'); ?>&#37; Steigung
			<?php endif; ?>
	</tr>
<?php endif; ?>

<?php if ($this->get('shoeid') != 0): ?>
	<tr>
		<td class="inlineHead">Schuh:</td>
		<td>
			<?php if (Request::isOnSharedPage()): ?>
				<?php echo Shoe::getNameOf($this->get('shoeid')); ?>
			<?php else: ?>
				<?php echo Shoe::getSearchLink($this->get('shoeid')); ?>
			<?php endif; ?>
		</td>
	</tr>
<?php endif; ?>

<?php if (!$this->Clothes()->areEmpty()): ?>
	<tr>
		<td class="inlineHead">Kleidung:</td>
		<td>
			<?php if (Request::isOnSharedPage()): ?>
				<?php echo $this->Clothes()->asString(); ?>
			<?php else: ?>
				<?php echo $this->Clothes()->asLinks(); ?>
			<?php endif; ?>
		</td>
	</tr>
<?php endif; ?>

<?php if ($this->hasPartner()): ?>
	<tr>
		<td class="inlineHead">Partner:</td>
		<td>
			<?php if (Request::isOnSharedPage()): ?>
				<?php echo $this->getPartner(); ?>
			<?php else: ?>
				<?php echo $this->getPartnerAsLinks(); ?>
			<?php endif; ?>
		</td>
	</tr>
<?php endif; ?>

</table>