<?php

namespace ApiBundle\Tests\Fixtures\Controller\Account;

use CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class OpenAccountActionFixture extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('test_user_1');

        $manager->persist($user);

        $manager->flush();

        $this->addReference('test_user_1', $user);
    }
}
