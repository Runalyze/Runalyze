<?php
/**
 * Window: formular for user
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (Request::param('delete') == 'true') {
	DB::getInstance()->deleteByID('user', (int)Request::sendId());
	Cache::delete(UserData::CACHE_KEY);

	header('Location: window.sportler.table.php?reload=true');
	exit;
}

if (Request::sendId() === false) {
	$Header   = __('Add body values');
	$Mode     = StandardFormular::$SUBMIT_MODE_CREATE;
	$UserData = new UserData( DataObject::$LAST_OBJECT );
	$UserData->setCurrentTimestamp();
} else {
	$Header   =  __('Edit body values');
	$Mode     = StandardFormular::$SUBMIT_MODE_EDIT;
	$UserData = new UserData( Request::sendId() );
}

$Formular = new StandardFormular($UserData, $Mode);

if ($Formular->submitSucceeded()) {
	header('Location: window.sportler.table.php?reload=true');
	exit;
}

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Sportler');

echo '<div class="panel-heading">';
echo '<div class="panel-menu"><ul><li>'.$Plugin->tableLink().'</li></ul></div>';
echo '<h1>'.$Header.'</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Formular->addCSSclass('no-automatic-reload');
$Formular->setId('sportler');
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
$Formular->display();
echo '</div>';