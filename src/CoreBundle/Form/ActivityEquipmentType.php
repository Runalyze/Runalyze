<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ActivityEquipmentType extends AbstractTokenStorageAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $equipmentCategories = $this->getAccount()->getEquipmentTypes();
        $accountEquipment = $this->getAccount()->getEquipment();

        $equipment = [];
        /** @var Equipment $acEqp */
        foreach($accountEquipment as $acEqp) {
            $equipment[$acEqp->getType()->getId()][] = $acEqp;
        }

        /** @var EquipmentType $category */
        foreach ($equipmentCategories as $category) {
            if (isset($equipment[$category->getId()])) {
                if ($category->getInput() == EquipmentType::CHOICE_SINGLE) {
                    $builder->add($category->getId(), EntityType::class, [
                        'class' => Equipment::class,
                        'choices' => $equipment[$category->getId()],
                        'choice_label' => 'name',
                        'attr' => [
                            'class' => 'w100 with50erLabel equipment'
                        ],
                        'choice_attr' => function ($eqp, $key, $index) {
                            /* @var Equipment $eqp */
                            return [
                                'data-start' => ($eqp->getDateStart()) ? $eqp->getDateStart()->format('Y-m-d') : '',
                                'data-end' => ($eqp->getDateEnd()) ? $eqp->getDateEnd()->format('Y-m-d') : ''];
                        },
                        'label' => $category->getName(),]);
                } else {
                    $builder->add($category->getId(), EntityType::class, [
                        'class' => Equipment::class,
                        'choices' => $equipment[$category->getId()],
                        'choice_label' => 'name',
                        'choice_attr' => function ($eqp, $key, $index) {
                            /* @var Equipment $eqp */
                            return [
                                'data-start' => ($eqp->getDateStart()) ? $eqp->getDateStart()->format('Y-m-d') : '',
                                'data-end' => ($eqp->getDateEnd()) ? $eqp->getDateEnd()->format('Y-m-d') : ''];
                        },
                        'label' => $category->getName(),
                        'attr' => [
                            'class' => 'w100 with50erLabel equipment'
                        ],
                        'multiple' => true,
                        'expanded' => true,
                        'required' => false
                    ]);
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Equipment'
        ));
    }
}
