<?php

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
class ApiKey
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private UserInterface $user;

    #[ORM\Column(length: 64)]
    private string $token;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private bool $revoked = false;

    public function __construct(UserInterface $user)
    {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->token = bin2hex(random_bytes(32));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): void
    {
        $this->revoked = true;
    }
} 