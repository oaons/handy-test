<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\TransactionRepository")
 * @ORM\Table(name="transaction")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *      "withdraw" = "CoreBundle\Entity\Transaction\WithdrawTransaction",
 *      "deposit" = "CoreBundle\Entity\Transaction\DepositTransaction",
 *      "transfer_withdraw" = "CoreBundle\Entity\Transaction\TransferWithdrawTransaction",
 *      "transfer_deposit" = "CoreBundle\Entity\Transaction\TransferDepositTransaction",
 *      "service_charge" = "CoreBundle\Entity\Transaction\ServiceChargeTransaction"
 * })
 */
abstract class Transaction
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Account", inversedBy="transactions")
     * @var Account
     */
    protected $account;

    /**
     * @ORM\Column(type="decimal", nullable=false, options={"unsigned"=true, "precision"=14, "scale"=4, "default"=0})
     * @var float
     */
    protected $amount;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetimetz")
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetimetz")
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
