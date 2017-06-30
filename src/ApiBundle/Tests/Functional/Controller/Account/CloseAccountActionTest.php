<?php

namespace ApiBundle\Tests\Controller\Account;

use ApiBundle\Tests\ApiTestCase;
use ApiBundle\Tests\Fixtures\Controller\Account\CloseAccountActionFixture;
use CoreBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Request;

class CloseAccountActionTest extends ApiTestCase
{
    /**
     * Test account successfully closed
     */
    public function testAccountClosed()
    {
        $fixtures = new CloseAccountActionFixture();
        $this->loadFixtures($fixtures);

        $id = $fixtures->getReference('test_account_1')->getId();
        $this->client
            ->request(Request::METHOD_DELETE, sprintf('/api/accounts/%s', $id));

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->clear();

        $account = $this->getEntityManager()->getRepository(Account::class)->find($id);

        self::assertNull($account);
    }
}
