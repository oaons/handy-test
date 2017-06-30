<?php

namespace ApiBundle\Controller;

use ApiBundle\Exception\FormInvalidException;
use ApiBundle\Form\FormHandler;
use ApiBundle\Form\OpenAccountType;
use CoreBundle\Entity\Account;
use CoreBundle\Entity\User;
use CoreBundle\Model\AccountModel;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class AccountController
{
    /**
     * @DI\Inject("core.model.account")
     * @var AccountModel
     */
    private $accountModel;

    /**
     * @DI\Inject("api.form.handler")
     * @var FormHandler
     */
    private $formHandler;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @ApiDoc(
     *   description = "Open new user account",
     *   statusCodes = {201 = "OK", 400 = "Validation failed"},
     *   input = {"class" = "ApiBundle\Form\OpenAccountType", "name" = ""},
     *   output = {"class" = "CoreBundle\Entity\Account", "groups" = {"AccountCreate"}},
     *   section = "Account"
     * )
     * @Rest\Post(path="/accounts")
     * @Rest\View(statusCode=201, serializerGroups={"AccountCreate"})
     * @param Request $request
     * @throws FormInvalidException
     * @return Account
     */
    public function openAccountAction(Request $request): Account
    {
        $form = $this->formHandler->handleRequest($request, OpenAccountType::class);

        $user = $form->getData();
        $account = $this->accountModel->openUserAccount($user);

        $this->objectManager->flush();

        return $account;
    }

    /**
     * @ApiDoc(
     *   description = "Get account information",
     *   statusCodes = {200 = "OK", 404 = "Account not found"},
     *   output = {"class" = "CoreBundle\Entity\Account", "groups" = {"Default"}},
     *   section = "Account"
     * )
     * @Rest\Get(path="/accounts/{account}")
     * @Rest\View(statusCode=200, serializerGroups={"Default"})
     * @ParamConverter("account", class="CoreBundle:Account", options={"id" = "account"})
     * @param Account $account
     * @return Account
     */
    public function getAccountAction(Account $account): Account
    {
        return $account;
    }

    /**
     * @ApiDoc(
     *   description = "Close account",
     *   statusCodes = {204 = "OK", 404 = "Account not found"},
     *  section = "Account"
     * )
     * @Rest\Delete(path="/accounts/{account}")
     * @Rest\View(statusCode=204)
     * @ParamConverter("account", class="CoreBundle:Account", options={"id" = "account"})
     * @param Account $account
     */
    public function closeAccountAction(Account $account)
    {
        $this->accountModel->closeAccount($account);
        $this->objectManager->flush();
    }

    /**
     * @ApiDoc(
     *   description = "Get list of user accounts",
     *   statusCodes = {200 = "OK", 404 = "User not found"},
     *   output = {"class" = "array<CoreBundle\Entity\Account>", "groups" = {"Default"}},
     *   section = "Account"
     * )
     * @Rest\Get(path="/users/{user}/accounts")
     * @Rest\View(statusCode=200, serializerGroups={"Default"})
     * @ParamConverter("user", class="CoreBundle:User", options={"id" = "user"})
     * @param User $user
     * @return Account[]
     */
    public function getUserAccountsAction(User $user)
    {
        return $user->getAccounts();
    }

    /**
     * @ApiDoc(
     *   description = "Add new account to existed user",
     *   statusCodes = {200 = "OK", 404 = "User not found"},
     *   output = {"class" = "CoreBundle\Entity\Account", "groups" = {"Default"}},
     *   section = "Account"
     * )
     * @Rest\Post(path="/users/{user}/accounts")
     * @Rest\View(statusCode=201, serializerGroups={"Default"})
     * @ParamConverter("user", class="CoreBundle:User", options={"id" = "user"})
     * @param User $user
     * @return Account
     */
    public function addUserAccountAction(User $user)
    {
        $account = $this->accountModel->addUserAccount($user);
        $this->objectManager->flush();

        return $account;
    }
}
