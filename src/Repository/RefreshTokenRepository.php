<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findValidToken(string $token): ?RefreshToken
    {
        return $this->createQueryBuilder('rt')
            ->where('rt.token = :token')
            ->andWhere('rt.revoked = :revoked')
            ->andWhere('rt.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findValidTokensForUser(User $user): array
    {
        return $this->createQueryBuilder('rt')
            ->where('rt.user = :user')
            ->andWhere('rt.revoked = :revoked')
            ->andWhere('rt.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function revokeAllUserTokens(User $user): void
    {
        $this->createQueryBuilder('rt')
            ->update()
            ->set('rt.revoked', ':revoked')
            ->where('rt.user = :user')
            ->andWhere('rt.revoked = :notRevoked')
            ->setParameter('user', $user)
            ->setParameter('revoked', true)
            ->setParameter('notRevoked', false)
            ->getQuery()
            ->execute();
    }
} 