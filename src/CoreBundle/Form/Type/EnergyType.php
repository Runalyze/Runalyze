<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Energy\Unit\AbstractEnergyUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class EnergyType extends AbstractType implements DataTransformerInterface
{
    /** @var AbstractEnergyUnit */
    protected $EnergyUnit;

    public function __construct(AbstractEnergyUnit $energyUnit)
    {
        $this->EnergyUnit = $energyUnit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = $this->EnergyUnit->getAppendix();
    }

    /**
     * @param  mixed $energy [kcal]
     * @return string
     */
    public function transform($energy)
    {
        return null === $energy ? '' : (string)round($this->EnergyUnit->fromBaseUnit((float)$energy));
    }

    /**
     * @param  null|string $energy
     * @return int|null
     */
    public function reverseTransform($energy)
    {
        return null === $energy ? null : (int)$this->EnergyUnit->toBaseUnit($energy);
    }

    public function getParent()
    {
        return TextType::class;
    }
}
