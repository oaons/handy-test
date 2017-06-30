<?php

namespace CoreBundle\Repository;

use CoreBundle\Entity\Account;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class TransactionRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string|null $transactionType
     * @return int
     */
    public function getAccountTransactionAmountForPeriod(
        Account $account,
        \DateTime $dateFrom,
        \DateTime $dateTo,
        string $transactionType = null
    )
    {
        $queryBuilder = $this->createQueryBuilder('transaction')
            ->select('SUM(transaction.amount)')
            ->where('transaction.account = :account')
            ->andWhere('transaction.createdAt > :dateFrom')
            ->andWhere('transaction.createdAt < :dateTo')
            ->groupBy('transaction.account')
            ->setParameter('account', $account)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo);

        if (null !== $transactionType) {
            $queryBuilder
                ->andWhere('transaction INSTANCE OF :transactionType')
                ->setParameter('transactionType', $this->_em->getClassMetadata($transactionType));
        }

        $periodAmount = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $periodAmount ?? 0;
    }

    /**
     * @param Account $account
     */
    public function updateAccountAmount(Account $account)
    {
        $connection = $this->_em->getConnection();

        $statement = $connection
            ->prepare(
                <<<'UPDATE'
                    UPDATE account
                        SET amount = (
                            SELECT SUM(amount) FROM transaction where account_id = :accountId
                        )
                    WHERE account.id = :accountId
UPDATE
            );
        $statement->bindValue('accountId', $account->getId());
        $statement->execute();
    }
}
