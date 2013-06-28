<div style="width:700px;margin:0 auto;">
	<table id="jd-tables-prognosis">
		<thead>
			<tr class="small r">
				<th>VDOT</th>
			<?php foreach (array_keys($this->Paces) as $key): ?>
				<th><?php echo $key; ?></th>
			<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
	<?php foreach ($this->Range as $vdot): ?>
			<tr class="small r <?php echo HTML::trClass($vdot); if (round(VDOT_FORM) == $vdot) echo ' highlight'; ?>">
				<td class="b"><?php echo $vdot; ?></td>
			<?php foreach ($this->Paces as $data): ?>
				<td><?php echo JD::v2Pace(JD::VDOT2v($vdot)*($data['percent'])/100); ?></td>
			<?php endforeach; ?>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>
</div>

<p class="info">
	Diese Tabelle richtet sich nach von Jack Daniels angegebenen Prozentwerten (vom VDOT) f&uuml;r die verschiedenen Tempobereiche.
	F&uuml;r seine eigene Tabelle hat er jedoch scheinbar andere Werte zugrunde gelegt.
	Besonders f&uuml;r niedrige VDOT-Werte gibt es hier einige Abweichungen.
</p>

<?php echo Ajax::wrapJSforDocumentReady('$("#jd-tables-prognosis").fixedHeaderTable({height:400});'); ?>