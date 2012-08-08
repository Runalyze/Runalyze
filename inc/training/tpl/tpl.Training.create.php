<?php
/**
 * File displaying the formular for adding a new training, called via TrainingCreator::displayWindow()
 */
$hideUpload   = ((CONF_TRAINING_CREATE_MODE != 'upload' && CONF_TRAINING_CREATE_MODE != 'tcx') || !$showUploader);
$hideGarmin   = (CONF_TRAINING_CREATE_MODE != 'garmin' || !$showUploader);
$hideFormular = (CONF_TRAINING_CREATE_MODE != 'form' && $showUploader);

$Links = array();
$Links[] = array('tag' => Ajax::change('Hochladen', 'ajax', 'upload', ($hideUpload) ? '' : 'triggered'));
$Links[] = array('tag' => Ajax::change('GarminCommunicator', 'ajax', 'garmin', ($hideGarmin) ? '' : 'triggered'));
$Links[] = array('tag' => Ajax::change('Formular', 'ajax', 'formular', ($hideFormular) ? '' : 'triggered'));

echo Ajax::toolbarNavigation($Links, 'right');

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
	<?php $Importer->displayGarminCommunicator($hideGarmin); ?>
</div>

<div class="change" id="formular"<?php if ($hideFormular) echo ' style="display:none;"'; ?>>
	<?php $Importer->displayHTMLformular(); ?>
</div>