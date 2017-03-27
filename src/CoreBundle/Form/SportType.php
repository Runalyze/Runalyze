<?php

namespace Runalyze\Bundle\CoreBundle\Form;

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

    /** @var EquipmentTypeRepository */
    protected $EquipmentTypeRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        TypeRepository $typeRepository,
        EquipmentTypeRepository $equipmentTypeRepository,
        TokenStorage $tokenStorage
    )
    {
        $this->TypeRepository = $typeRepository;
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
            ->add('mainEquipmenttype', ChoiceType::class, array(
                'choices' => $this->EquipmentTypeRepository->findAllFor($this->getAccount()),
                'choice_label' => function($equipmentType, $key, $index) {
                    /** @var EquipmentType $equipmentType */
                    return $equipmentType->getName();
                },
                //'choice_value' => 'getId',
                'label' => 'Main equipment'
            ))
            ->add('defaultType', ChoiceType::class, array(
                'choices' => $this->TypeRepository->findAllFor($this->getAccount(), $builder->getData()),
                'choice_label' => function($sportType, $key, $index) {
                    /** @var Type $sportType */
                    return $sportType->getName();
                },
                //'choice_value' => 'getId',
                'label' => 'Default sport type'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Sport'
        ));
    }
}
