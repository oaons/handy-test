<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\AccountRepository")
 * @ORM\Table(name="account")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Account
{
    /**
     * Account identifier
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\User", inversedBy="accounts")
     * @var User
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="CoreBundle\Entity\Transaction", mappedBy="account", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @var Collection|Transaction[]
     */
    protected $transactions;

    /**
     * @ORM\Column(type="decimal", nullable=false, options={"unsigned"=true, "precision"=14, "scale"=4, "default"=0})
     * @var float
     */
    protected $amount = 0;

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
     * @ORM\Column(type="datetimetz", nullable=true)
     * @var \DateTime|null
     */
    private $deletedAt;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->user->addAccount($this);
        $this->transactions = new ArrayCollection();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param Transaction $transaction
     */
    public function addTransaction(Transaction $transaction)
    {
        $this->transactions->add($transaction);
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @return Transaction[]|Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}
