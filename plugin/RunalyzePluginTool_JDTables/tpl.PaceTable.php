<div style="width:700px;margin:0 auto;">
	<table id="jd-tables-pace">
		<thead>
			<tr class="small r">
			<?php foreach ($this->config['pace_distances']['var'] as $km): ?>
				<th><?php echo Running::Km($km, 1, ($km <= 3)); ?></th>
			<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
	<?php foreach ($this->Range as $i => $sPer400m): ?>
			<tr class="small r <?php echo HTML::trClass($i); ?>">
			<?php foreach ($this->config['pace_distances']['var'] as $km): $s = $km > 0.4 ? round($km * $sPer400m/0.4) : $km * $sPer400m/0.4; ?>
				<td><?php echo Time::toString($s); ?></td>
			<?php endforeach; ?>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php echo Ajax::wrapJSforDocumentReady('$("#jd-tables-pace").fixedHeaderTable({height:400});'); ?>