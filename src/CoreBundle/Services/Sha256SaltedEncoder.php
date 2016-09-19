<?php

namespace Runalyze\Bundle\CoreBundle\Services;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class Sha256SaltedEncoder implements PasswordEncoderInterface
{
    /**
     * @param string $raw
     * @param string $salt
     * @return string
     */
    public function encodePassword($raw, $salt)
    {
        return hash('sha256', trim($raw).$salt); // Custom function for password encrypt
    }

    /**
     * @param string $encoded
     * @param string $raw
     * @param string $salt
     * @return bool
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded === $this->encodePassword($raw, $salt);
    }
}
