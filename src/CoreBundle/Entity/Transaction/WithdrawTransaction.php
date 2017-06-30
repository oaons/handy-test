<?php

namespace CoreBundle\Entity\Transaction;

use CoreBundle\Entity\Account;
use CoreBundle\Entity\Transaction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class WithdrawTransaction extends Transaction
{
    public function __construct(Account $account, float $amount)
    {
        $this->account = $account;
        $this->amount = -$amount;
        $this->account->addTransaction($this);
    }
}
