<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Form\DataTransformer\DistanceUnitTransformer;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Runalyze\Bundle\CoreBundle\Form\Type\EnergyType;
use Runalyze\Bundle\CoreBundle\Form\Type\TemperatureType;
use Runalyze\Bundle\CoreBundle\Form\Type\WeightType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;

class RaceResultType extends AbstractType
{
    private $configurationManager;

    public function __construct(ConfigurationManager $manager)
    {
        $this->configurationManager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('official_time', DurationType::class
            )
            ->add('official_distance', DistanceType::class, array(
                'required' => false,
            ))
            ->add('officially_measured', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('place_total', IntegerType::class, array(
                'attr' => array('min' => 1),
                'required' => false
            ))
            ->add('place_gender', IntegerType::class, array(
                'attr' => array('min' => 1),
                'required' => false
            ))
            ->add('place_ageclass', IntegerType::class, array(
                'attr' => array('min' => 1),
                'required' => false
            ))
            ->add('participants_total', IntegerType::class, array(
                'attr' => array('min' => 1),
                'required' => false
            ))
            ->add('participants_gender', IntegerType::class, array(
                'attr' => array('min' => 1),
                'required' => false
            ))
            ->add('participants_ageclass', IntegerType::class, array(
                'attr' => array('min' => 1),
                'required' => false
            ));

        $builder->get('official_distance')
            ->addModelTransformer(new DistanceUnitTransformer($this->configurationManager));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Raceresult'
        ));
    }
}
