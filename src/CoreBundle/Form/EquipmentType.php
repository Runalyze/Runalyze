<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;


class EquipmentType extends AbstractType
{

    /** @var EquipmentTypeRepository */
    protected $EquipmentRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        EquipmentTypeRepository $equipmentTypeRepository,
        TokenStorage $tokenStorage
    )
    {
        $this->EquipmentRepository = $equipmentTypeRepository;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        $account = $this->TokenStorage->getToken() ? $this->TokenStorage->getToken()->getUser() : null;

        if (!($account instanceof Account)) {
            throw new \RuntimeException('Equipment type must have a valid account token.');
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
            ->add('type', ChoiceType::class, array(
                'choices' => $this->EquipmentRepository->findAllFor($this->getAccount()),
                'choice_label' => 'name',
                'choice_value' => 'getId',
                'label' => 'Category'
            ))
            ->add('additionalKm', DistanceType::class, array(
                'label' => 'prev. distance',
                'required' => true,
            ))
            ->add('dateStart', DateType::class, [
                'label' => 'Start of use',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'required' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'End of use',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'required' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
            ->add('notes', TextareaType::class, array(
                'label' => 'Notes',
                'required' => false,
                'empty_data' => '',
                'attr' => ['class' => 'fullwidth']
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Equipment'
        ));
    }
}
