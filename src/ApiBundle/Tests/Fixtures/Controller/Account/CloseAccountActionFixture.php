<?php

namespace ApiBundle\Tests\Fixtures\Controller\Account;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class CloseAccountActionFixture extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('test_user_1');

        $account = new Account($user);

        $manager->persist($user);
        $manager->persist($account);

        $manager->flush();

        $this->addReference('test_account_1', $account);
    }
}
