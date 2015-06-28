<?php
/**
 * Script to refactor equipment
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
$hostname = '';
$database = '';
$username = '';
$password = '';

define('PREFIX', 'runalyze_');
define('LIMIT', 100); // Limit number of activities to refactor per request
define('CLI', false); // Set to true if running from command line
define('SET_GLOBAL_PROPERTIES', false); // Set to true to set max_allowed_packet and key_buffer_size for mysql

// Uncomment these lines to unset time/memory limits
#@ini_set('memory_limit', '-1');
#if (!ini_get('safe_mode')) { @set_time_limit(0); }



/*******************************************************************************
 * SCRIPT STARTS - YOU DON'T NEED TO CHANGE ANYTHING BELOW
 ******************************************************************************/

$starttime = microtime(true);

/**
 * Protect script
 */
define('NL', CLI ? PHP_EOL : '<br>'.PHP_EOL);

if (empty($database) && empty($hostname)) {
	echo 'Database connection has to be set within the file.'.NL;
	exit;
} else {
	date_default_timezone_set('Europe/Berlin');

	try {
		$PDO = new PDO('mysql:dbname='.$database.';host='.$hostname, $username, $password);
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if (SET_GLOBAL_PROPERTIES) {
			$PDO->exec('SET GLOBAL max_allowed_packet=1073741824;');
			$PDO->exec('SET GLOBAL key_buffer_size=1073741824;');
		}
	} catch (Exception $E) {
		echo 'Database connection failed: '.$E->getMessage().NL;
		exit;
	}
}

/**
 * Check version
 */
$IsNotRefactored = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'shoe"');

if (!$IsNotRefactored) {
	echo 'The database is already refactored.'.NL;
	exit;
}


/**
 * Overview for data
 */
$HasTableEq = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'equipment"');
$HasTableEqT = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'equipment_type"');
$HasTableEqS = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'equipment_sport"');
$HasTableEqAE = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'activity_equipment"');

if (!$HasTableEq OR !$HasTableEqT OR !$HasTableEqS OR !$HasTableEqAE) {
	echo 'Cannot find equipment tables. Please run latest sql updates.';
	exit;
} else {
    $countAccount = $PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'account`')->fetchColumn();
}

echo NL;

/**
 * Refactor Shoe table to equipment
 */
    $HasColumnAccount = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'account` LIKE "refactored"')->fetch();
    if(!$HasColumnAccount) {
        $PDO->exec('ALTER TABLE `'.PREFIX.'account` ADD `refactored` TINYINT NOT NULL AFTER `id`');
        echo 'Added column \'refactored\' to table account.'.NL;     
    }


$count = $PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'account` WHERE `refactored`=1')->fetchColumn();
if ($count < $countAccount) {
    $accounts = $PDO->query('SELECT `id` FROM `'.PREFIX.'account` WHERE `refactored`=0 LIMIT '.LIMIT);
    print_r($accounts);
    while ($Row = $accounts->fetch()) {
        $InsertShoeinType = $PDO->prepare('INSERT INTO runalyze_equipment_type (`name`, `input`, `max_km`, `accountid`) VALUES (:name, 0, 1000, :accountid)');
        $InsertShoeinType->execute(array(
            ':name' => 'Schuhe',
            ':accountid' => $Row['id']
        ));
        $ShoeTypeId = $PDO->lastInsertId();
        
    $shoetable = $PDO->query('SELECT `id`, `name`, `since`, `weight`, `km`, `time`, `additionalKm`, `inuse`  FROM `'.PREFIX.'shoe` WHERE `accountid`='.$Row['id']);    
    while ($shoe = $shoetable->fetch()) {    
        $InsertShoeinEqp = $PDO->prepare('INSERT INTO runalyze_equipment_type (`name`, `input`, `max_km`, `accountid`) VALUES (:name, 0, 1000, :accountid)');
        $InsertShoeinEqp->execute(array(
            ':name' => $shoe['name'],
            ':typeid' => $ShoeTypeId,
            ':accountid' => $Row['id'],
            ':notes' => $Row['weight'],
            ':distance' => $Row['km'],
            ':time' => $Row['time'],
            ':additional_km' => $Row['additionalKm'],  
            ':date_start' => $Row['since'],
            ':date_end' => $Row
        ));     
        
    }
    
    
    
    /*
    $HasTableShoe = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'shoe"');
    if($HasTableShoe) {
        $HasColumnShoe = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'shoe` LIKE "refactored"')->fetch();
        if(!$HasColumnShoe) {
           $PDO->exec('ALTER TABLE `'.PREFIX.'shoe` ADD `refactored` TINYINT NOT NULL AFTER `id`');
           echo 'Added column \'refactored\' to table shoe.'.NL;
        }

        $count = $PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'shoe` WHERE `refactored`=1')->fetchColumn();


    }*/

    /**
     * Refactor Clothes table to equipment
     */
    /*$HasTableClothes = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'clothes"');

    if($HasTableClothes) {
        $HasColumnShoe = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'clothes` LIKE "refactored"')->fetch();
        if(!$HasColumnShoe) {
           $PDO->exec('ALTER TABLE `'.PREFIX.'clothes` ADD `refactored` TINYINT NOT NULL AFTER `id`');
           echo 'Added column \'refactored\' to table shoe.'.NL;
        }
    }*/


}


/*
echo 'Table '.PREFIX.'training has column \'refactored\'.'.NL;
echo ' - refactored: '.$count.'/'.$tables[PREFIX.'training'].NL;
echo NL;

if ($count < $tables[PREFIX.'training']) {


	echo 'done;'.NL;
	echo NL;
	echo 'Time: '.(microtime(true) - $starttime).'s'.NL;
	echo 'Memory peak: '.memory_get_peak_usage().'B'.NL;
	echo NL;

	if (CLI) {
		echo '... call the script again to continue'.NL;
	} else {
		echo '... <a href="javascript:location.reload()">reload to continue</a>';
	}
}


if ($count + LIMIT >= $tables[PREFIX.'training']) {
	echo 'You are done. All rows are refactored.'.NL;

	$PDO->exec('DROP TABLE IF EXISTS `'.PREFIX.'shoe`, `'.PREFIX.'clothes`');
	echo 'All unused tables (shoe, clothe) have been dropped.'.NL;

	echo NL;
	echo 'Remember to unset your credentials within this file.'.NL;
	echo '(Or simply delete this file if you are not working on our git repository)'.NL;
}
*/
function notNull($value) {
	if (is_null($value))
		return '';

	return $value;
}