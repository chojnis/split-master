<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\Metadata\Post;
use App\State\RefreshTokenProcessor;
use App\Dto\RefreshTokenRequest;

#[ApiResource]
#[Post(
    uriTemplate: '/login/refresh',
    openapi: new Operation(
        summary: 'Refresh JWT token',
        description: 'Generates a new JWT token using a refresh token.',
        responses: [
            '200' => [
                'description' => 'JWT token refreshed',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'token' => ['type' => 'string'],
                                'refresh_token' => ['type' => 'string'],
                                'user' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'username' => ['type' => 'string'],
                                        'email' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ),
    input: RefreshTokenRequest::class,
    processor: RefreshTokenProcessor::class
)]
#[ORM\Entity]
#[ORM\Table(name: 'refresh_token')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $valid = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getValid(): ?\DateTimeInterface
    {
        return $this->valid;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function setValid(\DateTimeInterface $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __toString(): string
    {
        return $this->refreshToken;
    }
}
