<?php

require_once FRONTEND_PATH.'../plugin/RunalyzePluginTool_DbBackup/class.RunalyzeBackup.php';

class RunalyzeBackup_MockTester extends RunalyzeBackup
{
	protected function saveTableStructure($TableName) {}
	protected function saveRowsFromStatement(&$TableName, array $ColumnInfo, PDOStatement $Statement) {}
}

class RunalyzeBackupTest extends PHPUnit_Framework_TestCase
{
	const EMPTY_BACKUP_FILE = '../tests/testfiles/empty-backup.json.gz';

	public function tearDown()
	{
		if (file_exists(FRONTEND_PATH.self::EMPTY_BACKUP_FILE)) {
			unlink(FRONTEND_PATH.self::EMPTY_BACKUP_FILE);
		}
	}

	public function testOnlyQueries()
	{
		$Backup = new RunalyzeBackup_MockTester(self::EMPTY_BACKUP_FILE);
		$Backup->run();
	}

}