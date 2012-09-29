<?php

namespace Lightning\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lightning\ApiBundle\DependencyInjection\Security\AccountFactory;

/**
 * Symfony Bundle defintion
 *
 * @codeCoverageIgnore
 */
class LightningApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new AccountFactory());
    }
}
