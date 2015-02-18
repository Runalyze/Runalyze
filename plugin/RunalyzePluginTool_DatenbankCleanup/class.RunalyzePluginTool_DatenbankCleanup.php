<?php
/**
 * This file contains the class of the RunalyzePluginTool "DatenbankCleanup".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_DatenbankCleanup';
/**
 * Class: RunalyzePluginTool_DatenbankCleanup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_DatenbankCleanup extends PluginTool {
	/**
	 * Job
	 * @var Runalyze\Plugin\Tool\DatabaseCleanup\Job
	 */
	protected $Job = null;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Database cleanup');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Recalculation of some statistics may be needed after deleting some activities. '.
				'In addition, values for elevation, TRIMP and VDOT can be recalculated.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('Due to performance reasons, some statistics are saved in the database. '.
						'Under some circumstances you have to recalculate these values after deleting an activity by hand.') );
	}

	/**
	 * Require files
	 */
	protected function requireFiles() {
		require_once __DIR__.'/Job.php';
		require_once __DIR__.'/JobGeneral.php';
		require_once __DIR__.'/JobLoop.php';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->requireFiles();

		if (isset($_POST['mode'])) {
			$this->setJob();
			$this->runJob();
			$this->displayJob();
		} else {
			$this->displayForm();
		}
	}

	/**
	 * Set job
	 */
	private function setJob() {
		if ($_POST['mode'] == 'general') {
			$this->Job = new Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral();
		} elseif ($_POST['mode'] == 'loop') {
			$this->Job = new Runalyze\Plugin\Tool\DatabaseCleanup\JobLoop();
		}
	}

	/**
	 * Run job
	 */
	private function runJob() {
		$this->Job->run();
	}

	/**
	 * Display job
	 */
	private function displayJob() {
		include __DIR__.'/tpl.Job.php';
	}

	/**
	 * Display form
	 */
	private function displayForm() {
		include __DIR__.'/tpl.ViewForm.php';
	}

	/**
	 * Get job
	 * @return Runalyze\Plugin\Tool\DatabaseCleanup\Job
	 * @throws RuntimeException
	 */
	protected function Job() {
		if (is_null($this->Job)) {
			throw new RuntimeException('There is no current job.');
		}

		return $this->Job;
	}
}