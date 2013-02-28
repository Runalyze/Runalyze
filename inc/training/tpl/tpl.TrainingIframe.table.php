<?php
$INFOS_LEFT  = array();
$INFOS_RIGHT = array();
$INFOS_FULL  = array();

if ($this->hasDistance()) {
	$INFOS_LEFT[] = array('Distanz', $this->getDistanceStringWithFullDecimals());
	$INFOS_LEFT[] = array('Tempo',   $this->getSpeedString());
}

if ($this->hasPulse()) {
	$INFOS_RIGHT[] = array('&oslash;&nbsp;Puls', Running::PulseStringInBpm($this->get('pulse_avg')));
	$INFOS_RIGHT[] = array('max.&nbsp;Puls',     Running::PulseStringInBpm($this->get('pulse_max')));
}

$INFOS_LEFT[] = array('Zeit',     $this->getTimeString());
$INFOS_LEFT[] = array('Kalorien', Helper::Unknown($this->get('kcal')).' kcal');

if (CONF_RECHENSPIELE) {
	$INFOS_RIGHT[] = array('Trimp', $this->getTrimpString());

	if ($this->Sport()->isRunning() && $this->getVDOT() > 0)
		$INFOS_RIGHT[] = array('Vdot', $this->getVDOT().' '.$this->getVDOTicon());
}

if ($this->hasPartner())
	$INFOS_LEFT[] = array('Partner', $this->getPartner());

if ($this->get('shoeid') != 0)
	$INFOS_LEFT[] = array('Schuh', Shoe::getNameOf($this->get('shoeid')));

if (!$this->Weather()->isEmpty())
	$INFOS_RIGHT[] = array('Wetter', $this->Weather()->fullString());

if (!$this->Clothes()->areEmpty())
	$INFOS_RIGHT[] = array('Kleidung', $this->Clothes()->asString());

if ($this->hasRoute() ||$this->hasElevation()) {
	$berechnet = $this->GpsData()->calculateElevation();
	$routeInfo = Helper::Unknown($this->get('route'));

	if ($this->hasElevation() > 0 || $berechnet > 0) {
		$routeInfo .= ', '.$this->get('elevation').' H&ouml;henmeter';

		if ($berechnet != $this->get('elevation'))
			$routeInfo .= ', '.$berechnet.' berechnet';

		$routeInfo .= ', '.number_format($this->get('elevation')/10/$this->get('distance'), 2, ',', '.').' &#37; Steigung';
	}

	$INFOS_FULL[] = array('Strecke', $routeInfo);
}
?>



<table class="small fullWidth">
<?php
$num = max(array(count($INFOS_LEFT), count($INFOS_RIGHT)));

for ($i = 0; $i < $num; $i++) {
	echo '<tr>';

	if (isset($INFOS_LEFT[$i])) {
		echo '<td class="inlineHead">'.$INFOS_LEFT[$i][0].'</td>';
		echo '<td>'.$INFOS_LEFT[$i][1].'</td>';
	} else {
		echo '<td colspan="2">&nbsp;</td>';
	}

	if (isset($INFOS_RIGHT[$i])) {
		echo '<td class="inlineHead">'.$INFOS_RIGHT[$i][0].'</td>';
		echo '<td>'.$INFOS_RIGHT[$i][1].'</td>';
	} else {
		echo '<td colspan="2">&nbsp;</td>';
	}

	echo '</tr>';
}

foreach ($INFOS_FULL as $INFO) {
	echo '<tr>';
	echo '<td class="inlineHead">'.$INFO[0].'</td>';
	echo '<td colspan="3">'.$INFO[1].'</td>';
	echo '</tr>';
}
?>
</table>