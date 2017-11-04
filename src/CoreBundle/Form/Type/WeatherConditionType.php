<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Profile\Weather\WeatherConditionProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Data\Weather\Condition;

class WeatherConditionType extends AbstractType
{

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = '°';

        $conditions = [];
        $Condition = new Condition(0);

        foreach (Condition::completeList() as $id) {
            $Condition->set($id);

            $conditions[$id] = $Condition->string();
        }

      //  $view->vars['choices'] = $conditions;

       /* foreach (Condition::completeList() as $id) {
            $Condition->set($id);

            $this->addOption($id, $Condition->string());
        }*/
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
