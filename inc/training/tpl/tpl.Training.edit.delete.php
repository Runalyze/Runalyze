<?php
$DeleteText = '<strong>Training unwiderruflich l&ouml;schen &raquo;</strong>';
$DeleteUrl  = $_SERVER['SCRIPT_NAME'].'?delete='.$id;
$DeleteLink = Ajax::link($DeleteText, 'ajax', $DeleteUrl);

$Fieldset   = new FormularFieldset();
$Fieldset->setTitle('Training l&ouml;schen');
$Fieldset->setId('delete_training');
$Fieldset->addWarning($DeleteLink);
$Fieldset->setCollapsed();
$Fieldset->display();
?>