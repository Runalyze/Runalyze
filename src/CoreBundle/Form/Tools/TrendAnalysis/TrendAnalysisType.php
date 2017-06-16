<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools\TrendAnalysis;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue\QueryValues;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
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

    /** @var TokenStorage */
    protected $TokenStorage;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function __construct(
        SportRepository $sportRepository,
        TokenStorage $tokenStorage,
        ConfigurationManager $configurationManager
    )
    {
        $this->SportRepository = $sportRepository;
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
                'placeholder' => 'Choose sport(s)',
                'attr' => ['class' => 'chosen-select full-size']
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
                        'Percentage hilly' => QueryValues::PERCENTAGE_HILLY
                    ],
                    'Running dynamics' => [
                        'Ground contact time' => QueryValues::GROUND_CONTACT_TIME,
                        'Ground contact balance' => QueryValues::GROUND_CONTACT_BALANCE,
                        'Vertical oscillation' => QueryValues::VERTICAL_OSCILLATION
                    ],
                    'FIT details' => [
                        'HRV analysis' => QueryValues::FIT_HRV_ANALYSIS,
                        'Performance condition (start)' => QueryValues::FIT_PERFORMANCE_CONDITION_START,
                        'Performance condition (end)' => QueryValues::FIT_PERFORMANCE_CONDITION_END,
                        'Recovery time' => QueryValues::FIT_RECOVERY_TIME,
                        'Training effect' => QueryValues::FIT_TRAINING_EFFECT,
                        'VO2max estimate' => QueryValues::FIT_VO2MAX_ESTIMATE
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
