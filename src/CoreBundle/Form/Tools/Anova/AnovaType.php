<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools\Anova;

use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup\QueryGroups;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue\QueryValues;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AnovaType extends AbstractType
{
    /** @var SportRepository */
    protected $SportRepository;

    /** @var EquipmentTypeRepository */
    protected $EquipmentTypeRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        SportRepository $sportRepository,
        EquipmentTypeRepository $equipmentTypeRepository,
        TokenStorage $tokenStorage
    )
    {
        $this->SportRepository = $sportRepository;
        $this->EquipmentTypeRepository = $equipmentTypeRepository;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        $account = $this->TokenStorage->getToken() ? $this->TokenStorage->getToken()->getUser() : null;

        if (!($account instanceof Account)) {
            throw new \RuntimeException('Anova type must have a valid account token.');
        }

        return $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
            ->add('sport', ChoiceType::class, [
                'multiple' => true,
                'choices' => $this->SportRepository->findAllFor($this->getAccount()),
                'choice_label' => function($sport, $key, $index) {
                    /** @var Sport $sport */
                    return $sport->getName();
                },
                'placeholder' => 'Choose sport type(s)',
                'attr' => ['class' => 'chosen-select full-size']
            ])
            ->add('valueToGroupBy', ChoiceType::class, [
                //'expanded' => true,
                'choices' => [
                    'Time related' => [
                        'Month' => QueryGroups::MONTH,
                        'Year' => QueryGroups::YEAR
                    ],
                    'General groups' => [
                        'Sport type' => QueryGroups::SPORT,
                        'Activity type' => QueryGroups::TYPE
                    ],
                    'Equipment types' => $this->getGroupsForSingleChoiceEquipmentTypes()
                ]
            ])
            ->add('valueToLookAt', ChoiceType::class, [
                //'expanded' => true,
                'choices' => [
                    'Main values' => [
                        'Pace' => QueryValues::PACE,
                        'Distance' => QueryValues::DISTANCE,
                        'Duration' => QueryValues::DURATION,
                        'Heart rate' => QueryValues::HEART_RATE,
                        'TRIMP' => QueryValues::TRIMP,
                        'Power' => QueryValues::POWER,
                        'Cadence' => QueryValues::CADENCE
                    ],
                    'Running dynamics' => [
                        'Ground contact time' => QueryValues::GROUND_CONTACT_TIME,
                        'Ground contact balance' => QueryValues::GROUND_CONTACT_BALANCE,
                        'Vertical oscillation' => QueryValues::VERTICAL_OSCILLATION
                    ]
                ]
            ])
        ;
    }

    /**
     * @return array
     */
    protected function getGroupsForSingleChoiceEquipmentTypes()
    {
        $equipmentTypes = $this->EquipmentTypeRepository->findSingleChoiceTypesFor($this->getAccount());
        $groups = [];

        foreach ($equipmentTypes as $equipmentType) {
            $groups[$equipmentType->getName()] = QueryGroups::getEnumForEquipmentType($equipmentType);
        }

        return $groups;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AnovaData::class
        ]);
    }
}
