<?php

namespace App\DataFixtures;

use App\Entity\Wallet;
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

            $wallet = new Wallet();
            $wallet->setUser($user);
            $wallet->setNumber(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
            $wallet->setAmount(rand(50, 100));
            $manager->persist($wallet);
        }

        $commission = new Commission();
        $commission->setType(Commission::TYPE_TRANSACTION_USER);
        $commission->setValue(15);
        $manager->persist($commission);

        $manager->flush();
    }
}
