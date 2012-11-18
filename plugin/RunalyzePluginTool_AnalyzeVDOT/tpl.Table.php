<table style="width:100%" id="vdotAnalysisTable">
	<thead>
		<tr class="small">
			<th class="{sorter:'germandate'}">Datum</th>
			<th>Lauf</th>
			<th class="{sorter:'distance'}">km</th>
			<th>Zeit</th>
			<th>VDOT</th>
			<th>Puls</th>
			<th>VDOT<br />(aus&nbsp;Puls)</th>
			<th>Zeit<br />(aus&nbsp;Puls)</th>
			<th>VDOT<br />(korrigiert)</th>
			<th>Zeit<br />(korrigiert)</th>
			<th>VDOT<br />(Form)</th>
			<th>Zeit<br />(Form)</th>
			<th>Abweichung<br />(Form)</th>
			<th>Korrektur<br />Faktor</th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($this->Trainings as $i => $Training): ?>
	<tr class="small r <?php echo HTML::trClass($i); ?>">
		<td class="c"><?php echo date("d.m.Y", $Training['time']); ?></td>
		<td class="b l"><?php echo $Training['comment']; ?></td>
		<td><?php echo Running::Km($Training['distance']); ?></td>
		<td class="b"><?php echo Time::toString(round($Training['s']), false, true); ?></td>
		<td><?php echo round(JD::Competition2VDOT($Training['distance'], $Training['s']), 2); ?></td>
		<td><?php echo $Training['pulse_avg']; ?></td>

		<td><?php echo $Training['vdot']; ?></td>
		<td class="b"><?php echo Time::toString(round(JD::CompetitionPrognosis($Training['vdot'], $Training['distance'])), false, true); ?></td>

		<?php $c_vdot = round(JD::correctVDOT($Training['vdot']),2); ?>
		<td><?php echo $c_vdot; ?></td>
		<td class="b"><?php echo Time::toString(round(JD::CompetitionPrognosis($c_vdot, $Training['distance'])), false, true); ?></td>

		<?php $shape     = round(JD::calculateVDOTform($Training['time']),2); ?>
		<?php $prognosis = JD::CompetitionPrognosis($shape, $Training['distance']); ?>
		<td><?php echo $shape; ?></td>
		<td class="b"><?php echo Time::toString(round($prognosis, false, true)); ?></td>
		<td><?php echo HTML::plusMinus(sprintf("%01.2f", 100*($prognosis - $Training['s'])/$Training['s']), 2); ?> &#37;</td>
		<td><?php echo sprintf("%1.4f", JD::VDOTcorrectorFor($Training['id'], $Training)); ?></td>
	</tr>
<?php endforeach; ?>
<?php if (empty($this->Trainings)): ?>
	<tr>
		<td colspan="12"><em>Du hast noch keine Wettk&auml;pfe eingetragen.</td>
	</tr>
<?php endif; ?>
	</tbody>
</table>

<?php if (!empty($this->Trainings)) Ajax::createTablesorterWithPagerFor('#vdotAnalysisTable', true); ?>

<p class="info">
	<strong>VDOT/Zeit (normal):</strong> nach Standard-Formeln von Jack Daniels<br />
	Die Zeit h&auml;ttest du bei maximalem Wettkampf-Puls (laut Jack Daniels) laufen k&ouml;nnen.
</p>
<p class="info">
	<strong>VDOT/Zeit (Korrektur):</strong> nach individueller VDOT-Korrektur (Faktor <?php echo VDOT_CORRECTOR; ?>)<br />
	Die Zeit h&auml;ttest du bei maximalem Wettkampf-Puls (laut Jack Daniels, an dich angepasst) laufen k&ouml;nnen.
</p>
<p class="info">
	<strong>VDOT/Zeit (Form):</strong> aus der damaligen Trainingsform bzw. Prognose<br />
	Das h&auml;ttest du Runalyze zufolge laufen k&ouml;nnen.
</p>

<?php echo Ajax::wrapJSforDocumentReady('$("#ajax").addClass("bigWin");'); ?>