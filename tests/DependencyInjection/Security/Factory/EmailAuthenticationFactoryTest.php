<?php

namespace Tests\Rockz\EmailAuthBundle\DependencyInjection\Security\Factory;

use PHPUnit\Framework\TestCase;
use Rockz\EmailAuthBundle\DependencyInjection\Security\Factory\EmailAuthenticationFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EmailAuthenticationFactoryTest extends TestCase
{
    public function testCreate()
    {
        $config = array(
//            'use_forward' => true,
            'remember_me' => true,
            'email_parameter' => 'email_auth',
            'initial_redirect' => '/access',
            'pre_auth_success_redirect' => '/waiting',
            'pre_auth_failure_redirect' => '/#partial_failure',
            'success_redirect' => '/',
            'failure_redirect' => '/#total_failure',
            'csrf_protection' => false,
        );

        list($container, $authProviderId, $listenerId, $entryPointId) = $this->callFactory('foo', $config, 'user_provider', 'entry_point');



        // user provider
        // ----------------------------------------------------------------

        // user provider has right id
        $this->assertEquals(
            'security.authentication.rockz_email_auth_provider.foo',
            $authProviderId
        );

        // has user provider definition
        $this->assertTrue(
            $container->hasDefinition('security.authentication.rockz_email_auth_provider.foo'),
            'container must have a definition for given user provider id'
        );

        // check user provider definition
        $definition = $container->getDefinition('security.authentication.rockz_email_auth_provider.foo');
        $this->assertEquals(array(
            '$userProvider' => new Reference('user_provider'),
            '$providerKey' => 'foo',
            '$remoteAuthorizer' => new Reference('rockz_email_auth.remote_authorization.remote_authorizer.foo'),
        ), $definition->getArguments());



        // listener
        // ----------------------------------------------------------------

        // listener has right id
        $this->assertEquals(
            'security.authentication.rockz_email_auth_listener.foo',
            $listenerId
        );

        // has listener definition
        $this->assertTrue(
            $container->hasDefinition('security.authentication.rockz_email_auth_listener.foo'),
            'container must have a definition for given listener id'
        );

        // check listener definition
        $definition = $container->getDefinition('security.authentication.rockz_email_auth_listener.foo');
        $this->assertEquals(array(
            '$providerKey' => 'foo',
            '$emailParameter' => 'email_auth',
            '$preAuthenticationSuccessHandler' => new Reference('rockz_email_auth.security_http_authentication.email_authentication_pre_auth_success_handler.foo'),
            '$preAuthenticationFailureHandler' => new Reference('rockz_email_auth.security_http_authentication.email_authentication_pre_auth_failure_handler.foo'),
            '$authenticationSuccessHandler' => new Reference('rockz_email_auth.security_http_authentication.email_authentication_success_handler.foo'),
            '$authenticationFailureHandler' => new Reference('rockz_email_auth.security_http_authentication.email_authentication_failure_handler.foo'),
            '$options' => $config,
        ), $definition->getArguments());



        // entry point
        // ----------------------------------------------------------------

        // entry point has right id
        $this->assertEquals(
            'security.authentication.rockz_email_auth_entry_point.foo',
            $entryPointId
        );

        // has entry point definition
        $this->assertTrue(
            $container->hasDefinition('security.authentication.rockz_email_auth_entry_point.foo'),
            'container must have a definition for given entry point id'
        );

        // check entry point definition
        $definition = $container->getDefinition('security.authentication.rockz_email_auth_entry_point.foo');
        $this->assertEquals(array(
            '$httpUtils' => new Reference('security.http_utils'),
            '$redirectPath' => '/access',
        ), $definition->getArguments());
    }

    public function getHandlers()
    {
        return array(

            // test default pre auth success handler
            array(
                'pre_auth_success_handler',
                null,
                '$preAuthenticationSuccessHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_pre_auth_success_handler.foo'
            ),

            // test custom pre auth success handler
            array(
                'pre_auth_success_handler',
                'custom_pre_auth_success_handler',
                '$preAuthenticationSuccessHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_pre_auth_success_handler.foo'
            ),

            // test default pre auth failure handler
            array(
                'pre_auth_failure_handler',
                null,
                '$preAuthenticationFailureHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_pre_auth_failure_handler.foo'
            ),

            // test custom pre auth failure handler
            array(
                'pre_auth_failure_handler',
                'custom_pre_auth_failure_handler',
                '$preAuthenticationFailureHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_pre_auth_failure_handler.foo'
            ),


            // test default success handler
            array(
                'success_handler',
                null,
                '$authenticationSuccessHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_success_handler.foo'
            ),

            // test custom success handler
            array(
                'success_handler',
                'custom_success_handler',
                '$authenticationSuccessHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_success_handler.foo'
            ),

            // test default failure handler
            array(
                'failure_handler',
                null,
                '$authenticationFailureHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_failure_handler.foo'
            ),

            // test custom failure handler
            array(
                'failure_handler',
                'custom_failure_handler',
                '$authenticationFailureHandler',
                'rockz_email_auth.security_http_authentication.email_authentication_failure_handler.foo'
            ),
        );
    }

    /**
     * This test is responsible to check if the default or custom handlers
     * are setup properly with their respective ids
     *
     * @dataProvider getHandlers
     */
    public function testDefaultHandlerConfiguration($handlerKey, $handlerServiceId, $testedArgument, $expectedServiceId)
    {
        $options = array(
            'remember_me' => true,
            'email_parameter' => 'email_auth',
            'initial_redirect' => '/access',
            'pre_auth_success_redirect' => '/please-wait-right-here',
            'pre_auth_failure_redirect' => '/something-went-wrong',
            'success_redirect' => '/woop-woop',
            'failure_redirect' => '/damn',
            'csrf_protection' => false,
        );

        if ($handlerServiceId) {
            $options[$handlerKey] = $handlerServiceId;
        }

        list($container, $authProviderId, $listenerId, $entryPointId) = $this->callFactory('foo', $options, 'user_provider', 'entry_point');

        $definition = $container->getDefinition($listenerId);
        $arguments = $definition->getArguments();

        $this->assertEquals(
            new Reference($expectedServiceId),
            $arguments[$testedArgument],
            'There must be a handler anyway! (default or not)'
        );
    }

    public function testGetPosition()
    {
        $factory = new EmailAuthenticationFactory();
        $this->assertSame('pre_auth', $factory->getPosition());
    }

    public function testGetKey()
    {
        $factory = new EmailAuthenticationFactory();
        $this->assertSame('rockz_email_auth', $factory->getKey());
    }

    public function getValidConfigurationTests()
    {
        $tests = array();

        // completely basic
        $tests[] = array(
            array(),
            array(
                'remember_me' => true,
                'email_parameter' => 'email_auth',
                'initial_redirect' => '/access',
                'pre_auth_success_redirect' => '/waiting',
                'pre_auth_failure_redirect' => '/#partial_failure',
                'success_redirect' => '/',
                'failure_redirect' => '/#total_failure',
                'csrf_protection' => false,
                'csrf_token_id' => 'rockz_email_auth_authenticate',
                'csrf_parameter' => '_csrf_token',
            ),
        );

        // custom handler
        $tests[] = array(
            array(
                'email_parameter' => 'blub',
                'initial_redirect' => '/foo',
                'pre_auth_success_handler' => 'foo',
                'pre_auth_failure_handler' => 'bar',
                'success_handler' => 'baz',
                'failure_handler' => 'toot',
                'pre_auth_success_redirect' => '/please-wait-right-here',
                'pre_auth_failure_redirect' => '/something-went-wrong',
                'success_redirect' => '/woop-woop',
                'failure_redirect' => '/damn',
                'csrf_protection' => true,
                'csrf_token_id' => 'authenticate',
                'csrf_parameter' => 'token',
            ),
            array(
                'remember_me' => true,
                'email_parameter' => 'blub',
                'initial_redirect' => '/foo',
                'pre_auth_success_handler' => 'foo',
                'pre_auth_failure_handler' => 'bar',
                'success_handler' => 'baz',
                'failure_handler' => 'toot',
                'pre_auth_success_redirect' => '/please-wait-right-here',
                'pre_auth_failure_redirect' => '/something-went-wrong',
                'success_redirect' => '/woop-woop',
                'failure_redirect' => '/damn',
                'csrf_protection' => true,
                'csrf_token_id' => 'authenticate',
                'csrf_parameter' => 'token',
            ),
        );

        return $tests;
    }

    /**
     * @dataProvider getValidConfigurationTests
     */
    public function testConfigurationOptions($inputConfig, $expectedConfig)
    {
        $factory = new EmailAuthenticationFactory();

        $nodeDefinition = new ArrayNodeDefinition('rockz_email_auth');
        $factory->addConfiguration($nodeDefinition);

        $node = $nodeDefinition->getNode();
        $normalizedConfig = $node->normalize($inputConfig);
        $finalizedConfig = $node->finalize($normalizedConfig);

        $this->assertEquals($expectedConfig, $finalizedConfig);
    }

    protected function callFactory($id, $config, $userProviderId, $defaultEntryPointId)
    {
        $factory = new EmailAuthenticationFactory();

        $container = new ContainerBuilder();
        $container->register('rockz_email_auth.security_authentication_provider.email_authentication_provider');

        list($authProviderId, $listenerId, $entryPointId) = $factory->create($container, $id, $config, $userProviderId, $defaultEntryPointId);

        return array($container, $authProviderId, $listenerId, $entryPointId);
    }
}