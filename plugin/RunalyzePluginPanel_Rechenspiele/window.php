<div class="panel-heading">
    <h1><?php _e('Explanation: How are these experimental values calculated?'); ?></h1>
</div>

<div class="panel-content">
<?php
$Factory = new PluginFactory();
/** @var RunalyzePluginPanel_Rechenspiele $Plugin */
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Rechenspiele');

$Formular = new Formular();
$Formular->setId('rechenspiele-calculator');
$Formular->addCSSclass('ajax');
$Formular->addCSSclass('no-automatic-reload');
$Formular->addFieldset($Plugin->getFieldsetTRIMP(), false);
$Formular->addFieldset($Plugin->getFieldsetEffecticeVO2max(), false);
$Formular->addFieldset($Plugin->getFieldsetBasicEndurance());
$Formular->allowOnlyOneOpenedFieldset();
$Formular->display();
?>
</div>
