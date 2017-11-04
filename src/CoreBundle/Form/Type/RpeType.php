<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Data\RPE;


class RpeType extends AbstractType
{

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
