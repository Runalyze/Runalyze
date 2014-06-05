<?php
/**
* Delete Account
 * Call:   call/window.delete.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();
$Errors   = array();
AccountHandler::setAndSendDeletionKeyFor($Errors);

echo HTML::h1( __('Delete your account.') );

if (!empty($Errors)) {
	foreach ($Errors as $Error)
		echo HTML::error($Error);
} else {
	echo HTML::info(
			__('<em>A confirmation mail has been sent.</em><br>'.
				'How sad, that you decided to delete your account.<br>'.
				'Your account will be deleted as soon as you click the confirmation link in your mail.')
	);
}