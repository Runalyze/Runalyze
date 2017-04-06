<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Bundle\CoreBundle\Form\Type\EnergyKcalType;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartrateType;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\Profile\Sport\SportRelevance;
use Runalyze\Profile\View\DataBrowserRowProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SportType extends AbstractType
{
    /** @var TypeRepository */
    protected $TypeRepository;

    /** @var SportRepository */
    protected $SportRepository;

    /** @var EquipmentTypeRepository */
    protected $EquipmentTypeRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        TypeRepository $typeRepository,
        SportRepository $sportRepository,
        EquipmentTypeRepository $equipmentTypeRepository,
        TokenStorage $tokenStorage
    )
    {
        $this->TypeRepository = $typeRepository;
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
            throw new \RuntimeException('Sport type must have a valid account token.');
        }

        return $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sport $sport */
        $sport = $builder->getData();
        $usedInternalSportIds = $this->SportRepository->getUsedInternalSportIdsFor($this->getAccount());
        $isRunning = $sport->getInternalSportId() == SportProfile::RUNNING;
        if ($sport->getId() !== null) {
            $activityTypes = $this->TypeRepository->findAllFor($this->getAccount(), $sport);
            $equipmentTypes = $sport->getEquipmentType();
        }
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'attr' => [
                    'autofocus' => true
                ]
            ])
            ->add('power', CheckboxType::class, [
                'required' => false,
            ])
            ->add('outside', CheckboxType::class, [
                'required' => false,
            ])
            ->add('distances', CheckboxType::class, [
                'required' => false,
                'label' => 'Has a distance'
            ])
            ->add('kcal', EnergyKcalType::class, [
                'attr' => ['min' => 1, 'max' => 10000],
                'required' => false,
                'label' => 'kcal/h'
            ])
            ->add('HFavg', HeartrateType::class, [
                'attr' => ['min' => 40, 'max' => 255],
                'required' => false,
                'label' => 'avg. HR'
            ])
            ->add('speed', ChoiceType::class, [
                'choices' => PaceEnum::getChoices(),
                'label' => 'Speed unit'
            ])
            ->add('short', ChoiceType::class, [
                'choices' => DataBrowserRowProfile::getChoicesWithoutParent(),
                'choice_translation_domain' => false,
                'label' => 'Calendar view'
            ])
            ->add('isMain', ChoiceType::class, [
                'choices' => SportRelevance::getChoices(),
                'choice_translation_domain' => false,
                'label' => 'Sport relevance'
            ]);

        if ($sport->getInternalSportId() == null) {
            $builder->add('internalSportId', ChoiceType::class, [
                'required' => false,
                'choices' => $this->getFilteredChoicesForInternalSportId($usedInternalSportIds, $sport),
                'choice_translation_domain' => false,
                'placeholder' => 'None (custom sport type)',
                'preferred_choices' => function ($val, $key) {
                    return '' == $key;
                },
                'label' => 'Internal sport type',
                'disabled' => $isRunning
            ]);
        }
        if (!empty($equipmentTypes)) {
            $builder->add('mainEquipmenttype', ChoiceType::class, [
                'required' => false,
                'choices' => $equipmentTypes,
                'choice_label' => 'name',
                'label' => 'Main equipment',
            ]);
        }
        if (!empty($activityTypes)) {
            $builder->add('defaultType', ChoiceType::class, [
                'required' => false,
                'choices' => $activityTypes,
                'choice_label' => 'name',
                'placeholder' => 'None',
                'preferred_choices' => function ($val, $key) {
                    return '' == $key;
                },
                'label' => 'Default activity type'
            ]);
        }
    }

    protected function getFilteredChoicesForInternalSportId(array $usedInternalIds, Sport $sport = null)
    {
        $choicesWithIdsAsKeys = array_flip(SportProfile::getChoices());

        foreach ($usedInternalIds as $id) {
            if (null === $sport || $sport->getInternalSportId() != $id) {
                unset($choicesWithIdsAsKeys[$id]);
            }
        }

        if (isset($choicesWithIdsAsKeys[SportProfile::GENERIC])) {
            unset($choicesWithIdsAsKeys[SportProfile::GENERIC]);
        }

        return array_flip($choicesWithIdsAsKeys);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Sport'
        ]);
    }
}
