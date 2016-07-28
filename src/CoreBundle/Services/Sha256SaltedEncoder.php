<?php
namespace Runalyze\Bundle\CoreBundle\Services;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class Sha256SaltedEncoder implements PasswordEncoderInterface
{
    public function encodePassword($raw, $salt)
    {
        return hash('sha256', trim($raw).$salt); // Custom function for password encrypt
    }
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded === $this->encodePassword($raw, $salt);
    }
}