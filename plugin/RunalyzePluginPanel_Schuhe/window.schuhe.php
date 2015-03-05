<?php
/**
 * Window: formular for shoes
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (Request::param('delete') == 'true') {
	DB::getInstance()->deleteByID('shoe', (int)Request::sendId());
	DB::getInstance()->query('UPDATE `' . PREFIX . 'training` SET `shoeid`=0 WHERE `shoeid`=' . (int)Request::sendId());
	Cache::delete(ShoeFactory::CACHE_KEY);

	header('Location: window.schuhe.table.php?reload=true');
	exit;
}

if (Request::sendId() === false) {
	$Header = __('Add new shoe');
	$Mode = StandardFormular::$SUBMIT_MODE_CREATE;
	$Shoe = new Shoe(DataObject::$DEFAULT_ID);
} else {
	$Header = __('Edit shoe');
	$Mode = StandardFormular::$SUBMIT_MODE_EDIT;
	$Shoe = new Shoe(Request::sendId());
}

$Formular = new StandardFormular($Shoe, $Mode);

if ($Formular->submitSucceeded()) {
	header('Location: window.schuhe.table.php');
	ShoeFactory::clearCache();
	exit;
}

if (Request::sendId() > 0) {
	$DeleteText = '<strong>' . __('Delete shoe') . ' &raquo;</strong>';
	$DeleteUrl = $_SERVER['SCRIPT_NAME'] . '?delete=true&id=' . $Shoe->id();
	$DeleteLink = Ajax::link($DeleteText, 'ajax', $DeleteUrl);

	if ($Shoe->getKm() != $Shoe->getAdditionalKm())
		$DeleteLink = __('The shoe cannot be deleted as long it is used for some activity.');

	$DeleteFieldset = new FormularFieldset(__('Delete shoe'));
	$DeleteFieldset->addWarning($DeleteLink);

	$Formular->addFieldset($DeleteFieldset);
}

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Schuhe');

echo '<div class="panel-heading">';
echo '<div class="panel-menu"><ul><li>' . $Plugin->tableLink() . '</li></ul></div>';
echo '<h1>' . $Header . '</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Formular->setId('shoe');
$Formular->setLayoutForFields(FormularFieldset::$LAYOUT_FIELD_W33);
$Formular->display();
echo '</div>';
