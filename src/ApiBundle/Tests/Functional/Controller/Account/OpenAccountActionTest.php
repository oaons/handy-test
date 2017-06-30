<?php

namespace ApiBundle\Tests\Controller\Account;

use ApiBundle\Tests\ApiTestCase;
use ApiBundle\Tests\Fixtures\Controller\Account\OpenAccountActionFixture;
use CoreBundle\Entity\Account;
use CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class OpenAccountActionTest extends ApiTestCase
{
    /**
     * Test account successfully created
     */
    public function testAccountCreated()
    {
        $this->clearDb();

        $this->client
            ->request(Request::METHOD_POST, '/api/accounts', ['name' => 'test_user']);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $users = $this->getEntityManager()->getRepository(User::class)->findAll();
        self::assertCount(1, $users);
        /** @var User $user */
        $user = $users[0];
        self::assertSame('test_user', $user->getName());

        $accounts = $this->getEntityManager()->getRepository(Account::class)->findAll();
        self::assertCount(1, $users);
        /** @var Account $account */
        $account = $accounts[0];
        self::assertSame($user, $account->getUser());
        self::assertEquals(0, $account->getAmount());

        self::assertJsonStringEqualsJsonString(<<<EOL
            {
                "id": "{$account->getId()}",
                "amount": 0,
                "user": {
                    "id": "{$user->getId()}",
                    "name": "test_user"
                }
            }
EOL
            ,
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @dataProvider validationFailedProvider
     */
    public function testValidationFailed(array $parameters, string $response)
    {
        $fixtures = new OpenAccountActionFixture();
        $this->loadFixtures($fixtures);

        $this->client
            ->request(Request::METHOD_POST, '/api/accounts', $parameters);

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJsonStringEqualsJsonString($response, $this->client->getResponse()->getContent());
    }

    /**
     * @return array
     */
    public static function validationFailedProvider(): array
    {
        return [
            'empty name' => [
                'parameters' => [],
                'response' => <<<EOL
                    {
                        "code": 400,
                        "message": "Validation Failed",
                        "errors": {
                            "children": {
                                "name": {
                                    "errors": ["This value should not be blank."]
                                }
                            }
                        }
                    }
EOL
            ],
            'uniq name' => [
                'parameters' => ['name' => 'test_user_1'],
                'response' => <<<EOL
                    {
                        "code": 400,
                        "message": "Validation Failed",
                        "errors": {
                            "children": {
                                "name": {
                                    "errors": ["This value is already used."]
                                }
                            }
                        }
                    }
EOL
            ],
        ];
    }
}
