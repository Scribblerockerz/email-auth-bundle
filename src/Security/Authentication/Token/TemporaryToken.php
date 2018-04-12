<?php

namespace Rockz\EmailAuthBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TemporaryToken extends AbstractToken implements TokenInterface
{
    private $providerKey;

    /**
     * @param string $email
     * @param string $providerKey The provider key
     */
    public function __construct(string $email, string $providerKey)
    {
        parent::__construct([]);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->setUser($email);
        $this->providerKey = $providerKey;
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
        return '';
    }
}