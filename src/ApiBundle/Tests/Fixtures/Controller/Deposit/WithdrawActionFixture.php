<?php

namespace ApiBundle\Tests\Fixtures\Controller\Deposit;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction\DepositTransaction;
use CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class WithdrawActionFixture extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('test_user_1');

        $account = new Account($user);
        $account->addTransaction(new DepositTransaction($account, 99.25));

        $manager->persist($user);
        $manager->persist($account);

        $manager->flush();

        $this->addReference('test_account_1', $account);
    }
}
