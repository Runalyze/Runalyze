<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Account
 */

namespace Runalyze\Model\Account;

use Runalyze\Model;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Account entity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\HRV
 */
class Entity extends Model\EntityWithID implements Model\Loopable, UserInterface, \Serializable {
	/**
	 * Key: username
	 * @var string
	 */
	const USERNAME = 'username';
	
	/**
	 * Key: name
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * Key: mail
	 * @var string
	 */
	const MAIL = 'mail';

	/**
	 * Key: language
	 * @var string
	 */
	const LANGUAGE = 'language';
	

	/**
	 * Key: password
	 * @var string
	 */
	const PASSWORD = 'password';
	
	/**
	 * Key: salt
	 * @var string
	 */
	const SALT = 'salt';
	
	/**
	 * All properties
	 * @return array
	 */
	public static function allProperties() {
		return array(
			self::USERNAME,
			self::NAME,
			self::MAIL,
			self::PASSWORD,
			self::SALT
		);
	}

	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allProperties();
	}

	/**
	 * Is the property an array?
	 * @param string $key
	 * @return bool
	 */
	public function isArray($key) {
		return ($key == self::DATA);
	}

	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
			case self::DATA:
				return true;
		}

		return false;
	}

	/**
	 * Value at
	 * 
	 * Remark: This method may throw index offsets.
	 * @param int $index
	 * @param int $key string
	 * @return mixed
	 */
	public function at($index, $key) {
		return $this->Data[$key][$index];
	}

	/**
	 * Get activitiy id
	 * @return int
	 */
	public function activityID() {
		return $this->Data[self::ACTIVITYID];
	}

	/**
	 * Data
	 * @return array unit: [ms]
	 */
	public function data() {
		return $this->Data[self::DATA];
	}
	
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            $this->salt,
        ));
    }
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->Data[self::USERNAME],
            $this->Data[self::PASSWORD],
            // see section on salt below
            $this->Data[self::SALT]
        ) = unserialize($serialized);
    }
}