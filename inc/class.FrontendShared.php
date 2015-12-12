<?php
/**
 * This file contains class::FrontendShared
 * @package Runalyze\Frontend
 */

use Runalyze\View\Activity;
use Runalyze\Error;

/**
 * Class for customizing frontend for displaying shared activities
 *
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class FrontendShared extends Frontend {
	/**
	 * @var Runalyze\View\Activity\Context
	 */
	protected $ActivityContext = null;

	/**
	 * Flag: shared view is used
	 * @var boolean
	 */
	public static $IS_SHOWN = false;

	/**
	 * Flag: shared view is iframe
	 * @var boolean
	 */
	public static $IS_IFRAME = false;

	/**
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		self::$IS_SHOWN  = true;
		self::$IS_IFRAME = Request::param('mode') == 'iframe';

		$this->setEncoding();
		$this->initTraining();

		$UserId = (!is_null($this->ActivityContext)) ? SessionAccountHandler::getId() : 0;
		$User   = AccountHandler::getDataForId($UserId);

		if (!Request::isAjax()) {
			if (self::$IS_IFRAME) {
				include 'tpl/tpl.FrontendSharedIframe.header.php';
			} else {
				include 'tpl/tpl.FrontendShared.header.php';
			}
		}

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (!Request::isAjax()) {
			if (self::$IS_IFRAME) {
				include 'tpl/tpl.FrontendSharedIframe.footer.php';
			}

			include 'tpl/tpl.Frontend.footer.php';
		}

		if (RUNALYZE_DEBUG && Error::getInstance()->hasErrors()) {
			Error::getInstance()->display();
		}

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Init training
	 */
	private function initTraining() {
		// TODO: Cache?
		$this->ActivityContext = new Activity\Context(SharedLinker::getTrainingId(), SessionAccountHandler::getId());

		if ($this->ActivityContext->activity()->id() <= 0) {
			$this->ActivityContext = null;
		}
	}

	/**
	 * Display shared view
	 */
	public function displaySharedView() {
		if (is_null($this->ActivityContext)) {
			$this->throwErrorForInvalidRequest();
		} elseif (!$this->ActivityContext->activity()->isPublic()) {
			$this->throwErrorForPrivateTraining();
		} else {
			$this->displayRequestedTraining();
		}
	}

	/**
	 * Display requested training
	 */
	protected function displayRequestedTraining() {
		$_GET['id'] = $this->ActivityContext->activity()->id();

		if (Request::param('mode') == 'iframe') {
			$View = new TrainingViewIFrame($this->ActivityContext);
			$View->display();
		} else {
			$View = new TrainingView($this->ActivityContext);
			$View->display();
		}
	}

	/**
	 * Get page title according to current training
	 * @return string
	 */
	protected function getPageTitle() {
		if (is_null($this->ActivityContext) || !$this->ActivityContext->activity()->isPublic()) {
			return __('Problem');
		}

		return $this->ActivityContext->dataview()->titleWithComment().', '.$this->ActivityContext->dataview()->date();
	}

	/**
	 * Throw error: invalid request
	 */
	protected function throwErrorForInvalidRequest() {
		echo HTML::h1( __('Error') );
		echo HTML::error(
				sprintf('<strong>%s</strong>', __('Invalid request')).'<br><br>'.
				__('We are sorry, your request is not valid.')
		);

		$this->displayLinkToRunalyze();
	}

	/**
	 * Throw error: This training is private
	 */
	protected function throwErrorForPrivateTraining() {
		echo HTML::h1( __('Error') );
		echo HTML::error(
				sprintf('<strong>%s</strong>', __('Private activity')).'<br><br>'.
				__('This activity is private. The user does not allow you to see it.')
		);

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
		Error::init(Request::Uri());
	}

	/**
	 * Init SessionAccountHandler
	 */
	protected function initSessionAccountHandler() {
		// Nothing to do, no session handler needed
	}
}
