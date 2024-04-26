<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Api\Data;

/**
 * @api
 */
interface CommissionInvoiceItemsInterface
{

    const TOTAL = 'total';
    const HASMORE = 'hasmore';

    /**
     * Get Transaction list.
     *
     * @return \Magedelight\Commissions\Api\Data\CommissionInvoiceInterface[]
     */
    public function getItems();

    /**
     * Set Transaction list.
     *
     * @param \Magedelight\Commissions\Api\Data\CommissionInvoiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get Has more
     * @return bool
     */
    public function getHasMore();

    /**
     * Set Has more
     * @param bool $hasMore
     * @return $this
     */
    public function setHasMore($hasMore);

    /**
     * Get Total Items
     *
     * @return int
     */
    public function getTotalItems();

    /**
     * Set Total Items
     *
     * @param int $totalItems
     * @return $this
     */
    public function setTotalItems($totalItems);
}
