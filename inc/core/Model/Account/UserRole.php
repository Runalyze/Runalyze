<?php
/**
 * This file contains class::UserRole
 * @package Runalyze
 */

namespace Runalyze\Model\Account;

use Runalyze\Util\AbstractEnum;

/**
 * Enum for User Roles
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Model\Account
 */
final class UserRole extends AbstractEnum
{
	/** @var int */
	const ROLE_USER = 1;
	
	/** @var int */
	const ROLE_ADMIN = 2;
	
	/**
	 * Get sharer
	 * @param int $roleId int from internal enum
	 * @return string Rolename
	 * @throws \InvalidArgumentException
	 */
	public static function getRoleName($roleId)
	{
		$roleNames = self::roleNamesArray();
		if (!isset($roleNames[$roleId])) {
			throw new \InvalidArgumentException('Invalid type id "'.$roleId.'".');
		}
		return $roleNames[$roleId];
	}
	
	/**
	 * Get array with role names
	 * @return array
	 */
	private static function roleNamesArray()
	{
		return array(
			self::ROLE_USER => 'ROLE_USER',
			self::ROLE_ADMIN => 'ROLE_ADMIN',
		);
	}

}