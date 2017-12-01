<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Form\TokenStorageAwareTypeTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityEquipmentType extends AbstractType implements DataTransformerInterface
{
    use TokenStorageAwareTypeTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $equipmentCategories = $this->getAccount()->getEquipmentTypes();
        $accountEquipment = $this->getAccount()->getEquipment();

        $equipment = [];
        /** @var Equipment $acEqp */
        foreach ($accountEquipment as $acEqp) {
            $equipment[$acEqp->getType()->getId()][] = $acEqp;
        }

        /** @var EquipmentType $category */
        foreach ($equipmentCategories as $category) {
            if (isset($equipment[$category->getId()])) {
                $isSingleChoice = $category->getInput() == EquipmentType::CHOICE_SINGLE;

                $builder->add($category->getId(), EntityType::class, [
                    'class' => Equipment::class,
                    'choices' => $equipment[$category->getId()],
                    'choice_label' => 'name',
                    'choice_attr' => function ($eqp, $key, $index) {
                        /* @var Equipment $eqp */
                        return $this->getChoiceAttributesForEquipment($eqp);
                    },
                    'label' => $category->getName(),
                    'multiple' => !$isSingleChoice,
                    'expanded' => !$isSingleChoice,
                    'required' => false
                ]);
            }
        }

        $builder->addViewTransformer($this);
    }

    /**
     * @param  mixed $value
     * @return array
     */
    public function transform($value)
    {
        if (!($value instanceof \Doctrine\Common\Collections\Collection)) {
            return null;
        }

        /* @var Equipment[] $value */
        $categories = [];

        foreach ($value as $equipment) {
            if ($equipment->getType()->getInput() == EquipmentType::CHOICE_SINGLE) {
                $categories[$equipment->getType()->getId()] = $equipment;
            } else {
                if (!isset($categories[$equipment->getType()->getId()])) {
                    $categories[$equipment->getType()->getId()] = new ArrayCollection;
                }

                $categories[$equipment->getType()->getId()][] = $equipment;
            }
        }

        return $categories;
    }

    /**
     * @param  mixed $value
     * @return float|null
     */
    public function reverseTransform($value)
    {
        $equipment = new ArrayCollection;

        if (is_array($value)) {
            foreach ($value as $subValue) {
                if ($subValue instanceof \Doctrine\Common\Collections\Collection) {
                    foreach ($subValue as $object) {
                        $equipment[] = $object;
                    }
                } elseif (null !== $subValue) {
                    $equipment[] = $subValue;
                }
            }
        }

        return $equipment;
    }

    /**
     * @return array
     */
    protected function getChoiceAttributesForEquipment(Equipment $equipment)
    {
        return [
            'data-start' => $equipment->getDateStart() ? $equipment->getDateStart()->format('Y-m-d') : '',
            'data-end' => $equipment->getDateEnd() ? $equipment->getDateEnd()->format('Y-m-d') : ''
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }
}
