<?php

namespace Rockz\EmailAuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Rockz\EmailAuthBundle\Repository\AuthSessionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AuthSession
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $clientHash;

    /**
     * @ORM\Column(type="text")
     */
    protected $authorizationHash;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClientHash(): string
    {
        return $this->clientHash;
    }

    /**
     * @param string $hash
     * @return AuthSession
     */
    public function setClientHash(string $hash): AuthSession
    {
        $this->clientHash = $hash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthorizationHash(): string
    {
        return $this->authorizationHash;
    }

    /**
     * @param mixed $authorizationHash
     * @return AuthSession
     */
    public function setAuthorizationHash(string $authorizationHash): AuthSession
    {
        $this->authorizationHash = $authorizationHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return AuthSession
     */
    public function setStatus(string $status): AuthSession
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function updateTimestamps()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return AuthSession
     */
    public function setCreatedAt(\DateTime $createdAt): AuthSession
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return AuthSession
     */
    public function setUpdatedAt(\DateTime $updatedAt): AuthSession
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
