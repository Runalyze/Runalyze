<table id="jd-tables-prognosis" class="zebra-style c" style="width: 700px;">
	<thead>
		<tr>
			<th>VDOT</th>
		<?php foreach (array_keys($this->Paces) as $key): ?>
			<th><?php echo $key; ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
<?php foreach ($this->Range as $vdot): ?>
		<tr<?php if (round(VDOT_FORM) == $vdot) echo ' class="highlight"'; ?>>
			<td class="b"><?php echo $vdot; ?></td>
		<?php foreach ($this->Paces as $data): ?>
			<td><?php echo JD::v2Pace(JD::VDOT2v($vdot)*($data['percent'])/100); ?></td>
		<?php endforeach; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<p class="info">
	<?php _e('This table is computed by some formulas, derived from the tables in Jack Daniels\' Running formula.'); ?>
	<?php _e('These values do not fit the original table one hundred percent, especially for low VDOT values.'); ?>
</p>