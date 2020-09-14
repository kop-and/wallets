<?php

namespace App\Tests\ControllerTests;


use App\Entity\Account;
use App\Tests\IntegrationTestBase;
use Symfony\Component\HttpFoundation\Response;

class AccountControllerTest extends IntegrationTestBase
{
    public function testGetAccounts()
    {
        $this->sendRequest('GET', self::$router->generate('/api/accounts/'));


        $this->assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
    }

    public function testTransactionAccounts()
    {
        $account1 = self::$entityManager->getRepository(Account::class)->findOneBy(['id' => 6]);
        $account2 = self::$entityManager->getRepository(Account::class)->findOneBy(['id' => 7]);

        $requestData = [
            "fromAccount" => $account1->getId(),
            "toAccount" => $account2->getId(),
            "amount" => 10,
        ];

        $this->sendRequest('PUT', self::$router->generate('/api/accounts/'), $requestData);

        $this->assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
    }
}