<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return User[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ], [
            'time' => 'DESC'
        ]);
    }

    /**
     * @param Account $account
     * @return int|null [bpm]
     */
    public function getCurrentRestingHeartRate(Account $account)
    {
        $result = $this->createQueryBuilder('u')
            ->select('u.pulseRest')
            ->setMaxResults(1)
            ->where('u.account = :accountid')
            ->andWhere('u.pulseRest > 0')
            ->setParameter('accountid', $account->getId())
            ->orderBy('u.time', 'DESC')
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        return $result ? (int)$result['pulseRest'] : null;
    }

    /**
     * @param Account $account
     * @return int|null [bpm]
     */
    public function getCurrentMaximalHeartRate(Account $account)
    {
        $result = $this->createQueryBuilder('u')
            ->select('u.pulseMax')
            ->setMaxResults(1)
            ->where('u.account = :accountid')
            ->andWhere('u.pulseMax > 0')
            ->setParameter('accountid', $account->getId())
            ->orderBy('u.time', 'DESC')
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        return $result ? (int)$result['pulseMax'] : null;
    }

    /**
     * @param User $user
     * @param Account $account
     */
    public function save(User $user, Account $account)
    {
        $this->_em->persist($user);
        $this->_em->flush($user);
    }

    /**
     * @param User $user
     * @param Account $account
     */
    public function remove(User $user, Account $account)
    {
        $this->_em->remove($user);
        $this->_em->flush($user);
    }
}
