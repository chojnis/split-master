<?php

namespace App\Dto\User;

use App\Entity\User;
use App\Validator\UniqueEntityDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class UserUpdateDto {
    #[Groups(['user:update'])]
    public string $username;
    
    #[Groups(['user:update'])]
    public string $plainPassword;

    public function getUsername(): string {
        return $this->username;
    }

    public function getPlainPassword(): string {
        return $this->plainPassword;
    }
}