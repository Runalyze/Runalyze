<?php
use Runalyze\Calculation\JD\VDOT;
use Runalyze\Configuration;
use Runalyze\Activity\Duration;

$VDOT = new VDOT;
?>

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
		<?php foreach ($this->Range as $value): ?>
		<?php $VDOT->setValue($value); ?>
		<tr>
			<td class="b"><?php echo $value; ?></td>
			<?php foreach ($this->Paces as $data): ?>
			<td><?php echo Duration::format($VDOT->paceAt($data['percent']/100)); ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php
echo Ajax::wrapJS('$("#jd-tables-prognosis td.b").each(function(){ if ($(this).text() == \''.round(Configuration::Data()->vdot()).'\') $(this).parent().addClass("highlight"); });');
?>

<p class="info">
	<?php _e('This table is computed by some formulas, derived from the tables in Jack Daniels\' Running formula.'); ?>
	<?php _e('These values do not fit the original table one hundred percent, especially for low VDOT values.'); ?>
</p>