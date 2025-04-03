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
use App\Service\GroupMembershipService;


class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private GroupMembershipService $groupMembershipService,
    ) {}

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

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $faker = Factory::create();
        for ($i = 0; $i < 35; $i++) {
            $group = new Group();
            $group->setOwner($user);
            $group->setGroupName($faker->word);
            $group->setDescription($faker->sentence);
            $group->setCurrency($currency);
            $manager->persist($group);
            $this->groupMembershipService->ensureMembership($user, $group);
        }
        
        $manager->flush();
    }
}
