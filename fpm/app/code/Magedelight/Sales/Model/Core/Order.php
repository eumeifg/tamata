<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Core;

use Magento\Sales\Api\Data\OrderItemInterface;

class Order extends \Magento\Sales\Model\Order
{
    protected $vendor;
    
    /**
     *
     * @param type $vendor
     * @return \Magedelight\Sales\Model\Order
     */
    public function setVendorFilter($vendor)
    {
        $this->vendor = $vendor;
    }
    
    /**
     *
     * @param type $vendor
     * @return \Magedelight\Sales\Model\Order
     */
    public function getVendorFilter()
    {
        return $this->vendor;
    }
    
    /**
     * @param array $filterByTypes
     * @param bool $nonChildrenOnly
     * @return ImportCollection
     */
    public function getItemsCollection($filterByTypes = [], $nonChildrenOnly = false)
    {
        $collection = $this->_orderItemCollectionFactory->create()->setOrderFilter($this);

        if ($filterByTypes) {
            $collection->filterByTypes($filterByTypes);
        }
        if ($nonChildrenOnly) {
            $collection->filterByParent();
        }

        $collection->addVendorFilter($this->getVendorFilter());
        
        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setOrder($this);
            }
        }
        return $collection;
    }

    /**
     * Retrieve order shipment availability
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function canShip()
    {
        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }

        if ($this->getIsVirtual()) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_SHIP) === false) {
            return false;
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getIsVirtual() &&
                !$item->getLockedDoShip() && !$this->isRefunded($item)) {
                return true;
            }
        }
        return false;
    }

    private function isRefunded(OrderItemInterface $item)
    {
        return $item->getQtyRefunded() == $item->getQtyOrdered();
    }
}
