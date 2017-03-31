<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Parameter\Application\PaceUnit;
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\Profile\Sport\SportRelevance;
use Runalyze\Profile\View\DataBrowserRowProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
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
            throw new \RuntimeException('Poster type must have a valid account token.');
        }

        return $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usedInternalSportIds = $this->SportRepository->getUsedInternalSportIdsFor($this->getAccount());

        $builder
            ->add('name', TextType::class, array(
                'label' => 'Name',
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('power', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('outside', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('distances', CheckboxType::class, array(
                'required' => false,
                'label' => 'Has a distance'
            ))
            ->add('kcal', IntegerType::class, array(
                'attr' => array('min' => 1, 'max' => 10000),
                'required' => false,
                'label' => 'kcal/h'
            ))
            ->add('HFavg', IntegerType::class, array(
                'attr' => array('min' => 40, 'max' => 255),
                'required' => false,
                'label' => 'avg. HR'
            ))
            ->add('speed', ChoiceType::class, array(
                'choices' => PaceEnum::getChoices()
            ))
            ->add('mainEquipmenttype', ChoiceType::class, array(
                'choices' => $this->EquipmentTypeRepository->findAllFor($this->getAccount()),
                'choice_label' => 'name',
                'label' => 'Main equipment'
            ))
            ->add('defaultType', ChoiceType::class, array(
                'choices' => $this->TypeRepository->findAllFor($this->getAccount(), $builder->getData()),
                'choice_label' => 'name',
                'label' => 'Default sport type'
            ))
            ->add('short', ChoiceType::class, array(
                'choices' => DataBrowserRowProfile::getChoicesWithoutParent(),
                'choice_translation_domain' => false,
                'label' => 'Calendar view'
            ))
            ->add('internalSportId', ChoiceType::class, array(
                'choices' => SportProfile::getAvailableChoices($usedInternalSportIds),
                'choice_translation_domain' => false,
                'label' => 'Fix sport type'
            ))
            ->add('isMain', ChoiceType::class, array(
                'choices' => SportRelevance::getChoices(),
                'choice_translation_domain' => false,
                'label' => 'Sport relevance'
            ));;
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Sport'
        ));
    }
}
