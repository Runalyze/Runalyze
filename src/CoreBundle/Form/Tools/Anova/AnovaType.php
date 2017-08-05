<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools\Anova;

use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup\QueryGroups;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue\QueryValues;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
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

    /** @var TypeRepository */
    protected $TypeRepository;

    /** @var EquipmentTypeRepository */
    protected $EquipmentTypeRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        SportRepository $sportRepository,
        TypeRepository $typeRepository,
        EquipmentTypeRepository $equipmentTypeRepository,
        TokenStorage $tokenStorage,
        ConfigurationManager $configurationManager
    )
    {
        $this->SportRepository = $sportRepository;
        $this->TypeRepository = $typeRepository;
        $this->EquipmentTypeRepository = $equipmentTypeRepository;
        $this->TokenStorage = $tokenStorage;
        $this->ConfigurationManager = $configurationManager;

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
                'placeholder' => 'Choose sport(s)',
                'attr' => ['class' => 'chosen-select full-size']
            ])
            ->add('type', ChoiceType::class, [
                'multiple' => true,
                'choices' => $this->TypeRepository->findAllFor($this->getAccount()),
                'choice_label' => function($type, $key, $index) {
                    /** @var Type $type */
                    return $type->getName();
                },
                'placeholder' => 'Choose activity type(s)',
                'attr' => ['class' => 'chosen-select full-size'],
                'choice_attr' => function($type, $key, $index) {
                    /* @var Type $type */
                    return ['data-sportid' => $type->getSport()->getId()];
                }
            ])
            ->add('valueToGroupBy', ChoiceType::class, [
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
                'choices' => [
                    'Main values' => [
                        'Pace' => QueryValues::PACE,
                        'Distance' => QueryValues::DISTANCE,
                        'Duration' => QueryValues::DURATION,
                        'Heart rate' => QueryValues::HEART_RATE_AVERAGE,
                        'TRIMP' => QueryValues::TRIMP,
                        'Power' => QueryValues::POWER,
                        'Cadence' => QueryValues::CADENCE,
                        'RPE' => QueryValues::RPE,
                        'Effective VO2max' => $this->getQueryValueEnumForVO2max()
                    ],
                    'Running dynamics' => [
                        'Ground contact time' => QueryValues::GROUND_CONTACT_TIME,
                        'Ground contact balance' => QueryValues::GROUND_CONTACT_BALANCE,
                        'Vertical oscillation' => QueryValues::VERTICAL_OSCILLATION
                    ],
                    'Weather' => [
                        'Temperature' => QueryValues::WEATHER_TEMPERATURE,
                        'Humidity' => QueryValues::WEATHER_HUMIDITY,
                        'Pressure' => QueryValues::WEATHER_PRESSURE
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

    /**
     * @return string
     */
    protected function getQueryValueEnumForVO2max()
    {
        if ($this->ConfigurationManager->getList()->useVO2maxCorrectionForElevation()) {
            return QueryValues::VO2MAX_WITH_ELEVATION;
        }

        return QueryValues::VO2MAX;
    }
}
