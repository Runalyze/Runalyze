<?php
$Lines = array();

if ($this->hasDistance())
	$Lines[] = array('Distanz', $this->getDistanceStringWithFullDecimals());

	$Lines[] = array('Zeit', $this->getTimeString());

if ($this->hasDistance())
	$Lines[] = array('Tempo', $this->getSpeedInMainUnit().' <small>('.$this->getSpeedInAlternativeUnit().')</small>');

if ($this->hasPulse()) {
	$Lines[] = array('&oslash;&nbsp;Puls', Running::PulseStringInBpm($this->get('pulse_avg')).' <small>('.Running::PulseStringInPercent($this->get('pulse_avg')).')</small>');
	$Lines[] = array('max.&nbsp;Puls', Running::PulseStringInBpm($this->get('pulse_max')).' <small>('.Running::PulseStringInPercent($this->get('pulse_max')).')</small>');
}

if ($this->get('kcal') > 0)
	$Lines[] = array('Kalorien', $this->get('kcal').' kcal');

if (CONF_RECHENSPIELE)
	$Lines[] = array('Trimp', $this->getTrimpString());

if (CONF_RECHENSPIELE && $this->Sport()->isRunning() && $this->getVDOT() > 0)
	$Lines[] = array('Vdot', $this->getVDOT().' '.$this->getVDOTicon());


$Outsides = array();

if (!$this->Weather()->isEmpty())
	$Outsides[] = array('Wetter', $this->Weather()->fullString());

if ($this->hasRoute())
	$Outsides[] = array('Strecke', HTML::encodeTags($this->get('route')));

$calculated = $this->GpsData()->calculateElevation();
$difference = $this->GpsData()->getElevationDifference();
if ($this->hasElevation() || $calculated > 0) {
	$Text = $this->get('elevation').'&nbsp;m';

	if ($calculated != $this->get('elevation'))
		$Text .= ' <small>('.$calculated.'&nbsp;m berechnet)</small>';

	if (CONF_TRAINING_DO_ELEVATION && $this->get('elevation_corrected') != 1)
		$Text .= '<br />
			<em id="gps-results" class="block">
				Die H&ouml;hendaten sind noch nicht korrigiert.
				<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id='.$this->id().'" title="H&ouml;hendaten korrigieren"><strong>&raquo; jetzt korrigieren</strong></a>
			</em>';

	$Outsides[] = array('H&ouml;henmeter', $Text);
}
if ($difference > 20)
	$Outsides[] = array('H&ouml;henunterschied', Math::WithSign($difference).'m');
if ($this->hasElevation())
	$Outsides[] = array('Steigung', number_format($this->get('elevation')/10/$this->get('distance'), 2, ',', '.').'&nbsp;&#37;');

if ($this->get('shoeid') > 0)
	$Outsides[] = array('Schuh', Request::isOnSharedPage() ? Shoe::getNameOf($this->get('shoeid')) : Shoe::getSearchLink($this->get('shoeid')));

if (!$this->Clothes()->areEmpty())
	$Outsides[] = array('Kleidung', Request::isOnSharedPage() ? $this->Clothes()->asString() : $this->Clothes()->asLinks());

if ($this->hasPartner())
	$Outsides[] = array('Partner', Request::isOnSharedPage() ? $this->getPartner() : $this->getPartnerAsLinks());

if ($this->hasNotes())
	$Outsides[] = array('Notizen', $this->getNotes());

if (!empty($Outsides))
	$Lines = array_merge($Lines, array(array('&nbsp;', '')), $Outsides);
?>
<table class="small">
	<tbody>
	<?php foreach ($Lines as $Line): ?>
		<tr>
			<td class="inlineHead"><?php echo $Line[0]; ?></td>
			<td><?php echo $Line[1]; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>

<?php
$ExtraLines = array();

if ($this->get('created') > 0)
	$ExtraLines[] = array('Erstellt', 'am '.date('d.m.Y', $this->get('created')));
if ($this->get('edited') > 0)
	$ExtraLines[] = array('Bearbeitet', 'zuletzt am '.date('d.m.Y', $this->get('edited')));

if ($this->get('creator') == Importer::$CREATOR_FILE)
	$ExtraLines[] = array('Importer', 'Datei-Upload');
elseif ($this->get('creator') == Importer::$CREATOR_GARMIN_COMMUNICATOR)
	$ExtraLines[] = array('Importer', 'Garmin-Communicator');

if ($this->hasElevationData())
	$ExtraLines[] = array('H&ouml;hendaten', ($this->get('elevation_corrected') == 0 ? 'noch nicht ' : '').'korrigiert');

if (!empty($ExtraLines)):
?>

	<tbody id="training-table-extra">
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr class="space">
			<td colspan="2"></td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>

		<?php foreach ($ExtraLines as $Line): ?>
		<tr>
			<td class="inlineHead"><?php echo $Line[0]; ?>:</td>
			<td><?php echo $Line[1]; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
<?php else: ?>
	<?php echo Ajax::wrapJSasFunction('$("#training-view-toggler-details").remove();'); ?>
<?php endif; ?>
</table>