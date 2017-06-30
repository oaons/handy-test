<?php

namespace ApiBundle\Tests\Controller\Transaction;

use ApiBundle\Tests\ApiTestCase;
use ApiBundle\Tests\Fixtures\Controller\Deposit\TransferActionFixture;
use CoreBundle\Client\ApproveTransactionClientInterface;
use CoreBundle\Entity\Account;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\HttpFoundation\Request;

class TransferActionTest extends ApiTestCase
{

    /**
     * @var AbstractFixture
     */
    private $fixture;

    /**
     * @var Account
     */
    private $account1;

    /**
     * @var Account
     */
    private $account2;

    /**
     * @var Account
     */
    private $account3;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $approveTransactionClient = $this->createMock(ApproveTransactionClientInterface::class);
        $approveTransactionClient->method('isTransactionApproved')
            ->willReturn(true);
        $this->getContainer()->set('core.client.handy', $approveTransactionClient);
    }

    /**
     * Test transfer success same user accounts
     */
    public function testTransferSuccessSameUser()
    {
        $this->loadTestFixtures();

        $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/transfer', $this->account1->getId()),
                ['amount' => 12.22, 'to_account' => $this->account2->getId()]
            );
        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->refresh($this->account1);
        self::assertSame(288.23, $this->account1->getAmount());

        $this->getEntityManager()->refresh($this->account2);
        self::assertSame(10011.47, $this->account2->getAmount());

        self::assertJsonStringEqualsJsonString(<<<JSON
            {
                "id": "{$this->account1->getId()}",
                "amount": 288.23
            }

JSON
            ,
            $this->client->getResponse()->getContent()
        );
    }


    /**
     * Test transfer success different user accounts
     */
    public function testTransferSuccessDifferentUsers()
    {
        $this->loadTestFixtures();

        $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/transfer', $this->account1->getId()),
                ['amount' => 12.22, 'to_account' => $this->account3->getId()]
            );
        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->refresh($this->account1);
        self::assertSame(188.23, $this->account1->getAmount());

        $this->getEntityManager()->refresh($this->account3);
        self::assertSame(90113.52, $this->account3->getAmount());

        self::assertJsonStringEqualsJsonString(<<<JSON
            {
                "id": "{$this->account1->getId()}",
                "amount": 188.23
            }

JSON
            ,
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * Test transfer not approved
     */
    public function testTransferDifferentUsersNotApproved()
    {
        $approveTransactionClient = $this->createMock(ApproveTransactionClientInterface::class);
        $approveTransactionClient->method('isTransactionApproved')
            ->willReturn(false);
        $this->getContainer()->set('core.client.handy', $approveTransactionClient);

        $this->loadTestFixtures();

        $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/transfer', $this->account1->getId()),
                ['amount' => 12.22, 'to_account' => $this->account3->getId()]
            );
        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->refresh($this->account1);
        self::assertSame(300.45, $this->account1->getAmount());

        $this->getEntityManager()->refresh($this->account3);
        self::assertSame(90101.3, $this->account3->getAmount());

        self::assertJsonStringEqualsJsonString(<<<JSON
            {
                "code": 409,
                "message": "Transaction was not approved"
            }

JSON
            ,
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * Test transfer not approved
     */
    public function testTransferLimitExceed()
    {
        $approveTransactionClient = $this->createMock(ApproveTransactionClientInterface::class);
        $approveTransactionClient->method('isTransactionApproved')
            ->willReturn(true);
        $this->getContainer()->set('core.client.handy', $approveTransactionClient);

        $this->loadTestFixtures();

        $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/transfer', $this->account3->getId()),
                ['amount' => 12.22, 'to_account' => $this->account1->getId()]
            );
        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->refresh($this->account1);
        self::assertSame(300.45, $this->account1->getAmount());

        $this->getEntityManager()->refresh($this->account3);
        self::assertSame(90101.3, $this->account3->getAmount());

        self::assertJsonStringEqualsJsonString(<<<JSON
            {
                "code": 409,
                "message": "Daily transaction limit of 10000 funds exceed"
            }

JSON
            ,
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * Load test fixtures
     */
    private function loadTestFixtures()
    {
        $this->fixture = new TransferActionFixture();
        $this->loadFixtures($this->fixture);

        $this->account1 = $this->fixture->getReference('test_account_1');
        $this->account2 = $this->fixture->getReference('test_account_2');
        $this->account3 = $this->fixture->getReference('test_account_3');
        $this->getContainer()->get('core.model.transaction')->updateAccountAmount($this->account1);
        $this->getContainer()->get('core.model.transaction')->updateAccountAmount($this->account2);
        $this->getContainer()->get('core.model.transaction')->updateAccountAmount($this->account3);
    }
}
