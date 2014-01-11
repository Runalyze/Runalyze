<div class="w50" id="login-window">
<?php
FormularInput::setStandardSize( FormularInput::$SIZE_MIDDLE );

$Fieldset = new FormularFieldset('Administration');
$Fieldset->addField( new FormularInput('user', 'Benutzer', 'admin') );
$Fieldset->addField( new FormularInputPassword('password', 'Passwort') );
$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );

$Formular = new Formular();
$Formular->setId('admin-login');
$Formular->addFieldset($Fieldset);
$Formular->addSubmitButton('Login');
$Formular->setSubmitButtonsCentered();
$Formular->display();
?>
</div>