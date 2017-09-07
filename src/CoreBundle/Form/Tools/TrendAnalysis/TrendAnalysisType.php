<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools\TrendAnalysis;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue\QueryValues;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Dataset\Query;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TrendAnalysisType extends AbstractType
{
    /** @var SportRepository */
    protected $SportRepository;

    /** @var TypeRepository */
    protected $TypeRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function __construct(
        SportRepository $sportRepository,
        TypeRepository $typeRepository,
        TokenStorage $tokenStorage,
        ConfigurationManager $configurationManager
    )
    {
        $this->SportRepository = $sportRepository;
        $this->TypeRepository = $typeRepository;
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
            throw new \RuntimeException('Trend analysis type must have a valid account token.');
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
                'attr' => [
                    'data-placeholder' => __('Choose sport(s)'),
                    'class' => 'chosen-select full-size'
                ],
                'choice_attr' => function($sport, $key, $index) {
                    /* @var Sport $sport */
                    return ['data-id' => $sport->getId()];
                }
            ])
            ->add('type', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $this->TypeRepository->findAllFor($this->getAccount()),
                'choice_label' => function($type, $key, $index) {
                    /** @var Type $type */
                    return $type->getName();
                },
                'attr' => [
                    'data-placeholder' => __('Choose activity type(s)'),
                    'class' => 'chosen-select full-size'
                ],
                'choice_attr' => function($type, $key, $index) {
                    /* @var Type $type */
                    return ['data-sportid' => $type->getSport()->getId()];
                }
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
                        'Effective VO2max' => $this->getQueryValueEnumForVO2max(),
                        'Climb Score' => QueryValues::CLIMB_SCORE,
                        'Percentage hilly' => QueryValues::PERCENTAGE_HILLY,
                        'RPE' => QueryValues::RPE
                    ],
                    'Running dynamics' => [
                        'Ground contact time' => QueryValues::GROUND_CONTACT_TIME,
                        'Ground contact balance' => QueryValues::GROUND_CONTACT_BALANCE,
                        'Vertical oscillation' => QueryValues::VERTICAL_OSCILLATION,
                        'Flight time' => QueryValues::FLIGHT_TIME,
                        'Flight ratio' => QueryValues::FLIGHT_RATIO
                    ],
                    'FIT details' => [
                        'HRV analysis' => QueryValues::FIT_HRV_ANALYSIS,
                        'Performance condition (start)' => QueryValues::FIT_PERFORMANCE_CONDITION_START,
                        'Performance condition (end)' => QueryValues::FIT_PERFORMANCE_CONDITION_END,
                        'Recovery time' => QueryValues::FIT_RECOVERY_TIME,
                        'Training effect' => QueryValues::FIT_TRAINING_EFFECT,
                        'VO2max estimate' => QueryValues::FIT_VO2MAX_ESTIMATE
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TrendAnalysisData::class
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
