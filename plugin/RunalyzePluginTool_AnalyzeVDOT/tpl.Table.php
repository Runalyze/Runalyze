<table id="vdotAnalysisTable" class="fullwidth zebra-style">
	<thead>
		<tr>
			<th class="{sorter:'germandate'}">Datum</th>
			<th>Lauf</th>
			<th class="{sorter:'distance'}">km</th>
			<th>Zeit</th>
			<th>VDOT</th>
			<th>Puls</th>
			<th>VDOT<br /><small>(aus&nbsp;Puls)</small></th>
			<th>Zeit<br /><small>(aus&nbsp;Puls)</small></th>
			<th>VDOT<br /><small>(korrigiert)</small></th>
			<th>Zeit<br /><small>(korrigiert)</small></th>
			<th>VDOT<br /><small>(Form)</small></th>
			<th>Zeit<br /><small>(Form)</small></th>
			<th>Abweichung<br /><small>(Form)</small></th>
			<th>Korrektur<br />Faktor</th>
		</tr>
	</thead>
	<tbody class="r">
<?php foreach ($this->Trainings as $Training): ?>
	<tr>
		<td class="small c"><?php echo date("d.m.Y", $Training['time']); ?></td>
		<td class="b l"><?php echo $Training['comment']; ?></td>
		<td><?php echo Running::Km($Training['distance']); ?></td>
		<td class="b"><?php echo Time::toString(round($Training['s']), false, true); ?></td>
		<td><?php echo round(JD::Competition2VDOT($Training['distance'], $Training['s']), 2); ?></td>
		<td><?php echo $Training['pulse_avg']; ?></td>

		<td><?php echo $Training['vdot']; ?></td>
		<td class="b"><?php echo Time::toString(round(JD::CompetitionPrognosis($Training['vdot'], $Training['distance'])), false, true); ?></td>

		<?php if (CONF_JD_USE_VDOT_CORRECTOR): ?>
		<?php $c_vdot = round(JD::correctVDOT($Training['vdot']),2); ?>
		<td><?php echo $c_vdot; ?></td>
		<td class="b"><?php echo Time::toString(round(JD::CompetitionPrognosis($c_vdot, $Training['distance'])), false, true); ?></td>
		<?php else: ?>
		<td>-</td>
		<td class="b">-</td>
		<?php endif; ?>

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
<?php if (CONF_JD_USE_VDOT_CORRECTOR): ?>
<p class="info">
	<strong>VDOT/Zeit (Korrektur):</strong> nach individueller VDOT-Korrektur (Faktor <?php echo JD::correctionFactor(); ?>)<br />
	Die Zeit h&auml;ttest du bei maximalem Wettkampf-Puls (laut Jack Daniels, an dich angepasst) laufen k&ouml;nnen.
</p>
<?php else: ?>
<p class="warning">
	<strong>VDOT/Zeit (Korrektur):</strong>
	Die Korrektur ist in der Konfiguration abgeschaltet.
</p>
<?php endif; ?>
<p class="info">
	<strong>VDOT/Zeit (Form):</strong> aus der damaligen Trainingsform bzw. Prognose<br />
	Das h&auml;ttest du Runalyze zufolge laufen k&ouml;nnen.
</p>
<p class="info">
	<strong>Korrekturfaktor:</strong> Verh&auml;ltnis von VDOT zu VDOT (aus Puls)
</p>

<?php if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION): ?>
<p class="warning">
	Die Distanz-Korrektur f&uuml;r H&ouml;henmeter wird hier nicht verwendet.
</p>
<?php endif; ?>

<?php echo Ajax::wrapJSforDocumentReady('$("#ajax").addClass("big-window");'); ?>