<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * @param Account|null $account
     * @return Notification[]
     */
    public function findAll(Account $account = null)
    {
        if (null === $account) {
            throw new \LogicException('Finding all notifications is only possible for a specific account.');
        }

        return $this->findAllSince(null, $account);
    }

    /**
     * @param null|int $timestamp
     * @param Account $account
     * @return Notification[]
     */
    public function findAllSince($timestamp, Account $account)
    {
        $queryBuilder = $this->createQueryBuilder('n')
            ->where('n.account = :account')
            ->setParameter('account', $account)
            ->orderBy('n.createdAt', 'DESC');

        if (null !== $timestamp && 0 !== $timestamp) {
            $queryBuilder
                ->andWhere('n.wasRead = 0') // Only necessary as long as headline menu is filled via request with $timestamp = 1
                ->andWhere('n.createdAt > :time')
                ->setParameter('time', $timestamp)
                ->orderBy('n.createdAt', 'ASC');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function markAsRead(Notification $notification)
    {
        $this->save($notification->setRead());
    }

    public function markAllAsRead(Account $account)
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.wasRead', true)
            ->where('n.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->execute();

        $this->_em->clear(Notification::class);
    }

    /**
     * @return int number of removed notifications
     */
    public function removeExpiredNotifications()
    {
        $numDeleted = $this->createQueryBuilder('n')
            ->delete()
            ->where('n.expirationAt < :time')
            ->setParameter('time', time())
            ->getQuery()
            ->execute();

        $this->_em->clear(Notification::class);

        return $numDeleted;
    }

    public function save(Notification $notification)
    {
        $this->_em->persist($notification);
        $this->_em->flush();
    }
}
