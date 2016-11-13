<div class="w50" id="login-window">
<?php
FormularInput::setStandardSize( FormularInput::$SIZE_MIDDLE );

$Fieldset = new FormularFieldset( __('Administration') );
$Fieldset->addField( new FormularInput('user', __('Account'), 'admin') );
$Fieldset->addField( new FormularInputPassword('password', __('Password')) );
$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );

$Formular = new Formular();
$Formular->setId('admin-login');
$Formular->addFieldset($Fieldset);
$Formular->addSubmitButton( __('Login') );
$Formular->setSubmitButtonsCentered();
$Formular->display();
?>
</div>
