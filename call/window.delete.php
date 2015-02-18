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
			__('<em>A confirmation has been sent via mail.</em><br>'.
				'How sad, that you\'ve decided to delete your account.<br>'.
				'Your account will be deleted as soon as you click on the confirmation link in your mail.')
	);
}