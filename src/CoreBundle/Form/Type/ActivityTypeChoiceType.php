<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;

class ActivityTypeChoiceType extends AbstractTokenStorageAwareType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = $this->getAccount()->getActivityTypes();
        $view->vars['choice_label'] = function($type, $key, $index) {
        /** @var Type $type */
        return $type->getName();
    };
               $view->vars['choice_attr'] = function($type, $key, $index) {
        /* @var Type $type */
        return ['data-id' => $type->getId()];
    };
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
