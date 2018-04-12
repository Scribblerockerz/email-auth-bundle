<?php

namespace Rockz\EmailAuthBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PendingAuthenticationToken extends AbstractToken implements TokenInterface
{
    private $credentials;
    private $providerKey;

    /**
     * @param string|object   $user        The user can be a UserInterface instance, or an object implementing a __toString method or the username as a regular string
     * @param mixed           $credentials The user credentials
     * @param string          $providerKey The provider key
     * @param array           $roles       An array of roles
     */
    public function __construct($user, $credentials, string $providerKey, array $roles = array())
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->setUser($user);
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
        $this->setAuthenticated(true);
    }

    /**
     * Never return real roles for this token!
     *
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * Returns the provider key.
     *
     * @return string The provider key
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->credentials, $this->providerKey, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->credentials, $this->providerKey, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}