<table style="width:100%" class="sortable pager">
	<thead>
		<tr class="small">
			<th class="{sorter:'germandate'}">Datum</th>
			<th>Lauf</th>
			<th class="{sorter:'distance'}">km</th>
			<th>Zeit</th>
			<th>Puls</th>
			<th>VDOT<br />Wert</th>
			<th>VDOT<br />Zeit</th>
			<th>korr.<br />Wert</th>
			<th>korr.<br />Zeit</th>
			<th>Form<br />Wert</th>
			<th>Form<br />Zeit</th>
			<th>Form<br /><abbr title="Abweichung">Abw.</abbr></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($this->Trainings as $i => $Training): ?>
	<tr class="small r <?php echo HTML::trClass($i); ?>">
		<td><?php echo date("d.m.Y", $Training['time']); ?></td>
		<td class="b l"><?php echo $Training['comment']; ?></td>
		<td><?php echo Helper::Km($Training['distance']); ?></td>
		<td class="b"><?php echo Helper::Time(round($Training['s']), false, true); ?></td>
		<td><?php echo $Training['pulse_avg']; ?></td>

		<td><?php echo $Training['vdot']; ?></td>
		<td class="b"><?php echo Helper::Time(JD::CompetitionPrognosis($Training['vdot'], $Training['distance']), false, true); ?></td>

		<?php $c_vdot = round(JD::correctVDOT($Training['vdot']),2); ?>
		<td><?php echo $c_vdot; ?></td>
		<td class="b"><?php echo Helper::Time(JD::CompetitionPrognosis($c_vdot, $Training['distance']), false, true); ?></td>

		<?php $shape     = round(JD::calculateVDOTform($Training['time']),2); ?>
		<?php $prognosis = JD::CompetitionPrognosis($shape, $Training['distance']); ?>
		<td><?php echo $shape; ?></td>
		<td class="b"><?php echo Helper::Time($prognosis, false, true); ?></td>
		<td><?php echo HTML::plusMinus(sprintf("%01.2f", 100*($Training['s'] - $prognosis)/$Training['s']), 2); ?> &#37;</td>
	</tr>
<?php endforeach; ?>
	</tbody>
</table>

<?php Ajax::printPagerDiv(); ?>
<?php echo Ajax::wrapJSforDocumentReady('jTablesorterWithPagination()'); ?>

<p class="info small">
	<strong>VDOT Wert:</strong> VDOT aus Pace-Puls-Verh&auml;ltnis<br />
	<strong>korr. Wert:</strong> VDOT nach individueller VDOT-Korrektur (Faktor <?php echo VDOT_CORRECTOR; ?>)<br />
	<strong>Form Wert:</strong> VDOT der damaligen Trainingsform bzw. Prognose
</p>