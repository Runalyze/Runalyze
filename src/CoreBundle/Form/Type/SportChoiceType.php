<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;

class SportChoiceType extends AbstractTokenStorageAwareType
{

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = $this->getAccount()->getSports();
        $view->vars['choice_label'] = function($sport, $key, $index) {
        /** @var Sport $sport */
        return $sport->getName();
    };
               $view->vars['choice_attr'] = function($sport, $key, $index) {
        /* @var Sport $sport */
        return ['data-id' => $sport->getId()];
    };
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
