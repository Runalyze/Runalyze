<?php
namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Activity\Duration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DurationType extends AbstractType implements DataTransformerInterface
{
    /** @var bool */
    protected $IsRequired = false;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
        if (isset($options['required'])) {
            $this->IsRequired = $options['required'];
        }
    }

    /**
     * @param  mixed $duration [s]
     * @return string
     */
    public function transform($duration)
    {
        return null === $duration ? '0:00'  : Duration::format($duration);
    }

    /**
     * @param  string $duration
     * @return float
     * @throws TransformationFailedException if $duration is null
     */
    public function reverseTransform($duration)
    {
        if (null === $duration) {
            if ($this->IsRequired) {
                throw new TransformationFailedException('Duration cannot be empty');
            }

            return null;
        }

        return (new Duration($duration))->seconds();
    }

    public function getParent()
    {
        return TextType::class;
    }
}
