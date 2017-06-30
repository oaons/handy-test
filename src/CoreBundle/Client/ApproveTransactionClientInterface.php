<?php

namespace CoreBundle\Client;

use ApiBundle\Exception\DomainLogicException;

interface ApproveTransactionClientInterface
{
    /**
     * @throws DomainLogicException
     * @return bool
     */
    public function isTransactionApproved(): bool;
}
