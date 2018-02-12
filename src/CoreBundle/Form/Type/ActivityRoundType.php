<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ActivityRoundType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('distance', DistanceType::class, [
                'required' => false,
                'attr' => ['class' => 'small-size'],
                'label' => false
            ])
            ->add('duration', DurationType::class, [
                'required' => true,
                'attr' => ['class' => 'small-size'],
                'label' => false
            ])
            ->add('isActive', ChoiceType::class, [
                'choices' => [
                    'Resting' => false,
                    'Active' => true
                ],
                'required' => true,
                'attr' => ['class' => 'small-size'],
                'label' => false
            ])
        ;

        $builder->addViewTransformer($this);
    }

    /**
     * @param  mixed $value
     * @return array
     */
    public function transform($value)
    {
        if (!($value instanceof Round)) {
            return null;
        }

        /* @var Round $value */

        return [
            'distance' => $value->getDistance(),
            'duration' => $value->getDuration(),
            'isActive' => $value->isActive()
        ];
    }

    /**
     * @param  mixed $value
     * @return null|Round
     */
    public function reverseTransform($value)
    {
        if (is_array($value) && isset($value['duration']) && array_key_exists('distance', $value)) {
            return new Round(
                $value['distance'],
                $value['duration'],
                (bool)$value['isActive']
            );
        }

        return null;
    }
}
