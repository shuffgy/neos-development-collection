<?php

namespace Neos\Neos\Domain\Repository;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

/**
 * The User Repository
 *
 * @Flow\Scope("singleton")
 * @api
 */
class UserRepository extends Repository
{
    const SORT_BY_LASTLOGGEDIN = 'LastLoggedIn';
    const SORT_DIRECTION_DESC = 'DESC';

    /**
     * @return QueryResultInterface
     */
    public function findAllOrderedByUsername(): QueryResultInterface
    {
        return $this->createQuery()
            ->setOrderings(['accounts.accountIdentifier' => QueryInterface::ORDER_ASCENDING])
            ->execute();
    }

    /**
     * @param bool $sortDescending
     * @return QueryResultInterface
     */
    public function findAllOrderedByLastLoggedInDate(bool $sortDescending): QueryResultInterface
    {
        return $this->createQuery()
            ->setOrderings(['accounts.lastSuccessfulAuthenticationDate' => $sortDescending ? QueryInterface::ORDER_DESCENDING : QueryInterface::ORDER_ASCENDING])
            ->execute();
    }

    /**
     * @param string $searchTerm
     * @param string $sortBy Can be SORT_BY_LASTLOGGEDIN or empty
     * @param string $sortDirection Can be SORT_DIRECTION_DESC or empty
     * @return QueryResultInterface
     */
    public function findBySearchTerm(string $searchTerm, string $sortBy, string $sortDirection): QueryResultInterface
    {
        try {
            $query = $this->createQuery();
            $query->matching(
                $query->logicalOr(
                    $query->like('accounts.accountIdentifier', '%' . $searchTerm . '%'),
                    $query->like('name.fullName', '%' . $searchTerm . '%')
                )
            );
            return $query->setOrderings([$sortBy === self::SORT_BY_LASTLOGGEDIN ? 'accounts.lastSuccessfulAuthenticationDate' : 'accounts.accountIdentifier' => $sortDirection === self::SORT_DIRECTION_DESC ? QueryInterface::ORDER_DESCENDING : QueryInterface::ORDER_ASCENDING])->execute();
        } catch (\Neos\Flow\Persistence\Exception\InvalidQueryException $e) {
            throw new \RuntimeException($e->getMessage(), 1557767046, $e);
        }
    }
}
