<?php

namespace ApiBundle\Tests\Fixtures\Controller\Deposit;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction\DepositTransaction;
use CoreBundle\Entity\Transaction\TransferDepositTransaction;
use CoreBundle\Entity\Transaction\TransferWithdrawTransaction;
use CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class TransferActionFixture extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user1 = new User();
        $user1->setName('test_user_1');

        $user2 = new User();
        $user2->setName('test_user_2');

        $account1 = new Account($user1);
        $account1->addTransaction(new DepositTransaction($account1, 300.45));

        $account2 = new Account($user1);
        $account3 = new Account($user2);
        $account2->addTransaction(new TransferDepositTransaction($account2, $account3, 9999.25));
        $account3->addTransaction(new DepositTransaction($account3, 100100.55));
        $account3->addTransaction(new TransferWithdrawTransaction($account3, $account2, 9999.25));

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($account1);
        $manager->persist($account2);
        $manager->persist($account3);

        $manager->flush();

        $this->addReference('test_account_1', $account1);
        $this->addReference('test_account_2', $account2);
        $this->addReference('test_account_3', $account3);
    }
}
