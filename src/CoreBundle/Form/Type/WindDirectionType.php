<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class WindDirectionType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = '°';
        $view->vars['attr']['min'] = 0;
        $view->vars['attr']['max'] = 360;
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
