<?php

namespace Runalyze\Model\Account;

class UserRoleTest extends \PHPUnit_Framework_TestCase
{

	public function testCheckRoleName()
	{
        $this->assertEquals( 'ROLE_USER', UserRole::getRoleName(1));
        $this->assertEquals( 'ROLE_ADMIN', UserRole::getRoleName(2));
	}
}
