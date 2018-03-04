<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultiImporterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('show_multi_editor', CheckboxType::class, [
            'required' => false,
            'mapped' => false,
            'label' => 'Show multi editor afterwards'
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $activityHashes = $event->getData();

            $this->addChoicesToForm($event->getForm(), is_array($activityHashes) ? $activityHashes : []);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (is_array($data['activity'])) {
                $this->addChoicesToForm($event->getForm(), $data['activity']);

                $event->getForm()->get('activity')->setData($data['activity']);
            }
        });
    }

    protected function addChoicesToForm(FormInterface $form, array $activityHashes)
    {
        if ($form->has('activity')) {
            $form->remove('activity');
        }

        $form->add('activity', ChoiceType::class, [
            'mapped' => false,
            'required' => true,
            'multiple' => true,
            'expanded' => true,
            'choices' => $activityHashes,
            'choice_translation_domain' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
