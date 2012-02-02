<table>

<?php if ($this->hasDistance()): ?>
	<tr>
		<td class="small b">Distanz:</td>
		<td><?php echo $this->getDistanceStringWithFullDecimals(); ?></td>
	</tr>
<?php endif; ?>

	<tr>
		<td class="small b">Zeit:</td>
		<td><?php echo $this->getTimeString(); ?></td>
	</tr>

<?php if ($this->hasDistance()): ?>
	<tr>
		<td class="small b">Tempo:</td>
		<td><?php echo $this->getPace(); ?>/km<br />
			<?php echo $this->getKmh(); ?> km/h</td>
	</tr>
<?php endif; ?>

<?php if ($this->hasPulse()): ?>
	<tr>
		<td class="small b">
			&oslash; Puls:<br />
			max.&nbsp;Puls:</td>
		<td><?php echo Helper::PulseStringInBpm($this->get('pulse_avg')); ?>
			 (<?php echo Helper::PulseStringInPercent($this->get('pulse_avg')); ?>)<br />
			<?php echo Helper::PulseStringInBpm($this->get('pulse_max')); ?>
			(<?php echo Helper::PulseStringInPercent($this->get('pulse_max')); ?>)</td>
	</tr>
<?php endif; ?>

	<tr>
		<td class="small b">Kalorien:</td>
		<td><?php echo Helper::Unknown($this->get('kcal')); ?> kcal</td>
	</tr>

<?php if (CONF_RECHENSPIELE): ?>
	<tr>
		<td class="small b">Trimp:</td>
		<td><?php echo $this->getTrimpString(); ?></td>
	</tr>
	<?php if ($this->Sport()->isRunning() && $this->getVDOT() > 0): ?>
	<tr>
		<td class="small b">Vdot:</td>
		<td><?php echo $this->getVDOT(); ?> <?php echo $this->getVDOTicon(); ?></td>
	</tr>
	<?php endif; ?>
<?php endif; ?>

<?php if (!$this->Weather()->isEmpty() || $this->hasRoute() || !$this->Clothes()->areEmpty()): ?>
	<tr><td colspan="5"><br />&nbsp;</td></tr>
<?php endif; ?>

<?php if (!$this->Weather()->isEmpty()): ?>
	<tr>
		<td class="small b">Wetter:</td>
		<td><?php echo $this->Weather()->fullString(); ?></td>
	</tr>
<?php endif; ?>

<?php if ($this->hasRoute() || $this->hasElevation()): ?>
	<tr>
		<td class="small b">Strecke:</td>
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
		<td class="small b">Schuh:</td>
		<td><?php echo Shoe::getSeachLink($this->get('shoeid')); ?></td>
	</tr>
<?php endif; ?>

<?php if (!$this->Clothes()->areEmpty()): ?>
	<tr>
		<td class="small b">Kleidung:</td>
		<td><?php echo $this->Clothes()->asLinks(); ?></td>
	</tr>
<?php endif; ?>

<?php if ($this->hasPartner()): ?>
	<tr>
		<td class="small b">Partner:</td>
		<td><?php echo $this->getPartnerAsLinks(); ?></td>
	</tr>
<?php endif; ?>

</table>