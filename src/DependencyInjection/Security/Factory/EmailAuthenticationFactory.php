<?php

namespace Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $remoteAuthorizerId = $this->createRemoteAuthorizationService($container, $id, $config);
        $providerId = $this->createAuthenticationProvider($container, $id, $config, $userProviderId, $remoteAuthorizerId);
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
    protected function createAuthenticationProvider(ContainerBuilder $container, string $firewallName, array $config, $userProviderId, $remoteAuthorizerId) {
        $providerId = 'security.authentication.rockz_email_auth_provider.'.$firewallName;
        $container
            ->setDefinition(
                $providerId,
                new ChildDefinition('rockz_email_auth.security_authentication_provider.email_authentication_provider')
            )
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$providerKey', $firewallName)
            ->setArgument('$remoteAuthorizer', new Reference($remoteAuthorizerId))
        ;

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

        $listener = new ChildDefinition('rockz_email_auth.security_firewall.email_authentication_listener');
        $listener->setArgument('$providerKey', $firewallName);
        $listener->replaceArgument('$emailParameter', $config['email_parameter']);
        $listener->replaceArgument('$preAuthenticationSuccessHandler', new Reference($this->createPreAuthenticationSuccessHandler($container, $firewallName, $config)));
        $listener->replaceArgument('$preAuthenticationFailureHandler', new Reference($this->createPreAuthenticationFailureHandler($container, $firewallName, $config)));
        $listener->replaceArgument('$authenticationSuccessHandler', new Reference($this->createSuccessHandler($container, $firewallName, $config)));
        $listener->replaceArgument('$authenticationFailureHandler', new Reference($this->createFailureHandler($container, $firewallName, $config)));


        $container->setDefinition($listenerId, $listener);
        return $listenerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createPreAuthenticationSuccessHandler(ContainerBuilder $container, string $firewallName, array $config)
    {
        $defaultSuccessHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_success_handler';
        $successHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_pre_auth_success_handler'.'.'.$firewallName;

        $successHandler = null;

        if (isset($config['pre_auth_success_handler'])) {
            $successHandler = new ChildDefinition($config['pre_auth_success_handler']);
        } else {
            $successHandler = new ChildDefinition($defaultSuccessHandlerId);
        }

        $successHandler->replaceArgument('$redirectPath', $config['pre_auth_success_redirect']);

        $container->setDefinition($successHandlerId, $successHandler);

        return $successHandlerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createPreAuthenticationFailureHandler(ContainerBuilder $container, string $firewallName, array $config)
    {
        $defaultFailureHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_failure_handler';
        $failureHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_pre_auth_failure_handler'.'.'.$firewallName;

        $failureHandler = null;

        if (isset($config['pre_auth_failure_handler'])) {
            $failureHandler = new ChildDefinition($config['pre_auth_failure_handler']);
        } else {
            $failureHandler = new ChildDefinition($defaultFailureHandlerId);
        }

        $failureHandler->replaceArgument('$redirectPath', $config['pre_auth_failure_redirect']);

        $container->setDefinition($failureHandlerId, $failureHandler);

        return $failureHandlerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createSuccessHandler(ContainerBuilder $container, string $firewallName, array $config)
    {
        $defaultSuccessHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_success_handler';
        $successHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_success_handler'.'.'.$firewallName;

        $successHandler = null;

        if (isset($config['success_handler'])) {
            $successHandler = new ChildDefinition($config['success_handler']);
        } else {
            $successHandler = new ChildDefinition($defaultSuccessHandlerId);
        }

        $successHandler->replaceArgument('$redirectPath', $config['success_redirect']);

        $container->setDefinition($successHandlerId, $successHandler);

        return $successHandlerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createFailureHandler(ContainerBuilder $container, string $firewallName, array $config)
    {
        $defaultFailureHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_failure_handler';
        $failureHandlerId = 'rockz_email_auth.security_http_authentication.email_authentication_failure_handler'.'.'.$firewallName;

        $failureHandler = null;

        if (isset($config['failure_handler'])) {
            $failureHandler = new ChildDefinition($config['failure_handler']);
        } else {
            $failureHandler = new ChildDefinition($defaultFailureHandlerId);
        }

        $failureHandler->replaceArgument('$redirectPath', $config['failure_redirect']);

        $container->setDefinition($failureHandlerId, $failureHandler);

        return $failureHandlerId;
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
            ->setArgument('$httpUtils', new Reference('security.http_utils'))
            ->replaceArgument('$redirectPath', $config['initial_redirect']);;

        return $entryPointId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $firewallName
     * @param array $config
     * @return string
     */
    protected function createRemoteAuthorizationService(ContainerBuilder $container, string $firewallName, array $config)
    {
        $authorizationMailerId = 'rockz_email_auth.mailer.authorization_mailer.'.$firewallName;
        $authorizationMailer = $container
            ->setDefinition(
                $authorizationMailerId,
                new ChildDefinition('rockz_email_auth.mailer.authorization_mailer')
            );

        // apply firewall instance configuration
        if (isset($config['remote_authorization'])) {
            $authorizationMailer->replaceArgument('$options', $config['remote_authorization']);
        }

        $remoteAuthorizerId = 'rockz_email_auth.remote_authorization.remote_authorizer.'.$firewallName;
        $container
            ->setDefinition(
                $remoteAuthorizerId,
                new ChildDefinition('rockz_email_auth.remote_authorization.remote_authorizer')
            )
            ->setPublic(true)
            ->replaceArgument('$mailer', new Reference($authorizationMailerId))
        ;

        return $remoteAuthorizerId;
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
        return $config['remember_me'];
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
        /** @var ArrayNodeDefinition $builder */
        $builder
            ->children()
//                ->scalarNode('provider')->end()
                ->booleanNode('remember_me')->defaultTrue()->end()
                ->scalarNode('pre_auth_success_handler')->end()
                ->scalarNode('pre_auth_failure_handler')->end()
                ->scalarNode('success_handler')->end()
                ->scalarNode('failure_handler')->end()
                ->scalarNode('email_parameter')
                    ->defaultValue('email_auth')
                ->end()
                ->scalarNode('initial_redirect')
                    ->defaultValue('/access')
                ->end()
                ->scalarNode('pre_auth_success_redirect')
                    ->defaultValue('/waiting')
                ->end()
                ->scalarNode('pre_auth_failure_redirect')
                    ->defaultValue('/#partial_failure')
                ->end()
                ->scalarNode('success_redirect')
                    ->defaultValue('/')
                ->end()
                ->scalarNode('failure_redirect')
                    ->defaultValue('/#total_failure')
                ->end()
                ->arrayNode('remote_authorization')
                    ->children()
                        ->scalarNode('authorize_route')->defaultValue('rockz_email_auth_authorization_authorize')->end()
                        ->scalarNode('refuse_route')->defaultValue('rockz_email_auth_authorization_refuse')->end()
                        ->scalarNode('from_email')->defaultValue('changeme@example.com')->end()
                        ->scalarNode('template_email_authorize_login')->defaultValue('emails/authorization/login.html.twig')->end()
                    ->end()
                    ->end()
                ->end()
            ->end();
    }
}