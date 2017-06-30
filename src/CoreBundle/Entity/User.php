<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserRepository")
 * @ORM\Table(
 *     name="`user`",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="user_name_uniq", columns={"name"})
 *     }
 * )
 */
class User
{
    /**
     * User identifier
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * User name
     * @ORM\Column(type="string")
     * @var string
     */
    protected $name;

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
     * @ORM\OneToMany(targetEntity="CoreBundle\Entity\Account", mappedBy="user")
     * @ORM\OrderBy(value={"createdAt": "desc"})
     * @var Account[]
     */
    protected $accounts;


    public function __construct()
    {
        $this->accounts = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Collection|Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @param Account $account
     * @return $this
     */
    public function addAccount(Account $account)
    {
        $this->accounts->add($account);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return User
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }
}
