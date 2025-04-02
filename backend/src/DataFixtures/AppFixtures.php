<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Group;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Currency;
use App\Entity\GroupMembership;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $currency = new Currency();
        $currency->setName('zł');
        $currency->setCode('PLN');
        $manager->persist($currency);

        $currency2 = new Currency();
        $currency2->setName('€');
        $currency2->setCode('EUR');
        $manager->persist($currency2);
        
        $manager->flush();
    }
}
