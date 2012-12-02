<?php

namespace Lightning\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lightning\ApiBundle\DependencyInjection\Security\AccountFactory;
use Lightning\ApiBundle\DependencyInjection\Security\AccessTokenFactory;

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
        $extension->addSecurityListenerFactory(new AccessTokenFactory());
    }
}
