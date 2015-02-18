<?php
/**
 * Window: explanations for calculations
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();
?>
<div class="panel-heading">
	<h1><?php _e('Explanation: How are these experimental values calculated?'); ?></h1>
</div>

<div class="panel-content">
<?php
$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Rechenspiele');

$Formular = new Formular();
$Formular->setId('rechenspiele-calculator');
$Formular->addCSSclass('ajax');
$Formular->addCSSclass('no-automatic-reload');
$Formular->addFieldset( $Plugin->getFieldsetTRIMP(), false );
$Formular->addFieldset( $Plugin->getFieldsetVDOT(), false );
$Formular->addFieldset( $Plugin->getFieldsetBasicEndurance() );
$Formular->addFieldset( $Plugin->getFieldsetPaces(), false );
$Formular->allowOnlyOneOpenedFieldset();
$Formular->display();
?>
</div>