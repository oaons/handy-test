<?php

namespace ApiBundle\Controller;

use ApiBundle\Exception\DomainLogicException;
use ApiBundle\Form\FormHandler;
use ApiBundle\Form\Transaction\DepositType;
use ApiBundle\Form\Transaction\TransferType;
use ApiBundle\Form\Transaction\WithdrawType;
use Carbon\Carbon;
use CoreBundle\Entity\Account;
use CoreBundle\Model\TransactionModel;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class TransactionController
{
    /**
     * @DI\Inject("core.model.transaction")
     * @var TransactionModel
     */
    private $transactionModel;

    /**
     * @DI\Inject("api.form.handler")
     * @var FormHandler
     */
    private $formHandler;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @ApiDoc(
     *   description = "Deposit funds to account",
     *   statusCodes = {201 = "OK", 400 = "Validation failed", 404 = "Account not found"},
     *   input = {"class" = "ApiBundle\Form\Transaction\DepositType", "name" = ""},
     *   output = {"class" = "CoreBundle\Entity\Account", "groups" = {"Default"}},
     *   section = "Transaction"
     * )
     * @Rest\Post(path="/accounts/{account}/deposit")
     * @Rest\View(statusCode=201, serializerGroups={"Default"})
     * @ParamConverter("account", class="CoreBundle:Account", options={"id" = "account"})
     * @param Request $request
     * @param Account $account
     * @return Account
     */
    public function depositAction(Request $request, Account $account)
    {
        $form = $this->formHandler->handleRequest($request, DepositType::class);

        $amount = $form->get('amount')->getData();

        $this->entityManager->getConnection()->beginTransaction();
        $this->transactionModel->depositFunds($account, $amount);
        $this->entityManager->flush();
        $this->transactionModel->updateAccountAmount($account);
        $this->entityManager->getConnection()->commit();

        return $account;
    }

    /**
     * @ApiDoc(
     *   description = "Withdraw funds from account",
     *   statusCodes = {201 = "OK", 400 = "Validation failed", 404 = "Account not found", 409 = "Processing error"},
     *   input = {"class" = "ApiBundle\Form\Transaction\WithdrawType", "name" = ""},
     *   output = {"class" = "CoreBundle\Entity\Account", "groups" = {"Default"}},
     *   section = "Transaction"
     * )
     * @Rest\Post(path="/accounts/{account}/withdraw")
     * @Rest\View(statusCode=201, serializerGroups={"Default"})
     * @ParamConverter("account", class="CoreBundle:Account", options={"id" = "account"})
     * @param Request $request
     * @param Account $account
     * @return Account
     */
    public function withdrawAction(Request $request, Account $account)
    {
        $form = $this->formHandler->handleRequest($request, WithdrawType::class);

        $amount = $form->get('amount')->getData();

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->transactionModel->withdrawFunds($account, $amount);
            $this->entityManager->flush();
            $this->transactionModel->updateAccountAmount($account);
            $this->entityManager->getConnection()->commit();
        } catch (DomainLogicException $exception) {
            $this->entityManager->getConnection()->rollBack();
            throw $exception;
        }

        return $account;
    }

    /**
     * @ApiDoc(
     *   description = "Transfer funds between accounts",
     *   statusCodes = {201 = "OK", 400 = "Validation failed", 404 = "Account not found", 409 = "Processing error"},
     *   input = {"class" = "ApiBundle\Form\Transaction\TransferType", "name" = ""},
     *   output = {"class" = "CoreBundle\Entity\Account", "groups" = {"Default"}},
     *   section = "Transaction"
     * )
     * @Rest\Post(path="/accounts/{account}/transfer")
     * @Rest\View(statusCode=201, serializerGroups={"Default"})
     * @ParamConverter("account", class="CoreBundle:Account", options={"id" = "account"})
     * @param Request $request
     * @param Account $account
     * @return Account
     */
    public function transferAction(Request $request, Account $account)
    {
        $form = $this->formHandler->handleRequest($request, TransferType::class);

        $amount = $form->get('amount')->getData();
        $toAccount = $form->get('to_account')->getData();

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->transactionModel->transferFunds($account, $toAccount, $amount);
            $this->entityManager->flush();
            $this->transactionModel->updateAccountAmounts($account, $toAccount);
            $this->transactionModel->checkDailyTransactionLimit($account, Carbon::now());
            $this->entityManager->getConnection()->commit();
        } catch (DomainLogicException $exception) {
            $this->entityManager->getConnection()->rollBack();
            throw $exception;
        }

        return $account;
    }
}
