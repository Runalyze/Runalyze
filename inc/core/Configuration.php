<?php

namespace Runalyze;

/**
 * @deprecated since v3.1
 */
class Configuration
{
    /** @var \Runalyze\Configuration\Category[] */
    private static $Categories = array();

    /** @var array|null */
    private static $ValuesFromDB = null;

    /** @var int|null */
    private static $AccountID = null;

    /**
     * @param mixed $accountid
     */
    public static function loadAll($accountid = 'auto')
    {
        if ($accountid === 'auto') {
            self::$AccountID = self::loadAccountID();
        } else {
            self::$AccountID = $accountid;
        }

        self::fetchAllValues();
        self::initAllCategories();
    }

    private static function initAllCategories()
    {
        self::ActivityForm();
        self::ActivityView();
        self::Data();
        self::DataBrowser();
        self::Design();
        self::General();
        self::Privacy();
        self::Trimp();
        self::VO2max();
        self::BasicEndurance();
    }

    /**
     * @param mixed $accountid
     * @throws \InvalidArgumentException
     */
    public static function resetConfiguration($accountid = 'auto')
    {
        if ($accountid !== 'auto' && !is_numeric($accountid)) {
            throw new \InvalidArgumentException('Invalid accountid: '.$accountid);
        }

        if ($accountid === 'auto') {
            if (null === self::$AccountID) {
                throw new \InvalidArgumentException('Configuration does not know any accountid.');
            }

            $accountid = self::$AccountID;
        }

        \DB::getInstance()->exec('DELETE FROM `'.PREFIX.'conf` WHERE `accountid`="'.$accountid.'" AND `category` != "general" AND `category` != "data"');

        self::initAllCategories();
    }

    private static function fetchAllValues()
    {
        self::$Categories = array();

        if (self::$AccountID !== null) {
            self::$ValuesFromDB = \DB::getInstance()->query('SELECT `key`,`value`,`category` FROM '.PREFIX.'conf WHERE `accountid`="'.self::$AccountID.'"')->fetchAll();
        } else {
            self::$ValuesFromDB = array();
        }
    }

    /**
     * @return int
     */
    private static function loadAccountID()
    {
        if (defined('RUNALYZE_TEST'))
            return null;

        return \SessionAccountHandler::getId();
    }

    /**
     * @param string $categoryName
     * @return \Runalyze\Configuration\Category
     */
    private static function get($categoryName)
    {
        if (!isset(self::$Categories[$categoryName])) {
            $className = 'Runalyze\\Configuration\\Category\\'.$categoryName;
            $Category = new $className();
            $Category->setUserID(self::$AccountID, self::$ValuesFromDB);

            self::$Categories[$categoryName] = $Category;
        }

        return self::$Categories[$categoryName];
    }

    /**
     * @return \Runalyze\Configuration\Category\General
     */
    public static function General()
    {
        return self::get('General');
    }

    /**
     * @return \Runalyze\Configuration\Category\ActivityView
     */
    public static function ActivityView()
    {
        return self::get('ActivityView');
    }

    /**
     * @return \Runalyze\Configuration\Category\ActivityForm
     */
    public static function ActivityForm()
    {
        return self::get('ActivityForm');
    }

    /**
     * @return \Runalyze\Configuration\Category\DataBrowser
     */
    public static function DataBrowser()
    {
        return self::get('DataBrowser');
    }

    /**
     * @return \Runalyze\Configuration\Category\Privacy
     */
    public static function Privacy()
    {
        return self::get('Privacy');
    }

    /**
     * @return \Runalyze\Configuration\Category\Design
     */
    public static function Design()
    {
        return self::get('Design');
    }

    /**
     * @return \Runalyze\Configuration\Category\Data
     */
    public static function Data()
    {
        return self::get('Data');
    }

    /**
     * @return \Runalyze\Configuration\Category\VO2max
     */
    public static function VO2max()
    {
        return self::get('VO2max');
    }

    /**
     * @return \Runalyze\Configuration\Category\Trimp
     */
    public static function Trimp()
    {
        return self::get('Trimp');
    }

    /**
     * @return \Runalyze\Configuration\Category\BasicEndurance
     */
    public static function BasicEndurance()
    {
        return self::get('BasicEndurance');
    }

}
