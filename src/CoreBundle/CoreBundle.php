<?php

namespace Runalyze\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Runalyze\Bundle\CoreBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

/**
 * Bundles are auto configured as Twig paths, public resources etc.
 * So having this will bootstrap a lot of boilerplate for console commands
 * and so on.
 */
class CoreBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
