<?php
use Runalyze\Activity\Duration;

$Duration = new Duration();
?>
<table id="jd-tables-prognosis" class="zebra-style r" style="width: 700px;">
	<thead>
		<tr>
			<?php foreach ($this->Configuration()->value('pace_distances') as $km): ?>
			<th><?php echo Running::Km($km, 1, ($km <= 3)); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->Range as $sPer400m): ?>
		<tr>
			<?php foreach ($this->Configuration()->value('pace_distances') as $km): ?>
			<?php $Duration->fromSeconds(($km * $sPer400m/0.4)); ?>
			<td><?php echo $Duration->string('auto', $km >= 0.4 ? 0 : 1); ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>