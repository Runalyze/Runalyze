<?php
namespace Runalyze\Bundle\CoreBundle\Entity;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class AccountRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.mail = :mail')
            ->setParameter('username', $username)
            ->setParameter('mail', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}