<?php
/**
 * Window: explanations for calculations
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();
?>
<h1>Wie sich die Werte der Rechenspiele berechnen</h1>

<?php
$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Rechenspiele');

$Formular = new Formular();
$Formular->setId('rechenspiele-calculator');
$Formular->addCSSclass('ajax');
$Formular->addCSSclass('no-automatic-reload');
$Formular->addFieldset( $Plugin->getFieldsetTRIMP(), false );
$Formular->addFieldset( $Plugin->getFieldsetVDOT(), false );
$Formular->addFieldset( $Plugin->getFieldsetBasicEndurance() );
$Formular->addFieldset( $Plugin->getFieldsetPaces(), false );
$Formular->allowOnlyOneOpenedFieldset();
//$Formular->addSubmitButton('Berechnungen starten');
$Formular->display();
?>