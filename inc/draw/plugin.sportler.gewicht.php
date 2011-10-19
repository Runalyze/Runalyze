<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   inc/draw/plugin.sportler.gewicht.php
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(320, 148);
$Draw->padding['left']   = 20;
$Draw->padding['right']  = 35;
$Draw->padding['bottom'] = 7;

// TODO: Set as config-var?
$data_num     = 20;
$titleError   = '';
$Weights      = array();
$HRrests      = array();

$data = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'user` ORDER BY `time` DESC LIMIT '.$data_num);

if (!empty($data)) {
	foreach ($data as $dat) {
		$Weights[] = $dat['weight'];
		$HRrests[] = $dat['pulse_rest'];
	}

	$Weights = array_reverse($Weights);
	$HRrests = array_reverse($HRrests);
} else {
	$titleError = 'Es sind keine Daten vorhanden.';
}


$ScaleFormat    = array("DrawYLines" => array(1), "TickAlpha" => 50);
$LegendFormat   = array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL);
$TresholdFormat = array("AxisID" => 1,
	"WriteCaption" => TRUE, "Caption" => "Wunschgewicht", "CaptionAlign" => CAPTION_LEFT_TOP,
	"R" => 0, "G" => 136, "B" => 0, "Alpha" => 100);

if (!empty($Weights)) {
	$WeightFormat = array("R" => 0, "G" => 0, "B" => 136);
	$Draw->pData->addPoints($Weights, 'Gewicht');
	$Draw->pData->setSerieOnAxis('Gewicht', 1);
	$Draw->pData->setAxisPosition(1, AXIS_POSITION_RIGHT);
	$Draw->pData->setAxisUnit(1, ' kg');
	$Draw->pData->setPalette('Gewicht', $WeightFormat);
}

if (!empty($HRrests)) {
	$HRFormat = array("R" => 136, "G" => 0, "B" => 0);
	$Draw->pData->addPoints($HRrests, 'Ruhepuls');
	$Draw->pData->setSerieOnAxis('Ruhepuls', 0);
	$Draw->pData->setPalette('Ruhepuls', $HRFormat);
}

$xAxis = array();
for ($i = 0; $i < $data_num; $i++)
	$xAxis[] = '';
$Draw->pData->addPoints($xAxis, 'Labels');
$Draw->pData->setAbscissa('Labels');

$Draw->startImage();
if ($titleError == '')
	$Draw->drawScale($ScaleFormat);
$Draw->drawLineChart();

$Draw->pImage->drawLegend(130, 15, $LegendFormat);

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
$Plugin_conf = $Plugin->get('config');
if ($Plugin_conf['wunschgewicht']['var'] > 1 && $Plugin_conf['wunschgewicht']['var'] > $Draw->pData->getMin('Gewicht'))
	$Draw->pImage->drawThreshold($Plugin_conf['wunschgewicht']['var'], $TresholdFormat);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>