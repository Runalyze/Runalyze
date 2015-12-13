<?php
/**
 * This file contains class::FrontendSharedList
 * @package Runalyze\Frontend
 */

use Runalyze\Configuration;
use Runalyze\Error;

/**
 * Class for customizing frontend for displaying shared activity-lists
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class FrontendSharedList extends FrontendShared {
	/**
	 * UserData
	 * @var array 
	 */
	protected $User = array();

	/**
	 * Display the HTML-Header
	 */
	public function displayHeader() {
		FrontendShared::$IS_SHOWN  = true;

		$this->setEncoding();

		$this->initUser();

		$User = $this->User;

		if (!Request::isAjax())
			include 'tpl/tpl.FrontendShared.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Display the HTML-Footer
	 */
	public function displayFooter() {
		if (!Request::isAjax())
			include 'tpl/tpl.Frontend.footer.php';

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Init user 
	 */
	protected function initUser() {
		$this->User = AccountHandler::getDataFor(Request::param('user'));
	}

	/**
	 * Does the requested user exist?
	 * @return boolean 
	 */
	protected function userExists() {
		return is_array($this->User) && !empty($this->User);
	}

	/**
	 * Does this user allow general statistics?
	 * @return boolean 
	 */
	public function userAllowsStatistics() {
		return Configuration::Privacy()->showStatisticsInList();
	}

	/**
	 * Does this user allow a shared list?
	 * @return boolean 
	 */
	protected function userAllowsList() {
		return Configuration::Privacy()->listIsPublic();
	}

	/**
	 * Init SessionAccountHandler
	 */
	protected function initSessionAccountHandler() {
		SessionAccountHandler::setAccountFromRequest();
	}

	/**
	 * Get ID of this user
	 * @return int 
	 */
	protected function getUserId() {
		if (isset($this->User['id']))
			return $this->User['id'];

		return 0;
	}

	/**
	 * Get ID of this user
	 * @return int 
	 */
	public function getUsername() {
		if (isset($this->User['username']))
			return $this->User['username'];

		return '';
	}

	/**
	 * Get array for user
	 * @return array
	 */
	public function getUser() {
		return $this->User;
	}

	/**
	 * Display shared view 
	 */
	public function displaySharedView() {
		if (!$this->userExists())
			$this->throwErrorForInvalidRequest();
		elseif (!$this->userAllowsList())
			$this->throwErrorForPrivateList();
		else {
			$this->displayRequestedList();
		}
	}

	/**
	 * Display requested training 
	 */
	protected function displayRequestedList() {
		$DataBrowser = new DataBrowserShared();
		$DataBrowser->display();

		// TODO: Diagramme etc?
	}

	/**
	 * Display general statistics
	 */
	public function displayGeneralStatistics() {
		if (!$this->userAllowsStatistics())
			return;

		$Statistics = new FrontendSharedStatistics($this);
		$Statistics->display();
	}

	/**
	 * Get page title
	 * @return string
	 */
	protected function getPageTitle() {
		if (!$this->userExists() || !$this->userAllowsList())
			return __('Problem');

		return sprintf( __('Activity view of %s'), $this->User['username'] );
	}

	/**
	 * Throw error: This training is private 
	 */
	protected function throwErrorForPrivateList() {
		echo HTML::h1(__('Error'));
		echo HTML::error(
				sprintf('<strong>%s</strong>', __('This list is private')).'<br><br>'.
				__('The user does not share his activity list.')
		);

		$this->displayLinkToRunalyze();
	}
}