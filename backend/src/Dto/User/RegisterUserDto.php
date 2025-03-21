<?php

namespace App\Dto\User;

use App\Entity\User;
use App\Validator\UniqueEntityDto;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntityDto(field: 'email', entityClass: User::class, existsMessage: 'A user with this email address already exists')]
class RegisterUserDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[Groups(['user:create'])]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    #[Groups(['user:create'])]
    public string $password;
}