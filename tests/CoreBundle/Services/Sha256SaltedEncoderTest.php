<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services;

use Runalyze\Bundle\CoreBundle\Services\Sha256SaltedEncoder;

class Sha256SaltedEncoderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Sha256SaltedEncoder */
    protected $Encoder;

    public function setUp()
    {
        $this->Encoder = new Sha256SaltedEncoder();
    }

    public function testValidation()
    {
        $this->assertTrue(
            $this->Encoder->isPasswordValid(
                $this->Encoder->encodePassword('Th1$PassSwordIsSoDamnGreat!', 'Salt and pepper ...'),
                'Th1$PassSwordIsSoDamnGreat!', 'Salt and pepper ...'
            )
        );
    }

    public function testValidationOfWrongPassword()
    {
        $this->assertFalse(
            $this->Encoder->isPasswordValid(
                $this->Encoder->encodePassword('Th1$PassSwordIsSoDamnGreat!', 'A password is no sword.'),
                'Th1$PasSwordIsSoDamnGreat!', 'A password is no sword.'
            )
        );
    }
}
