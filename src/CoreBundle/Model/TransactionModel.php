<?php

namespace CoreBundle\Model;

use ApiBundle\Exception\DomainLogicException;
use Carbon\Carbon;
use CoreBundle\Client\ApproveTransactionClientInterface;
use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction\DepositTransaction;
use CoreBundle\Entity\Transaction\ServiceChargeTransaction;
use CoreBundle\Entity\Transaction\TransferDepositTransaction;
use CoreBundle\Entity\Transaction\TransferWithdrawTransaction;
use CoreBundle\Entity\Transaction\WithdrawTransaction;
use CoreBundle\Repository\TransactionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("core.model.transaction")
 */
class TransactionModel
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var int
     */
    private $transferLimit;

    /**
     * @var int
     */
    private $serviceCharge;

    /**
     * @var ApproveTransactionClientInterface
     */
    private $approveClient;

    /**
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "transactionRepository" = @DI\Inject("core.repository.transaction"),
     *     "approveClient" = @DI\Inject("core.client.handy"),
     *     "transferLimit" = @DI\Inject("%account.transaction.transfer.limit%"),
     *     "serviceCharge" = @DI\Inject("%account.transaction.transfer.service_charge%"),
     * })
     * @param ObjectManager $objectManager
     * @param TransactionRepository $transactionRepository
     * @param ApproveTransactionClientInterface $approveClient
     * @param int $transferLimit
     * @param int $serviceCharge
     */
    public function __construct(
        ObjectManager $objectManager,
        TransactionRepository $transactionRepository,
        ApproveTransactionClientInterface $approveClient,
        int $transferLimit,
        int $serviceCharge
    )
    {
        $this->objectManager = $objectManager;
        $this->transactionRepository = $transactionRepository;
        $this->approveClient = $approveClient;
        $this->transferLimit = $transferLimit;
        $this->serviceCharge = $serviceCharge;
    }

    /**
     * @param Account $account
     * @param float $amount
     */
    public function depositFunds(Account $account, float $amount)
    {
        $transaction = new DepositTransaction($account, $amount);
        $this->objectManager->persist($transaction);
    }

    /**
     * @param Account $account
     * @param float $amount
     */
    public function withdrawFunds(Account $account, float $amount)
    {
        $transaction = new WithdrawTransaction($account, $amount);
        $this->objectManager->persist($transaction);
    }

    /**
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param float $amount
     */
    public function transferFunds(Account $fromAccount, Account $toAccount, float $amount)
    {
        $withdrawTransaction = new TransferWithdrawTransaction($fromAccount, $toAccount, $amount);
        $depositTransaction = new TransferDepositTransaction($toAccount, $fromAccount, $amount);

        $this->objectManager->persist($withdrawTransaction);
        $this->objectManager->persist($depositTransaction);

        if ($fromAccount->getUser() !== $toAccount->getUser()) {
            $serviceChargeTransaction = new ServiceChargeTransaction($fromAccount, $this->serviceCharge, $withdrawTransaction);
            $this->objectManager->persist($serviceChargeTransaction);

            if (!$this->approveClient->isTransactionApproved()) {
                throw new DomainLogicException('Transaction was not approved');
            }
        }
    }

    /**
     * @param Account $account
     * @param Carbon $now
     * @throws DomainLogicException
     */
    public function checkDailyTransactionLimit(Account $account, Carbon $now)
    {
        $dayStart = (clone $now)->startOfDay();
        $dayEnd = (clone $now)->endOfDay();

        $transferWithdrawTransactionAmount = $this->transactionRepository
            ->getAccountTransactionAmountForPeriod($account, $dayStart, $dayEnd, TransferWithdrawTransaction::class);

        // $transferWithdrawTransactionAmount will always be < 0, so we invert limit
        if ($transferWithdrawTransactionAmount < -$this->transferLimit) {
            throw new DomainLogicException(sprintf('Daily transaction limit of %d funds exceed', $this->transferLimit));
        }
    }

    /**
     * @param Account $account
     * @throws DomainLogicException
     */
    public function updateAccountAmount(Account $account)
    {
        $this->transactionRepository->updateAccountAmount($account);
        $this->objectManager->refresh($account);
        if ($account->getAmount() < 0) {
            throw new DomainLogicException('Can not withdraw funds. Please check your balance');
        }
    }

    /**
     * @param Account[] ...$accounts
     */
    public function updateAccountAmounts(Account ...$accounts)
    {
        foreach ($accounts as $account) {
            $this->transactionRepository->updateAccountAmount($account);
            $this->objectManager->refresh($account);
            if ($account->getAmount() < 0) {
                throw new DomainLogicException('Can not withdraw funds. Please check your balance');
            }
        }
    }
}
