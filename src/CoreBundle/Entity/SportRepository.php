<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Runalyze\Profile\Sport\Running;
use Runalyze\Profile\Sport\SportProfile;

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
     * @param bool $returnNull
     * @return array|Sport
     */
    public function findRunningFor(Account $account, $returnNull = false)
    {
        $sport = $this->findOneBy([
            'account' => $account->getId(),
            'internalSportId' => SportProfile::RUNNING
        ]);

        if (null === $sport && !$returnNull) {
            $sport = new Sport();
            $sport->setDataFrom(new Running());
        }

        return $sport;
    }

    /**
     * @param Account $account
     * @return Sport[]
     */
    public function findWithDistancesFor(Account $account)
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

    /**
     * @param int $internalTypeId
     * @param Account $account
     * @return bool
     */
    public function isInternalTypeFree($internalTypeId, Account $account)
    {
        return null === $this->findOneBy([
            'account' => $account->getId(),
            'internalSportId' => (int)$internalTypeId
        ]);
    }

    /**
     * @param Account $account
     * @return array
     */
    public function getFreeInternalTypes(Account $account)
    {
        $allTypes = array_flip(SportProfile::getChoices());

        foreach ($this->getUsedInternalSportIdsFor($account) as $usedId) {
            if (isset($allTypes[$usedId])) {
                unset($allTypes[$usedId]);
            }
        }

        if (isset($allTypes[SportProfile::GENERIC])) {
            unset($allTypes[SportProfile::GENERIC]);
        }

        return $allTypes;
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
