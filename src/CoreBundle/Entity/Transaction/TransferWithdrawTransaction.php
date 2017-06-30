<?php

namespace CoreBundle\Entity\Transaction;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TransferWithdrawTransaction extends Transaction
{
    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Account")
     * @var Account
     */
    private $toAccount;

    /**
     * @param Account $account
     * @param Account $toAccount
     * @param float $amount
     */
    public function __construct(Account $account, Account $toAccount, float $amount)
    {
        $this->account = $account;
        $this->toAccount = $toAccount;
        $this->amount = -$amount;
        $this->account->addTransaction($this);
    }
}
