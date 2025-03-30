<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionRequest
{
    #[Assert\NotBlank(message: 'Nazwa transakcji nie może być pusta.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Nazwa transakcji nie może przekroczyć ilości znaków: {{ limit }}.'
    )]
    public string $name;

    #[Assert\NotBlank(message: 'Wartość nie może być pusta.')]
    #[Assert\Positive(message: 'Wartość musi być większa od 0.')]
    #[Assert\LessThanOrEqual(
        value: 999999.99,
        message: 'Wartość nie może przekroczyć limitu: {{ compared_value }}.'
    )]
    public float $amount;

    #[Assert\NotNull(message: 'Waluta jest obowiązkowa.')]
    public int $currencyId;

    #[Assert\NotNull(message: 'Do transakcji musi być przypisany płatnik.')]
    public int $payerId;

    #[Assert\NotBlank]
    #[Assert\Count(
        min: 1,
        minMessage: 'Transakcja musi mieć co najmniej jednego odbiorcę.'
    )]
    #[Assert\All([
        new Assert\Type('integer', message: 'Każdy odbiorca musi być określony przez ID (liczba całkowita).')
    ])]
    public array $payeesIds;
}