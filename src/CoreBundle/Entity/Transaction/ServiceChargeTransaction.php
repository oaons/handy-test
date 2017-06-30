<?php

namespace CoreBundle\Entity\Transaction;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ServiceChargeTransaction extends Transaction
{
    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Transaction\TransferWithdrawTransaction")
     * @var TransferWithdrawTransaction
     */
    private $transaction;

    public function __construct(Account $account, float $amount, TransferWithdrawTransaction $transaction)
    {
        $this->account = $account;
        $this->amount = -$amount;
        $this->transaction = $transaction;
        $this->account->addTransaction($this);
    }
}
