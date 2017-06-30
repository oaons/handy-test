<?php

namespace CoreBundle\Entity\Transaction;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TransferDepositTransaction extends Transaction
{
    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Account")
     * @var Account
     */
    private $fromAccount;

    /**
     * @param Account $account
     * @param Account $fromAccount
     * @param float $amount
     */
    public function __construct(Account $account, Account $fromAccount, float $amount)
    {
        $this->account = $account;
        $this->fromAccount = $fromAccount;
        $this->amount = $amount;
        $this->account->addTransaction($this);
    }
}
