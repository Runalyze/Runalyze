<?php
/**
 * This file contains class::FrontendShared
 * @package Runalyze\Frontend
 */
/**
 * Class for customizing frontend for displaying shared activities
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class FrontendShared extends Frontend {
	/**
	 * Training object
	 * @var TrainingObject
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

		if (!Request::isAjax()) {
			if (self::$IS_IFRAME)
				include 'tpl/tpl.FrontendSharedIframe.header.php';
			else
				include 'tpl/tpl.FrontendShared.header.php';
		}

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (!Request::isAjax()) {
			if (self::$IS_IFRAME)
				include 'tpl/tpl.FrontendSharedIframe.footer.php';

			include 'tpl/tpl.Frontend.footer.php';
		}

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Init training 
	 */
	private function initTraining() {
		$data = DB::getInstance()->fetchByID('training', SharedLinker::getTrainingId());

		if ($data)
			$this->Training = new TrainingObject( $data );
	}

	/**
	 * Display shared view 
	 */
	public function displaySharedView() {
		if (is_null($this->Training) || $this->Training->isDefaultId())
			$this->throwErrorForInvalidRequest();
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

		if (Request::param('mode') == 'iframe') {
			$View = new TrainingViewIFrame($this->Training);
			$View->display();
		} else {
			$View = new TrainingView($this->Training);
			$View->display();
		}
	}

	/**
	 * Get page title according to current training 
	 * @return string
	 */
	protected function getPageTitle() {
		if (is_null($this->Training) || $this->Training->isDefaultId() || !$this->Training->isPublic())
			return __('Problem');

		return $this->Training->DataView()->getTitle().' am '.$this->Training->DataView()->getDate(false).' - Trainingsansicht';
	}

	/**
	 * Throw error: invalid request 
	 */
	protected function throwErrorForInvalidRequest() {
		echo HTML::h1(__('Error'));
		echo HTML::error('
			<strong>Invalid request.</strong><br>
			<br>
			Wir wissen mit deiner Anfrage nichts anzufangen ;-)');

		$this->displayLinkToRunalyze();
	}

	/**
	 * Throw error: This training is private 
	 */
	protected function throwErrorForPrivateTraining() {
		echo HTML::h1(__('Error'));
		echo HTML::error('
			<strong>Dieses Training ist privat.</strong><br>
			<br>
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