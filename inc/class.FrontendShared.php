<?php

use Runalyze\Error;

/**
 * @deprecated since v3.1
 */
class FrontendShared extends Frontend {
	/**
	 * Flag: shared view is used
	 * @var boolean
	 */
	public static $IS_SHOWN = false;

	/**
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		self::$IS_SHOWN  = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (Error::getInstance()->hasErrors()) {
			Error::getInstance()->display();
		}

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Init SessionAccountHandler
	 */
	protected function initSessionAccountHandler() {
		// Nothing to do, no session handler needed
	}
}
