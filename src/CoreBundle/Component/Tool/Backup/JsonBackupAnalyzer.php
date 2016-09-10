<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

use Runalyze\Util\File\GZipReader;

class JsonBackupAnalyzer
{
	/** @var string */
	protected $VersionString = '';

    /** @var string */
    protected $RunalyzeVersion = '';

	/** @var array ['table' => #rows] */
	protected $NumberOf = array();

	/**
	 * Construct
	 * @param string $fileName absolute path
     * @param string $runalyzeVersion runalyze version string
	 */
	public function __construct($fileName, $runalyzeVersion)
    {
        $this->RunalyzeVersion = $runalyzeVersion;

		$reader = new GZipReader($fileName);

		while (!$reader->eof()) {
            $line = trim($reader->readLine());

			if (substr($line, 0, 8) == '{"TABLE"') {
				$tableName = substr($line, 10, -2);
				$this->NumberOf[$tableName] = 0;
			} elseif ($line != '' && $line{0} == '{' && isset($this->NumberOf[$tableName])) {
				$this->NumberOf[$tableName]++;
			} elseif (substr($line, 0, 8) == 'version=') {
				$this->VersionString = substr($line, 8);
			}
		}

        $reader->close();
	}

	/**
	 * Count rows for table
	 * @param string $tableName
	 * @return int
	 */
	public function count($tableName)
    {
		if (isset($this->NumberOf[$tableName])) {
			return $this->NumberOf[$tableName];
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	public function fileIsOkay()
    {
		return $this->versionIsOkay() && $this->expectedTablesAreThere();
	}

	/**
	 * @return bool
	 */
	public function versionIsOkay()
    {
		return (self::extractMajorAndMinorVersion($this->RunalyzeVersion) == self::extractMajorAndMinorVersion($this->VersionString));
	}

	/**
	 * @return string
	 */
	public function fileVersion()
    {
		if ($this->VersionString == '') {
			return 'unknown';
		}

		return 'v'.$this->VersionString;
	}

	/**
	 * @return bool
	 */
	protected function expectedTablesAreThere()
    {
		$Expected = $this->expectedTables();
		$Found = array_keys($this->NumberOf);

		return (sort($Expected) == sort($Found));
	}

	/**
	 * @return array error messages
	 */
	public function errors()
    {
		$Expected = $this->expectedTables();
		$Found = array_keys($this->NumberOf);
		$Errors = array();

		foreach (array_diff($Expected, $Found) as $MissingKey) {
			$Errors[] = 'Missing table: '.$MissingKey;
		}

		foreach (array_diff($Found, $Expected) as $AdditionalKey) {
			$Errors[] = 'Additional table: '.$AdditionalKey;
		}

		return $Errors;
	}

	/**
	 * @return array table names with default prefix 'runalyze_'
	 */
	protected function expectedTables()
    {
		return array(
			'runalyze_account',
			'runalyze_conf',
			'runalyze_dataset',
			'runalyze_hrv',
			'runalyze_plugin',
			'runalyze_plugin_conf',
			'runalyze_route',
			'runalyze_sport',
			'runalyze_training',
			'runalyze_trackdata',
			'runalyze_type',
			'runalyze_user',
			'runalyze_equipment_type',
			'runalyze_equipment_sport',
			'runalyze_equipment',
			'runalyze_activity_equipment',
			'runalyze_activity_tag',
			'runalyze_tag',
			'runalyze_raceresult'
		);
	}

	/**
	 * Extract major and minor version, i.e. 'X.Y' of any 'X.Y[.Z][-abc]'
	 * @param string $versionString
	 * @return string
	 */
	public static function extractMajorAndMinorVersion($versionString)
    {
		if (preg_match('/^(\d+\.\d+)/', $versionString, $matches)) {
			return $matches[1];
		}

		return '';
	}
}
