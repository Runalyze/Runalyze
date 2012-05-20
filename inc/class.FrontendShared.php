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
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		$this->setEncoding();
		$this->initTraining();

		include 'tpl/tpl.FrontendShared.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		include 'tpl/tpl.Frontend.footer.php';

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Init training 
	 */
	private function initTraining() {
		$this->Training = new Training ( SharedLinker::getTrainingId() );
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
	 * Init SessionHandler
	 */
	protected function initSessionHandler() {
		// Nothing to do, no session handler needed
	}
}