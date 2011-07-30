<table>
<?php if ($this->get('distanz') != 0): ?>
	<tr>
		<td class="small b">Distanz:</td>
		<td><?php echo Helper::Km($this->get('distanz'), 2, $this->get('bahn')); ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td class="small b">Zeit:</td>
		<td><?php echo Helper::Time($this->get('dauer')); ?></td>
	</tr>
<?php if ($this->get('distanz') != 0): ?>
	<tr>
		<td class="small b">Tempo:</td>
		<td><?php echo $this->get('pace'); ?>/km<br />
			<?php echo Helper::Kmh($this->get('distanz'), $this->get('dauer')); ?> km/h</td>
	</tr>
<?php endif; ?>
	<tr>
		<td class="small b">Kalorien:</td>
		<td><?php echo Helper::Unknown($this->get('kalorien')); ?> kcal</td>
	</tr>
<?php if ($this->get('puls') != 0): ?>
	<tr>
		<td class="small b">Puls:</td>
		<td>&oslash; <?php echo Helper::Unknown($this->get('puls')); ?>bpm<br />
			max. <?php echo Helper::Unknown($this->get('puls_max')); ?>bpm</td>
	</tr>
<?php endif; ?>
<?php if ($this->get('wetterid') != 0 OR $this->get('temperatur') != NULL OR $this->get('strecke') != '' OR $this->getStringForClothes() != ''): ?>
	<tr><td colspan="5"><br />&nbsp;</td></tr>
<?php endif; ?>
<?php if ($this->get('wetterid') != 0 OR $this->get('temperatur') != NULL): ?>
	<tr>
		<td class="small b">Wetter:</td>
		<td><?php echo(Helper::WeatherImage($this->get('wetterid')).' '.Helper::WeatherName($this->get('wetterid')).' bei '.Helper::Unknown($this->get('temperatur')).' &#176;C'); ?></td>
	</tr>
<?php endif; ?>
<?php if ($this->get('strecke') != ''): ?>
	<tr>
		<td class="small b">Strecke:</td>
		<td><?php echo $this->get('strecke'); ?>
			<?php if ($this->get('hm') > 0): ?><br />
			<small>
				&nbsp;<?php echo $this->get('hm'); ?> H&ouml;henmeter<br />
				&nbsp;&oslash; <?php echo number_format($this->get('hm')/10/$this->get('distanz'), 2, ',', '.'); ?>&#37; Steigung
			</small>
			<?php endif; ?>
	</tr>
<?php endif; ?>
<?php if ($this->get('schuhid') != 0): ?>
	<tr>
		<td class="small b">Schuh:</td>
		<td><?php echo Helper::Shoe($this->get('schuhid')); ?></td>
	</tr>
<?php endif; ?>
<?php if ($this->getStringForClothes() != ''): ?>
	<tr>
		<td class="small b">Kleidung:</td>
		<td><?php echo $this->getStringForClothes(); ?></td>
	</tr>
<?php endif; ?>
<?php if ($this->get('trainingspartner') != ''): ?>
	<tr>
		<td class="small b">Partner:</td>
		<td><?php echo $this->get('trainingspartner'); ?></td>
	</tr>
<?php endif; ?>
</table>