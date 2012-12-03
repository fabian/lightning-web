<?php

namespace Lightning\ApiBundle\DependencyInjection\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

/**
 * Factory for the security provider
 *
 * @codeCoverageIgnore
 */
class AccountFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.account.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('lightning.api_bundle.security.account_provider'))
            ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.account.'.$id;
        $container->setDefinition(
            $listenerId,
            new DefinitionDecorator('lightning.api_bundle.security.account_listener')
        );

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'http';
    }

    public function getKey()
    {
        return 'account';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
