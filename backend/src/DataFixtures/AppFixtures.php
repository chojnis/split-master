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

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $users[] = $user;
        }

        $groups = [];
        for ($i = 0; $i < 5; $i++) {
            $group = new Group();
            $group->setGroupName($faker->company);
            $group->setDescription($faker->sentence);
            $group->setOwner($users[$i]);
            $manager->persist($group);
            $groups[] = $group;
        }

        $currency = new Currency();
        $currency->setName('zł');
        $currency->setCode('PLN');
        $manager->persist($currency);

        for ($i = 0; $i < 50; $i++) {
            $transaction = new Transaction();
            $transaction->setName($faker->sentence);
            $transaction->setAmount($faker->randomFloat(2, 1, 1000));
            $transaction->setPayer($users[$faker->numberBetween(0, 9)]);
            $transaction->setGroup($groups[$faker->numberBetween(0, 4)]);
            $transaction->addPayee($users[$faker->numberBetween(0, 9)]);
            $transaction->setCurrency($currency);
            $manager->persist($transaction);
        }

        $manager->flush();
    }
}
