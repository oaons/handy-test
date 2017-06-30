<?php

namespace CoreBundle\Model;

use CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("core.model.user")
 */
class UserModel
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("doctrine.orm.entity_manager"),
     * })
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param User $user
     * @return User
     */
    public function updateUser(User $user): User
    {
        $this->objectManager->persist($user);

        return $user;
    }
}
