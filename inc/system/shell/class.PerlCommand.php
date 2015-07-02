<?php
/**
 * This file contains class::PerlCommand
 * @package Runalyze\System\Shell
 */
/**
 * PerlCommand
 * 
 * Generel class for all perl commands.
 * Path to perl binary is set automatically.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\System\Shell
 */
class PerlCommand extends ShellCommand {
	/**
	 * Path to perl executable
	 * @var string
	 */
	private static $PERL_PATH = '/usr/bin/perl';

	/**
	 * Path to perl scripts
	 * 
	 * Relative to FRONTEND_PATH
	 * @var string
	 */
	private static $PERL_PATH_SCRIPTS = '../call/perl/';

	/**
	 * Script name
	 * @var string
	 */
	protected $script = '';

	/**
	 * Arguments
	 * @var string
	 */
	protected $args = '';

	/**
	 * New shell command
	 * @param string $command [optional]
	 */
	public function __construct($command = '') {
		if ($command != '')
			$this->setCommand($command);
	}

	/**
	 * Set script name
	 * @param string $script name of perl script, must be located in self::$PERL_PATH_SCRIPTS
	 * @param string $args arguments
	 */
	public function setScript($script, $args) {
		$this->script = $script;
		$this->args = $args;
	}

	/**
	 * Set command
	 * @param string $command
	 */
	public function setCommand($command) {
		$this->command = self::$PERL_PATH.' '.$command;
	}

	/**
	 * Run command
	 * @return string
	 */
	public function run() {
		if (empty($this->command))
			$this->setCommand(FRONTEND_PATH.self::$PERL_PATH_SCRIPTS.$this->script.' '.$this->args);

		return shell_exec( $this->command.' 2>&1' );
	}
}