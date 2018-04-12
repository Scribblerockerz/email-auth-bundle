<?php

namespace Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EmailAuthenticationFactory implements SecurityFactoryInterface
{
    const AUTHENTICATION_PROVIDER_KEY = 'rockz_email_auth';

    /**
     * Configures the container services required to use the authentication listener.
     *
     * @param ContainerBuilder $container
     * @param string $id The unique id of the firewall
     * @param array $config The options array for the listener
     * @param $userProviderId
     * @param $defaultEntryPointId
     * @return array containing three values:
     *               - the provider id
     *               - the listener id
     *               - the entry point id
     */
    public function create(ContainerBuilder $container, $id, $config, $userProviderId, $defaultEntryPointId)
    {
        $providerId = $this->createAuthenticationProvider($container, $id, $config, $userProviderId);
        $listenerId = $this->createAuthenticationListener($container, $id, $config);
        $entryPointId = $this->createEntryPoint($container, $id, $config);

        // add remember-me aware tag if requested
        if ($this->isRememberMeAware($config)) {
            $container
                ->getDefinition($listenerId)
                ->addTag('security.remember_me_aware', array('id' => $id, 'provider' => $userProviderId))
            ;
        }

        return array($providerId, $listenerId, $entryPointId);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createAuthenticationProvider(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        $userProviderId
    ) {
        $providerId = 'security.authentication.rockz_email_auth_provider.'.$firewallName;
        $container
            ->setDefinition(
                $providerId,
                new ChildDefinition('rockz_email_auth.security_authentication_provider.email_authentication_provider')
            )
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$providerKey', $firewallName);

        return $providerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createAuthenticationListener(ContainerBuilder $container, string $firewallName, array $config)
    {
        $listenerId = 'security.authentication.rockz_email_auth_listener.'.$firewallName;
        $container
            ->setDefinition(
                $listenerId,
                new ChildDefinition('rockz_email_auth.security_firewall.email_authentication_listener')
            )
            ->setArgument('$providerKey', $firewallName);

        return $listenerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createEntryPoint(ContainerBuilder $container, string $firewallName, array $config)
    {
        $entryPointId = 'security.authentication.rockz_email_auth_entry_point.'.$firewallName;
        $container
            ->setDefinition(
                $entryPointId,
                new ChildDefinition('rockz_email_auth.security_http_entry_point.email_authentication_entry_point')
            )
            ->setArgument('$httpUtils', new Reference('security.http_utils'));

        return $entryPointId;
    }

    /**
     * Subclasses may disable remember-me features for the listener, by
     * always returning false from this method.
     *
     * @param array $config
     * @return bool Whether a possibly configured RememberMeServices should be set for this listener
     */
    protected function isRememberMeAware(array $config)
    {
        return true;//;$config['remember_me'];
    }

    /**
     * Defines the position at which the provider is called.
     * Possible values: pre_auth, form, http, and remember_me.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * Defines the configuration key used to reference the provider
     * in the firewall configuration.
     *
     * @return string
     */
    public function getKey()
    {
        return self::AUTHENTICATION_PROVIDER_KEY;
    }

    /**
     * @param NodeDefinition $builder
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        // TODO: Implement addConfiguration() method.
    }
}