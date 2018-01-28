<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Runalyze\Bundle\CoreBundle\Model\Sport\SportStatistics;
use Runalyze\Profile\Sport\Running;
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\Util\LocalTime;

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
     * @param int $internalTypeId
     * @param Account $account
     * @return null|Sport
     */
    public function findInternalIdFor($internalTypeId, Account $account)
    {
        return $this->findOneBy([
            'account' => $account->getId(),
            'internalSportId' => (int)$internalTypeId
        ]);
    }

    /**
     * @param Account $account
     * @param bool $returnNull
     * @return null|Sport
     */
    public function findRunningFor(Account $account, $returnNull = false)
    {
        $sport = $this->findInternalIdFor(SportProfile::RUNNING, $account);

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
     * @param int $sportId
     * @param Account $account
     * @return null|Sport|object
     */
    public function findThisOrAny($sportId, Account $account)
    {
        /** @var null|Sport $requestedSport */
        $requestedSport = $this->find($sportId);

        if (null !== $requestedSport && $requestedSport->getAccount()->getId() != $account->getId()) {
            $requestedSport = null;
        }

        if (null === $requestedSport) {
            $results = $this->findBy(
                ['account' => $account->getId()],
                null,
                1
            );

            if (is_array($results) && count($results) == 1) {
                return $results[0];
            }
        }

        return $requestedSport;
    }

    public function findEquipmentCategoryIdsFor(array $sportIds)
    {
        // TODO: this query results in two joins with one of them being useless
        // SELECT r0_.id AS id_0, r1_.id AS id_1 FROM runalyze_sport r0_ INNER JOIN runalyze_equipment_sport r2_ ON r0_.id = r2_.sportid INNER JOIN runalyze_equipment_type r1_ ON r1_.id = r2_.equipment_typeid WHERE r0_.id IN (?)

        return $this->createQueryBuilder('s')
            ->innerJoin('s.equipmentType', 'e')
            ->select('s.id as sport_id, e.id as equipment_type_id')
            ->where('s.id IN(:sports)')
            ->setParameter('sports', $sportIds)
            ->getQuery()
            ->getResult();
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
        return null === $this->findInternalIdFor((int)$internalTypeId, $account);
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

    /**
     * @param int|null $timestamp
     * @param Account $account
     * @param bool $raw if enabled, raw array data is returned
     * @return SportStatistics|array
     */
    public function getSportStatisticsSince($timestamp, Account $account, $raw = false)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('s')
            ->addSelect('COUNT(t.id) as num')
            ->addSelect('SUM(t.distance) as distance')
            ->addSelect('SUM(t.s) as time_in_s')
            ->addSelect('SUM(CASE WHEN t.distance > 0 THEN 1 ELSE 0 END) as count_distance')
            ->from('CoreBundle:Sport', 's')
            ->innerJoin('s.trainings', 't','WITH', 't.account = :account')
            ->where('s.account = :account')
            ->setParameter(':account', $account->getId())
            ->groupBy('s.id')
            ->orderBy('distance', 'DESC')
            ->addOrderBy('time_in_s', 'DESC');

        if (null !== $timestamp) {
            $queryBuilder->andWhere('t.time > :startTime');
            $queryBuilder->setParameter(':startTime', $timestamp);
        }

        if ($raw) {
            return $queryBuilder->getQuery()->getResult();
        }

        return new SportStatistics((new LocalTime($timestamp))->toServerTime(), $queryBuilder->getQuery()->getResult());
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
