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
namespace Magedelight\Commissions\Model;

use \Magedelight\Commissions\Api\Data\CommissionInvoiceItemsInterface;

class CommissionInvoiceItems extends \Magento\Framework\DataObject implements CommissionInvoiceItemsInterface
{

    /**
     * Get Transaction list.
     *
     * @return \Magedelight\Commissions\Api\Data\CommissionInvoiceInterface[]
     */
    public function getItems()
    {
        return $this->getData('items');
    }

    /**
     * Set Transaction list.
     *
     * @param \Magedelight\Commissions\Api\Data\CommissionInvoiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        return $this->setData('items', $items);
    }

    /**
     * Get Has more
     * @return bool
     */
    public function getHasMore()
    {
        return $this->getData(CommissionInvoiceItemsInterface::HASMORE);
    }

    /**
     * Set Has more
     * @param bool $hasMore
     * @return $this
     */
    public function setHasMore($hasMore)
    {
        return $this->setData(CommissionInvoiceItemsInterface::HASMORE, $hasMore);
    }

    /**
     * Get Total Items
     *
     * @return int
     */
    public function getTotalItems()
    {
        return $this->getData(CommissionInvoiceItemsInterface::TOTAL);
    }

    /**
     * Set Total Items
     *
     * @param int $totalItems
     * @return $this
     */
    public function setTotalItems($totalItems)
    {
        return $this->setData(CommissionInvoiceItemsInterface::TOTAL, $totalItems);
    }
}
