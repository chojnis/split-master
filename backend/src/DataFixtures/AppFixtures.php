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
use App\Service\GroupService;
use App\Dto\Transaction\TransactionRequest;
use App\Service\TransactionService;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private GroupService $groupService,
        private TransactionService $transactionService,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $currencies = [
            ['name' => 'zł', 'code' => 'PLN'],
            ['name' => '€', 'code' => 'EUR'],
            ['name' => '$', 'code' => 'USD'],
        ];

        $currenciesObj = [];

        foreach ($currencies as $currencyData) {
            $currency = new Currency();
            $currency->setName($currencyData['name']);
            $currency->setCode($currencyData['code']);
            $manager->persist($currency);
            $currenciesObj[] = $currency;
        }

        $users = ['pierwszy@user.com', 'drugi@user.com', 'trzeci@user.com'];
        $usersObj = [];
        foreach ($users as $userEmail) {
            $user = new User();
            $user->setEmail($userEmail);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $usersObj[] = $user;
        }

        $groups = [
            ['groupName' => 'Weekend nad morzem'],
            ['groupName' => 'Wakacje we Włoszech', 'description' => 'Rzym'],
            ['groupName' => 'Wyjazd do USA'],
        ];
        $groupsObj = [];
        foreach ($groups as $key=>$groupData) {
            $group = new Group();
            $group->setGroupName($groupData['groupName']);
            if(isset($groupData['description'])) {
                $group->setDescription($groupData['description']);
            }
            $group->setOwner($usersObj[$key]);
            $group->setCurrency($currenciesObj[$key]);
            $manager->persist($group);
            $groupsObj[] = $group;
            $this->groupService->ensureMembership($usersObj[$key], $group);
        }

        foreach ($usersObj as $user) {
            foreach ($groupsObj as $group) {
                $this->groupService->ensureMembership($user, $group);
            }
        }

        // fake transactions
        $faker = Factory::create();
        foreach ($groupsObj as $group) {
            for ($i = 0; $i < 10; $i++) {
                $transaction = new TransactionRequest();
                $transaction->name = $faker->sentence(3);
                $transaction->amount = $faker->randomFloat(2, 1, 1000);
                $transaction->currencyId = $group->getCurrency()->getId();
                $transaction->payerId = $usersObj[array_rand($usersObj)]->getId();
                $transaction->payeesIds = array_map(fn($user) => $user->getId(), $usersObj);
                $transaction->transactionDate = $faker->dateTimeBetween('-1 month', 'now');

                $this->transactionService->createTransaction($transaction, $group);
            }
        }
        
        $manager->flush();
    }
}
