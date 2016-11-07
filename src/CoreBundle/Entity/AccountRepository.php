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
    
    public function getAmountOfActivatedUsers($cache = true)
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.activationHash is NULL')
            ->getQuery()
            ->useResultCache($cache)
            ->setResultCacheLifetime(320)
            ->getSingleScalarResult();
    }

    public function deleteNotActivatedAccounts($days = 7) {
        $minimumAge = (new \DateTime())->getTimestamp()-$days*86400;

        return $this->createQueryBuilder('u')
            ->delete()
            ->where('u.activationHash IS NOT NULL AND u.registerdate < :minimumAge')
            ->setParameter('minimumAge', $minimumAge)
            ->getQuery()
            ->getArrayResult();
    }

}