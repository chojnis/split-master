<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class GroupMembershipInviteDto 
{
    #[Groups(['group_membership:create'])]
    #[Assert\NotBlank]
    public string $email;

    public function getEmail(): string
    {
        return $this->email;
    }
}