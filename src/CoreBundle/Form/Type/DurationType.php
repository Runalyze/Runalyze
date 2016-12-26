<?php
namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Activity\Duration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DurationType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    /**
     * @param  mixed $duration [s]
     * @return string
     */
    public function transform($duration)
    {
        return null === $duration ? null : Duration::format($duration);
    }

    /**
     * @param  null|string $duration
     * @return float|null
     */
    public function reverseTransform($duration)
    {
        return null === $duration ? '' : (new Duration($duration))->seconds();
    }

    public function getParent()
    {
        return TextType::class;
    }
}