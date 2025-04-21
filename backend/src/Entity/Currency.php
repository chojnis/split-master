<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    normalizationContext: ['groups' => ['currency:read']]
)]
#[GetCollection]
#[Get]
#[ORM\Entity(repositoryClass: 'App\Repository\CurrencyRepository')]
#[ORM\Table(name: 'currency')]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['currency:read', 'transaction:read', 'debt:read', 'group:read', 'settlement:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 3, unique: true)]
    #[Groups(['currency:read', 'currency:write', 'transaction:read', 'debt:read', 'group:read', 'settlement:read'])]
    private string $code;

    #[ORM\Column(type: 'string', length: 10)]
    #[Groups(['currency:read', 'currency:write', 'transaction:read', 'debt:read', 'group:read', 'settlement:read'])]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
