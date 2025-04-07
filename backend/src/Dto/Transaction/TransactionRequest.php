<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Currency;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;


class TransactionRequest
{
    #[Groups(['transaction:write'])]
    #[Assert\NotBlank(message: 'Nazwa transakcji nie może być pusta.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Nazwa transakcji nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    public string $name;

    #[Groups(['transaction:write'])]
    #[Assert\NotBlank(message: 'Wartość nie może być pusta.')]
    #[Assert\Positive(message: 'Wartość musi być większa od 0.')]
    #[Assert\LessThanOrEqual(
        value: 999999.99,
        message: 'Wartość nie może przekroczyć limitu: {{ compared_value }}.'
    )]
    public float $amount;

    #[Groups(['transaction:write'])]
    #[Assert\NotNull(message: 'Waluta jest obowiązkowa.')]
    public int $currencyId;

    #[Groups(['transaction:write'])]
    #[Assert\NotNull(message: 'Do transakcji musi być przypisany płatnik.')]
    public int $payerId;

    #[Groups(['transaction:write'])]
    #[Assert\NotBlank(message: 'Transakcja musi mieć co najmniej jednego odbiorcę.')]
    #[Assert\Count(
        min: 1,
        minMessage: 'Transakcja musi mieć co najmniej jednego odbiorcę.'
    )]
    public array $payeesIds;

    #[Groups(['transaction:write'])]
    #[Assert\Positive(message: 'Kurs wymiany musi być większy od 0.')]
    #[Assert\LessThanOrEqual(
        value: 9999.999999,
        message: 'Kurs wymiany nie może przekroczyć limitu: {{ compared_value }}.'
    )]
    public ?float $exchangeRate = null;

    #[Groups(['transaction:write'])]
    #[Assert\Type(\DateTime::class)]
    #[Assert\LessThanOrEqual("today", message: 'Data transakcji nie może być w przyszłości.')]
    public ?\DateTime $transactionDate = null;
}