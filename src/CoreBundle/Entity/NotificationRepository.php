<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * @param null|int $timestamp
     * @param Account $account
     * @return Notification[]
     */
    public function findAllSince($timestamp, Account $account)
    {
        $queryBuilder = $this->createQueryBuilder('n')
            ->where('n.account = :account')
            ->setParameter('account', $account);

        if (null !== $timestamp && 0 !== $timestamp) {
            $queryBuilder
                ->andWhere('n.createdAt > :time')
                ->setParameter('time', $timestamp)
                ->orderBy('n.createdAt', 'DESC');
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function markAsRead(Notification $notification)
    {
        // TODO
    }

    public function markAllAsRead(Account $account)
    {
        // TODO
    }
}
