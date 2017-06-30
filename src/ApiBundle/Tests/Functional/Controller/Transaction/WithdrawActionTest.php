<?php

namespace ApiBundle\Tests\Controller\Transaction;

use ApiBundle\Tests\ApiTestCase;
use ApiBundle\Tests\Fixtures\Controller\Deposit\WithdrawActionFixture;
use CoreBundle\Entity\Account;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\HttpFoundation\Request;

class WithdrawActionTest extends ApiTestCase
{

    /**
     * @var AbstractFixture
     */
    private $fixture;

    /**
     * @var Account
     */
    private $account;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new WithdrawActionFixture();
        $this->loadFixtures($this->fixture);

        $this->account = $this->fixture->getReference('test_account_1');
        $this->getContainer()->get('core.model.transaction')->updateAccountAmount($this->account);
    }
    /**
     * Test withdraw account success
     */
    public function testWithdrawSuccess()
    {
        $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/withdraw', $this->account->getId()),
                ['amount' => 12.22]
            );
        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->refresh($this->account);
        self::assertSame(87.03, $this->account->getAmount());

        self::assertJsonStringEqualsJsonString(<<<JSON
            {
                "id": "{$this->account->getId()}",
                "amount": 87.03
            }

JSON
            ,
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @dataProvider validationFailedProvider
     * @param array $parameters
     * @param string $response
     */
    public function testValidationFailed(array $parameters, string $response)
    {
       $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/withdraw', $this->account->getId()),
                $parameters
            );

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJsonStringEqualsJsonString($response, $this->client->getResponse()->getContent());
    }

    /**
     * Test amount exceed
     */
    public function testAmountExceed()
    {
       $this->client
            ->request(
                Request::METHOD_POST,
                sprintf('/api/accounts/%s/withdraw', $this->account->getId()),
                ['amount' => 200]
            );
        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        $this->getEntityManager()->refresh($this->account);
        self::assertSame(99.25, $this->account->getAmount());
    }

    /**
     * @return array
     */
    public static function validationFailedProvider(): array
    {
        return [
            'empty parameters' => [
                'parameters' => [],
                'response' => <<<JSON
                    {
                      "code": 400,
                      "message": "Validation Failed",
                      "errors": {
                        "children": {
                          "amount": {
                            "errors": [
                              "This value should not be blank."
                            ]
                          }
                        }
                      }
                    }
JSON
            ],
            'zero amount' => [
                'parameters' => ['amount' => 0],
                'response' => <<<JSON
                    {
                      "code": 400,
                      "message": "Validation Failed",
                      "errors": {
                        "children": {
                          "amount": {
                            "errors": [
                              "This value should be 0.0001 or more."
                            ]
                          }
                        }
                      }
                    }
JSON
            ],
        ];
    }
}
