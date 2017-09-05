<?php

namespace Runalyze\Model\Account;

use Runalyze\Common\Enum\AbstractEnum;

final class UserRole extends AbstractEnum
{
	/** @var int */
	const ROLE_USER = 1;

	/** @var int */
	const ROLE_ADMIN = 2;

	/**
	 * @param int $roleId int from internal enum
	 * @return string role name
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
	 * @return array [int(enum) => 'role name']
	 */
	private static function roleNamesArray()
	{
		return [
			self::ROLE_USER => 'ROLE_USER',
			self::ROLE_ADMIN => 'ROLE_ADMIN',
        ];
	}
}
