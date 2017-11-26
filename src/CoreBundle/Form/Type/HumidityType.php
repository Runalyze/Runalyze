<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HumidityType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = '%';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'scale' => 0,
            'type' => 'fractional',
            'compound' => false,
        ));

        $resolver->setAllowedValues('type', array(
            'fractional',
            'integer',
        ));

        $resolver->setAllowedTypes('scale', 'int');
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
