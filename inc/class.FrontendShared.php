<?php
/**
 * Class for customizing frontend for displaying shared activities
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class FrontendShared extends Frontend {
	/**
	 * Training to be display in shared view
	 * @var Training
	 */
	protected $Training = null;

	/**
	 * Flag: shared view is used
	 * @var boolean
	 */
	static public $IS_SHOWN = false;

	/**
	 * Flag: shared view is iframe
	 * @var boolean
	 */
	static public $IS_IFRAME = false;

	/**
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		self::$IS_SHOWN  = true;
		self::$IS_IFRAME = Request::param('mode') == 'iframe';

		$this->setEncoding();
		$this->initTraining();

		$UserId = (!is_null($this->Training)) ? $this->Training->get('accountid') : 0;
		$User   = AccountHandler::getDataForId($UserId);

		if (self::$IS_IFRAME)
			include 'tpl/tpl.FrontendSharedIframe.header.php';
		else
			include 'tpl/tpl.FrontendShared.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (self::$IS_IFRAME)
			include 'tpl/tpl.FrontendSharedIframe.footer.php';

		include 'tpl/tpl.Frontend.footer.php';

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Init training 
	 */
	private function initTraining() {
		$id = SharedLinker::getTrainingId();

		if ($id <= 0)
			return;

		$this->Training = new Training($id);
	}

	/**
	 * Display shared view 
	 */
	public function displaySharedView() {
		if (is_null($this->Training) || !$this->Training->isValid())
			$this->throwErrorForUnknownTraining();
		elseif (!$this->Training->isPublic())
			$this->throwErrorForPrivateTraining();
		else
			$this->displayRequestedTraining();
	}

	/**
	 * Display requested training 
	 */
	protected function displayRequestedTraining() {
		$_GET['id'] = $this->Training->id();

		if (Request::param('mode') == 'iframe')
			$this->Training->displayAsIframe();
		else
			$this->Training->display();
	}

	/**
	 * Get page title according to current training 
	 * @return string
	 */
	protected function getPageTitle() {
		if (is_null($this->Training) || !$this->Training->isValid() || !$this->Training->isPublic())
			return 'Problem';

		return $this->Training->getTitle().' am '.$this->Training->getDate(false).' - Trainingsansicht';
	}

	/**
	 * Throw error: invalid request 
	 */
	protected function throwErrorForUnknownTraining() {
		echo HTML::h1('Fehler');
		echo HTML::error('
			<strong>Invalid request.</strong><br />
			<br />
			Wir wissen mit deiner Anfrage nichts anzufangen ;-)');

		$this->displayLinkToRunalyze();
	}

	/**
	 * Throw error: This training is private 
	 */
	protected function throwErrorForPrivateTraining() {
		echo HTML::h1('Fehler');
		echo HTML::error('
			<strong>Dieses Training ist privat.</strong><br />
			<br />
			Jeder Benutzer von Runalyze kann selbst bestimmen,
			welche Trainings er anderen zeigt und welche nicht.');

		$this->displayLinkToRunalyze();
	}

	/**
	 * Display link to www.runalyze.de 
	 */
	protected function displayLinkToRunalyze() {
		$List = new BlocklinkList();
		$List->addCompleteLink('<a class="nopadding" href="http://www.runalyze.de/" title="Runalyze - Online Lauftagebuch"><strong>&raquo;&nbsp;runalyze.de</strong></a>');
		$List->display();
	}

	/**
	 * Overwrite init of error-handling to use logfiles
	 */
	protected function initErrorHandling() {
		Error::init(Request::Uri(), true);
	}

	/**
	 * Init SessionAccountHandler
	 */
	protected function initSessionAccountHandler() {
		// Nothing to do, no session handler needed
	}
}