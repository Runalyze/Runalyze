<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Distance\Unit\AbstractDistanceUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class DistanceType extends AbstractType implements DataTransformerInterface
{
    /** @var AbstractDistanceUnit */
    protected $DistanceUnit;

    /** @var int */
    protected $ViewPrecision = 2;

    public function __construct(AbstractDistanceUnit $distanceUnit)
    {
        $this->DistanceUnit = $distanceUnit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = $this->DistanceUnit->getAppendix();
    }

    /**
     * @param  mixed $distance [km]
     * @return string
     */
    public function transform($distance)
    {
        return null === $distance ? '' : number_format($this->DistanceUnit->fromBaseUnit((float)$distance), $this->ViewPrecision);
    }

    /**
     * @param  null|string $distance
     * @return float|null
     */
    public function reverseTransform($distance)
    {
        return null === $distance ? null : $this->DistanceUnit->toBaseUnit((float)str_replace(',', '.', $distance));
    }

    public function getParent()
    {
        return TextType::class;
    }
}
