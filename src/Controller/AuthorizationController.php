<?php

namespace Rockz\EmailAuthBundle\Controller;

use Rockz\EmailAuthBundle\RemoteAuthorization\RemoteAuthorizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationController extends AbstractController
{
    protected $remoteAuthorizer;

    public function __construct(RemoteAuthorizer $remoteAuthorizer)
    {
        $this->remoteAuthorizer = $remoteAuthorizer;
    }

    /**
     * @param string $authorizationHash
     * @return Response
     * @throws \Rockz\EmailAuthBundle\RemoteAuthorization\Exception\SessionNotFoundException
     */
    public function authorize(string $authorizationHash)
    {
        $this->remoteAuthorizer->authorizeRequest($authorizationHash);
        return $this->render('@RockzEmailAuth/email_login/authorize.html.twig');
    }

    /**
     * @param string $authorizationHash
     * @return Response
     * @throws \Rockz\EmailAuthBundle\RemoteAuthorization\Exception\SessionNotFoundException
     */
    public function refuse(string $authorizationHash)
    {
        $this->remoteAuthorizer->refuseRequest($authorizationHash);
        return $this->render('@RockzEmailAuth/email_login/refuse.html.twig');
    }
}