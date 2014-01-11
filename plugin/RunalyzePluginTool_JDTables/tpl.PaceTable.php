<table id="jd-tables-prognosis" class="zebra-style r" style="width: 700px;">
	<thead>
		<tr>
		<?php foreach ($this->config['pace_distances']['var'] as $km): ?>
			<th><?php echo Running::Km($km, 1, ($km <= 3)); ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
<?php foreach ($this->Range as $sPer400m): ?>
		<tr>
		<?php foreach ($this->config['pace_distances']['var'] as $km): $s = $km > 0.4 ? round($km * $sPer400m/0.4) : $km * $sPer400m/0.4; ?>
			<td><?php echo Time::toString($s); ?></td>
		<?php endforeach; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>