<?php
namespace Runalyze\Bundle\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('security.authentication.switchuser_listener');
        $definition->setClass('Runalyze\Bundle\CoreBundle\EventListener\SwitchUserListener');
    }
}