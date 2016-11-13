<?php
/**
 * This file contains class::ShellCommand
 * @package Runalyze\System\Shell
 */
/**
 * ShellCommand
 * 
 * Generel class for all shell commands.
 * To catch stderr as well, ' 2>&1' is always added to the command.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\System\Shell
 */
class ShellCommand {
	/**
	 * Command
	 * @var string
	 */
	protected $command = '';

	/**
	 * New shell command
	 * @param string $command [optional]
	 */
	public function __construct($command = '') {
		$this->setCommand($command);
	}

	/**
	 * Set command
	 * @param string $command
	 */
	public function setCommand($command) {
		$this->command = $command;
	}

	/**
	 * Run command
	 * @return string
	 */
	public function run() {
		return shell_exec( $this->command.' 2>&1' );
	}
}
