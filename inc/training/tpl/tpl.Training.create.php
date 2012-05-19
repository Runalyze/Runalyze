<?php
/**
 * File displaying the formular for adding a new training, called via Training::displayCreateWindow()
 */

$Links = array();
$Links[] = array('tag' => Ajax::change('Hochladen', 'ajax', 'upload'));
$Links[] = array('tag' => Ajax::change('GarminCommunicator', 'ajax', 'garmin'));
$Links[] = array('tag' => Ajax::change('Formular', 'ajax', 'formular'));

echo Ajax::toolbarNavigation($Links, 'right');

$hideUpload   = (CONF_TRAINING_CREATE_MODE != 'upload' || !$showUploader);
$hideGarmin   = (CONF_TRAINING_CREATE_MODE != 'garmin' || !$showUploader);
$hideFormular = (CONF_TRAINING_CREATE_MODE != 'form' && $showUploader);

if (isset($_GET['sportid'])) {
	$hideUpload   = true;
	$hideGarmin   = true;
	$hideFormular = false;
}
?>

<div class="change" id="upload"<?php if ($hideUpload) echo ' style="display:none;"'; ?> onmouseover="javascript:createUploader()">
	<?php $Importer->displayUploadFormular(); ?>
</div>

<div class="change" id="garmin"<?php if ($hideGarmin) echo ' style="display:none;"'; ?>>
	<?php $Importer->displayGarminCommunicator(); ?>
</div>

<div class="change" id="formular"<?php if ($hideFormular) echo ' style="display:none;"'; ?>>
	<?php $Importer->displayHTMLformular(); ?>
</div>