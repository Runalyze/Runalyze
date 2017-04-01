<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SportRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Sport[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }

    /**
     * @param Account $account
     * @return Sport[]
     */
    public function findSportsWithKmFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId(),
            'distances' => true
        ]);
    }

    /**
     * @param Account $account
     * @return array internal sport ids
     */
    public function getUsedInternalSportIdsFor(Account $account)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
            ->select('s.internalSportId')
            ->where('s.account = :account')
            ->andWhere($queryBuilder->expr()->isNotNull('s.internalSportId'))
            ->setParameter('account', $account->getId());

        return $queryBuilder->getQuery()->getResult("COLUMN_HYDRATOR");
    }

    public function save(Sport $sport)
    {
        $this->_em->persist($sport);
        $this->_em->flush();
    }

    public function remove(Sport $sport)
    {
        $this->_em->remove($sport);
        $this->_em->flush();
    }
}
