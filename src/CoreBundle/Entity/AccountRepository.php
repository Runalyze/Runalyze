<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class AccountRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * @param string $username username or mail
     * @return null|Account
     */
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.mail = :mail')
            ->setParameter('username', $username)
            ->setParameter('mail', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param bool $cache
     * @return mixed number of accounts
     */
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

    /**
     * @param int $days
     * @return array
     */
    public function deleteNotActivatedAccounts($days = 7)
    {
        $minimumAge = time() - $days * 86400;

        return $this->createQueryBuilder('u')
            ->delete()
            ->where('u.activationHash IS NOT NULL AND u.registerdate < :minimumAge')
            ->setParameter('minimumAge', $minimumAge)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $criteria
     * @return bool
     */
    public function existsOneWith(array $criteria)
    {
        return null !== $this->findOneBy($criteria);
    }

    /**
     * @param string $deletionHash
     * @return bool true on success
     */
    public function deleteByHash($deletionHash)
    {
        /** @var null|Account $account */
        $account = $this->findOneBy([
            'deletionHash' => $deletionHash
        ]);

        if (null !== $account) {
            $this->_em->remove($account);
            $this->_em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param string $activationHash
     * @return bool true on success
     */
    public function activateByHash($activationHash)
    {
        /** @var null|Account $account */
        $account = $this->findOneBy([
            'activationHash' => $activationHash
        ]);

        if (null !== $account) {
            $this->save($account->removeActivationHash());

            return true;
        }

        return false;
    }

    public function save(Account $account)
    {
        $this->_em->persist($account);
        $this->_em->flush();
    }
}
