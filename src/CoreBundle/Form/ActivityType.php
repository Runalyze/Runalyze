<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Runalyze\Bundle\CoreBundle\Form\Type\HumidityType;
use Runalyze\Bundle\CoreBundle\Form\Type\PressureType;
use Runalyze\Bundle\CoreBundle\Form\Type\TemperatureType;
use Runalyze\Bundle\CoreBundle\Form\Type\WindDirectionType;
use Runalyze\Data\RPE;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Runalyze\Bundle\CoreBundle\Form\Type\EnergyKcalType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartrateType;
class ActivityType extends AbstractType
{

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
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
        $placeOrParticipantsOptions = [
            'attr' => ['min' => 1, 'class'=> 'small-size'],
            'required' => false
        ];

        $builder
            ->add('s', DurationType::class, array(
                'required' => true,
                'attr' => ['class' => 'small-size'],
                'label' => 'Duration'
            ))
            ->add('distance', DistanceType::class, array(
                'required' => false,
                'attr' => ['class' => 'small-size']
            ))
            ->add('sport', ChoiceType::class, array(
                'choices' => $this->getAccount()->getSports(),
                'choice_label' => 'name',
                'choice_value' => 'getId',
                'label' => 'Sport type',
            ))
            ->add('type', ChoiceType::class, array(
                'choices' => $this->getAccount()->getActivityTypes(),
                'choice_label' => 'name',
                'choice_value' => 'getId',
                'label' => 'Activity type',
            ))
            ->add('title', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))



            ->add('kcal', EnergyKcalType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('pulseAvg', HeartrateType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('pulseMax', HeartrateType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('rpe', ChoiceType::class, array(
                'choices' => RPE::completeList(),
                'label' => 'RPE',
            ))


            ->add('temperature', TemperatureType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('wind_deg', WindDirectionType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('humidity', HumidityType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('pressure', PressureType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('notes', TextareaType::class, array(
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'fullwidth']
            ))
            ->add('route', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('partner', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))

        ;





        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Training::class
        ]);
    }
}
