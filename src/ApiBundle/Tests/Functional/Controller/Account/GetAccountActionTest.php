<?php

namespace ApiBundle\Tests\Controller\Account;

use ApiBundle\Tests\ApiTestCase;
use ApiBundle\Tests\Fixtures\Controller\Account\GetAccountActionActionFixture;
use Symfony\Component\HttpFoundation\Request;

class GetAccountActionTest extends ApiTestCase
{
    /**
     * Test get account success
     */
    public function testGetSuccess()
    {
        $fixtures = new GetAccountActionActionFixture();
        $this->loadFixtures($fixtures);

        $account = $fixtures->getReference('test_account_1');

        $this->getContainer()->get('core.model.transaction')->updateAccountAmount($account);
        $this->getEntityManager()->clear();

        $this->client
            ->request(Request::METHOD_GET, sprintf('/api/accounts/%s', $account->getId()));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJsonStringEqualsJsonString(<<<JSON
            {
                "id": "{$account->getId()}",
                "amount": 99.25
            }
JSON
            ,
            $this->client->getResponse()->getContent()
        );
    }
}
