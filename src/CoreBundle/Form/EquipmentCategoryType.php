<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class EquipmentCategoryType extends AbstractType
{
    /** @var SportRepository */
    protected $SportRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        SportRepository $SportRepository,
        TokenStorage $tokenStorage
    )
    {
        $this->SportRepository = $SportRepository;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        $account = $this->TokenStorage->getToken() ? $this->TokenStorage->getToken()->getUser() : null;

        if (!($account instanceof Account)) {
            throw new \RuntimeException('Equipment category must have a valid account token.');
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
            ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $equipmentType = $event->getData();
            $form = $event->getForm();
            if (!$equipmentType || null === $equipmentType->getId()) {
                $form->add('input', ChoiceType::class, array(
                    'choices' => ['Single choice' => 0, 'Multiple Choice' => 1],
                    'choice_translation_domain' => false,
                    'label' => 'Type'
                ));
            }
        });

        $builder->add('maxKm', DistanceType::class, array(
                'label' => 'max. Km',
                'required' => false
            ))
            ->add('maxTime', DurationType::class, array(
                'label' => 'max. Time',
                'attr' => ['class' => 'medium-size']
            ))
            ->add('sport', EntityType::class, [
                'class'   => Sport::class,
                'choices' => $this->SportRepository->findAllFor($this->getAccount()),
                'choice_label' => 'name',
                'label' => 'Assigned sports ',
                'placeholder' => 'Choose sport type(s)',
                'attr' => ['class' => 'chosen-select full-size'],
                'multiple' => true,
                'expanded' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\EquipmentType'
        ));
    }
}
