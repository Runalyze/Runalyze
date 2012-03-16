<table class="small">
	<thead>
		<tr>
			<th>Zeit</th>
			<th>Distanz</th>
			<th>Tempo</th>
			<?php if ($showCellForHeartrate) echo '<th>bpm</th>'; ?>
			<?php if ($showCellForElevation) echo '<th>hm</th>'; ?>
	</thead>
	<tbody>
<?php foreach ($Data as $i => $Info): ?>
		<tr class="r <?php echo HTML::trClass2($i); ?>">
			<td><?php echo $Info['time']; ?></td>
			<td><?php echo $Info['distance']; ?></td>
			<td><?php echo $Info['pace']; ?></td>
			<?php if ($showCellForHeartrate) echo '<td>'.$Info['heartrate'].'</td>'; ?>
			<?php if ($showCellForElevation) echo '<td>'.$Info['elevation'].'</td>'; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>