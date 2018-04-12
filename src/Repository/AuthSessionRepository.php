<?php

namespace Rockz\EmailAuthBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Rockz\EmailAuthBundle\Entity\AuthSession;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AuthSessionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AuthSession::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('a')
            ->where('a.something = :value')->setParameter('value', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function save(AuthSession $authSession)
    {
        $this->_em->persist($authSession);
        $this->_em->flush();
    }
}
