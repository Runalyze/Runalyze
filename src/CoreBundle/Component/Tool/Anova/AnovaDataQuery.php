<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup\QueryGroupInterface;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup\QueryGroups;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue\QueryValueInterface;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue\QueryValues;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;
use Runalyze\Metrics\Common\UnitInterface;

class AnovaDataQuery
{
    /** @var AnovaData */
    protected $AnovaData;

    /** @var QueryGroupInterface */
    protected $QueryGroup;

    /** @var QueryValueInterface */
    protected $QueryValue;

    /** @var array */
    protected $Groups = [];

    public function __construct(AnovaData $anovaData)
    {
        $this->AnovaData = $anovaData;
        $this->QueryGroup = QueryGroups::getGroup($anovaData->getValueToGroupBy());
        $this->QueryValue = QueryValues::get($anovaData->getValueToLookAt());
    }

    public function loadAllGroups(EntityManager $entityManager, Account $account)
    {
        $this->Groups = [];
        $groups = $this->QueryGroup->loadAllGroups($entityManager, $account, $this->AnovaData);

        foreach ($groups as $id => $label) {
            $this->Groups[(int)$id] = [
                'label' => $label,
                'data' => []
            ];
        }
    }

    /**
     * @return UnitInterface
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        $sports = $this->AnovaData->getSport();

        if (1 == count($sports)) {
            $unitSystem->setPaceUnitFromSport(array_shift($sports));
        }

        return $this->QueryValue->getValueUnit($unitSystem);
    }

    /**
     * @return array
     */
    public function getResults(TrainingRepository $trainingRepository, Account $account, UnitSystem $unitSystem)
    {
        $unit = $this->getValueUnit($unitSystem);
        $iterator = $this->buildQuery($trainingRepository, $account)->iterate(null, AbstractQuery::HYDRATE_ARRAY);

        foreach ($iterator as $row) {
            $data = array_shift($row);

            $this->Groups[(int)$data['grouping']]['data'][] = $unit->fromBaseUnit((float)$data['value']);
        }

        $this->filterEmptyGroups();

        return array_values($this->Groups);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    protected function buildQuery(TrainingRepository $trainingRepository, Account $account)
    {
        $queryBuilder = $trainingRepository->createQueryBuilder('t')
            ->select('1')
            ->andWhere('t.account = :account')
            ->andWhere('t.time BETWEEN :startTime and :endTime')
            ->join('t.sport', 's')
            ->setParameter('account', $account->getId())
            ->setParameter('startTime', $this->AnovaData->getDateFromTimestamp())
            ->setParameter('endTime', $this->AnovaData->getDateToTimestamp());

        $this->addSportConditionToQuery($queryBuilder);
        $this->addTypeConditionToQuery($queryBuilder);
        $this->QueryGroup->addSelectionToQuery($queryBuilder, 't', 'grouping', 's');
        $this->QueryValue->addSelectionToQuery($queryBuilder, 't', 'value');

        return $queryBuilder->getQuery();
    }

    protected function addSportConditionToQuery(QueryBuilder $queryBuilder)
    {
        $queryBuilder->andWhere($queryBuilder->expr()->in('t.sport', ':sports'));
        $queryBuilder->setParameter(':sports', array_map(function(Sport $sport) {
            return $sport->getId();
        }, $this->AnovaData->getSport()));
    }

    protected function addTypeConditionToQuery(QueryBuilder $queryBuilder)
    {
        $types = $this->AnovaData->getType();

        if (!empty($types)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('t.type', ':types'));
            $queryBuilder->setParameter(':types', array_map(function(Type $type) {
                return $type->getId();
            }, $types));
        }
    }

    protected function filterEmptyGroups()
    {
        if (!$this->QueryGroup->showEmptyGroups()) {
            foreach ($this->Groups as $key => $data) {
                if (empty($data['data'])) {
                    unset($this->Groups[$key]);
                }
            }
        }
    }
}
