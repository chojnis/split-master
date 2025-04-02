<?php

namespace App\Dto\Group;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class CreateGroupRequest
{
    #[Groups(['group:write'])]
    #[Assert\NotBlank(message: 'Nazwa grupy nie może być pusta.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Nazwa grupy nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    public ?string $groupName = null;
    
    #[Groups(['group:write'])]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Opis nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    public ?string $description = null;
    
    #[Groups(['group:write'])]
    #[Assert\NotBlank(message: 'Waluta jest obowiązkowa.')]
    public ?int $currencyId = null;
}