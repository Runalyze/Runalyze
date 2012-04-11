<?php
/**
 * File displaying the formular for adding a new training, called via Training::displayCreateWindow()
 */
?>

<?php
$Links = array();
$Links[] = array('tag' => Ajax::change('Hochladen', 'ajax', 'upload'));
$Links[] = array('tag' => Ajax::change('GarminCommunicator', 'ajax', 'garmin'));
$Links[] = array('tag' => Ajax::change('Formular', 'ajax', 'formular'));

echo Ajax::toolbarNavigation($Links, 'right');

Error::getInstance()->addError('Testing');
?>

<div class="change" id="upload"<?php if (CONF_TRAINING_CREATE_MODE != 'upload' || !$showUploader) echo ' style="display:none;"'; ?> onmouseover="javascript:createUploader()">
	<?php $Importer->displayUploadFormular(); ?>
</div>

<div class="change" id="garmin"<?php if (CONF_TRAINING_CREATE_MODE != 'garmin' || !$showUploader) echo ' style="display:none;"'; ?>>
	<?php $Importer->displayGarminCommunicator(); ?>
</div>

<div class="change" id="formular"<?php if (CONF_TRAINING_CREATE_MODE != 'form' && $showUploader) echo ' style="display:none;"'; ?>>
	<?php $Importer->displayHTMLformular(); ?>
</div>