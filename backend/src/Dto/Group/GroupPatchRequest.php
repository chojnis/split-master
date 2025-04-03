<?php

namespace App\Dto\Group;

use Symfony\Component\Serializer\Annotation\Groups;

class GroupPatchRequest
{
    #[Groups(['group:write'])]
    public string $groupName;
    
    #[Groups(['group:write'])]
    public string $description;
    
    #[Groups(['group:write'])]
    public int $currencyId;
}