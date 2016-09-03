<?php
namespace Runalyze\Bundle\CoreBundle\Form\Settings;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Runalyze\Timezone;
use Runalyze\Language;
use Runalyze\Profile\Athlete\Gender;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                    'disabled' => true
            ))
            ->add('name', TextType::class)
            ->add('mail', EmailType::class, array(
                    'disabled' => true
            ))
            ->add('language', ChoiceType::class, array(
                'choices' => Language::getChoices()
            ))
            ->add('timezone', ChoiceType::class, array(
                'choices' => Timezone::getChoices(),
                'choice_translation_domain' => false
            ))
            ->add('gender', ChoiceType::class, array(
                'choices' => Gender::getChoices(),
                'choice_translation_domain' => false
            ))
            ->add('birthyear', IntegerType::class, array(
                'attr' => array('min' => 1900, 'max' => date("Y")),
                'required' => false
            ))
            ->add('registerdate', DateTimeType::class, array(
                    'input' => 'timestamp',
                    'disabled' => true,
                    'date_widget' =>  'single_text',
                    'date_format' => 'yyyy-MM-dd h:s'
                ))
            ->add('allow_support', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                )))
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Account'
        ));
    }
}