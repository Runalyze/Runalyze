<?php
/**
 * Class for customizing frontend for displaying shared activity-lists
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class FrontendSharedList extends FrontendShared {
	/**
	 * UserData
	 * @var array 
	 */
	protected $User = array();

	/**
	 * Function to display the HTML-Header
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
	 * Function to display the HTML-Footer
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
	 * Does this user allow a shared list?
	 * @return boolean 
	 */
	protected function userAllowsList() {
		return CONF_TRAINING_LIST_PUBLIC;
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
	 * Display shared view 
	 */
	public function displaySharedView() {
		if (!$this->userExists())
			$this->throwErrorForInvalidRequest();
		elseif (!$this->userAllowsList())
			$this->throwErrorForPrivateList();
		else
			$this->displayRequestedList();
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
	 * Get page title
	 * @return string
	 */
	protected function getPageTitle() {
		if (!$this->userExists() || !$this->userAllowsList())
			return 'Problem';

		return 'Trainingsansicht von '.$this->User['username'];
	}

	/**
	 * Throw error: This training is private 
	 */
	protected function throwErrorForPrivateList() {
		echo HTML::h1('Fehler');
		echo HTML::error('
			<strong>Diese Trainingsliste ist privat.</strong><br />
			<br />
			Jeder Benutzer von Runalyze kann selbst bestimmen,
			ob er seine Trainings ver&ouml;ffentlicht oder nicht.');

		$this->displayLinkToRunalyze();
	}
}