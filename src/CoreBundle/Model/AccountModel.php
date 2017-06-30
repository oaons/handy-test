<?php

namespace CoreBundle\Model;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("core.model.account")
 */
class AccountModel
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "userModel" = @DI\Inject("core.model.user")
     * })
     * @param ObjectManager $objectManager
     * @param UserModel $userModel
     */
    public function __construct(ObjectManager $objectManager, UserModel $userModel)
    {
        $this->objectManager = $objectManager;
        $this->userModel = $userModel;
    }

    /**
     * @param User $user
     * @return Account
     */
    public function openUserAccount(User $user): Account
    {
        $this->userModel->updateUser($user);

        return $this->addUserAccount($user);
    }

    /**
     * @param Account $account
     */
    public function closeAccount(Account $account)
    {
        $this->objectManager->remove($account);
    }

    /**
     * @param User $user
     * @return Account
     */
    public function addUserAccount(User $user): Account
    {
        $account = new Account($user);
        $this->objectManager->persist($account);

        return $account;
    }
}
