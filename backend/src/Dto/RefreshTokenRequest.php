<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RefreshTokenRequest
{
    #[Assert\NotBlank]
    public ?string $refresh_token = null;
}