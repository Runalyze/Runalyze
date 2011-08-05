<table>

<?php if ($this->hasDistance()): ?>
	<tr>
		<td class="small b">Distanz:</td>
		<td><?php echo $this->getDistanceString(); ?></td>
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

	<tr>
		<td class="small b">Kalorien:</td>
		<td><?php echo Helper::Unknown($this->get('kcal')); ?> kcal</td>
	</tr>

<?php if ($this->get('pulse_avg') != 0): ?>
	<tr>
		<td class="small b">Puls:</td>
		<td>&oslash; <?php echo Helper::Unknown($this->get('pulse_avg')); ?>bpm<br />
			max. <?php echo Helper::Unknown($this->get('pulse_max')); ?>bpm</td>
	</tr>
<?php endif; ?>

<?php if (!$this->Weather()->isEmpty() || $this->get('route') != '' || !$this->Clothes()->areEmpty()): ?>
	<tr><td colspan="5"><br />&nbsp;</td></tr>
<?php endif; ?>

<?php if (!$this->Weather()->isEmpty()): ?>
	<tr>
		<td class="small b">Wetter:</td>
		<td><?php echo $this->Weather()->fullString(); ?></td>
	</tr>
<?php endif; ?>

<?php if ($this->get('route') != '' || $this->get('elevation') > 0): ?>
	<tr>
		<td class="small b">Strecke:</td>
		<td><?php echo Helper::Unknown($this->get('route')); ?>
				<?php $berechnet = Training::calculateElevation($this->get('arr_alt')); ?>
			<?php if ($this->get('elevation') > 0 || $berechnet > 0): ?><br />
			<small>
				&nbsp;<?php echo $this->get('elevation'); ?> H&ouml;henmeter<br />
				<?php if ($berechnet != $this->get('elevation')): ?>
					&nbsp;<?php echo $berechnet ?> H&ouml;henmeter (berechnet)<br />
				<?php endif; ?>
				&nbsp;&oslash; <?php echo number_format($this->get('elevation')/10/$this->get('distance'), 2, ',', '.'); ?>&#37; Steigung
			</small>
			<?php endif; ?>
	</tr>
<?php endif; ?>

<?php if ($this->get('shoeid') != 0): ?>
	<tr>
		<td class="small b">Schuh:</td>
		<td><?php echo Shoe::getName($this->get('shoeid')); ?></td>
	</tr>
<?php endif; ?>

<?php if (!$this->Clothes()->areEmpty()): ?>
	<tr>
		<td class="small b">Kleidung:</td>
		<td><?php echo $this->Clothes()->asLinks(); ?></td>
	</tr>
<?php endif; ?>

<?php if ($this->get('partner') != ''): ?>
	<tr>
		<td class="small b">Partner:</td>
		<td><?php echo $this->get('partner'); ?></td>
	</tr>
<?php endif; ?>

</table>