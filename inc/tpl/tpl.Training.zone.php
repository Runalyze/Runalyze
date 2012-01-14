<strong class="small"><?php echo $title; ?>:</strong>

<table class="small">
	<thead>
		<tr>
			<th>Zone</th>
			<th>Anteil</th>
			<th>Zeit</th>
			<th>Distanz</th>
			<?php if ($showCellForAverageData) echo '<th>'.$titleForAverage.'</th>'; ?>
	</thead>
	<tbody>
<?php foreach ($Data as $i => $Info): ?>
		<tr class="r <?php echo HTML::trClass2($i); ?>" style="opacity:<?php echo (0.5 + $Info['percentage']/200); ?>;">
			<td><?php echo $Info['zone']; ?></td>
			<td><?php echo $Info['percentage']; ?>&nbsp;&#37;</td>
			<td><?php echo $Info['time']; ?></td>
			<td><?php echo $Info['distance']; ?></td>
			<?php if ($showCellForAverageData) echo '<td>'.$Info['average'].'</td>'; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>