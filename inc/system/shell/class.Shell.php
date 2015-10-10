<?php
/**
 * This file contains class::Shell
 * @package Runalyze\System\Shell
 */
/**
 * Shell
 * 
 * Class for executing shell commands
 * 
 * @author Hannes Christiansen
 * @package Runalyze\System\Shell
 */
class Shell {
	/**
	 * Command
	 * @var ShellCommand
	 */
	protected $Command = null;

	/**
	 * Output
	 * @var string
	 */
	protected $Output = '';

	/**
	 * Single line
	 * @var string
	 */
	protected $Line = '';

	/**
	 * Run command
	 * @param ShellCommand $Command
	 */
	public function runCommand(ShellCommand $Command) {
		$this->Output = $Command->run();
	}

	/**
	 * Get output
	 * @return string
	 */
	public function getOutput() {
		return trim($this->Output);
	}

	/**
	 * Jump to next line
	 * 
	 * Can be used to iterate over all lines<br>
	 * <code>
	 * while ($Shell->nextLine())
	 *   $Line = $Shell->getLine();
	 * </code>
	 * 
	 * @return boolean
	 */
	public function nextLine() {
		$this->Line = strtok($this->Output, "\r\n");

		return $this->Line !== false;
	}

	/**
	 * Get one line
	 * 
	 * @see Shell::nextLine()
	 * @return string
	 */
	public function getLine() {
		return $this->Line;
	}

	/**
	 * Is Perl available?
	 * 
	 * Tries to run a testscript and returns true if succeeded.
	 * @return boolean
	 */
	public static function isPerlAvailable() {
		try {
			$Command = new PerlCommand();
			$Command->setScript('test.pl', '');

			$Shell = new Shell();
			$Shell->runCommand($Command);

			return ($Shell->getOutput() == 'success');
		} catch (Exception $Exception) {
			return false;
		}
	}
}