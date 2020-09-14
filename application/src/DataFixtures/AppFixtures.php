<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Commission;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail('test' . $i . '@email.com');
            $user->setName('test name' . $i);
            $user->setPassword('password' . $i);
            $user->setEnabled(true);
            $user->setUsername('username' . $i);
            $user->setRoles(['']);
            $manager->persist($user);

            $account = new Account();
            $account->setUser($user);
            $account->setNumber("$i$i$i$i$i$i$i" . rand(100000, 999999));
            $account->setAmount(rand(50, 100));
            $manager->persist($account);
        }

        $commission = new Commission();
        $commission->setType(Commission::TYPE_TRANSACTION_USER);
        $commission->setValue(0.015);
        $manager->persist($commission);

        $manager->flush();
    }
}
