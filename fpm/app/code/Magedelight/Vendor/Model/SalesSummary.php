<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\SalesSummaryInterface;

class SalesSummary extends \Magento\Framework\DataObject implements SalesSummaryInterface
{
    /**
     * Get Label
     * @return string|null
     */
    public function getLabel()
    {
        $this->getData(SalesSummaryInterface::LABEL);
    }

    /**
     * Set Label
     * @param string|null $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->setData(SalesSummaryInterface::LABEL, $label);
    }

    /**
     * Get Sale Total
     * @return string|float|null
     */
    public function getSaleTotal()
    {
        $this->getData(SalesSummaryInterface::SALE_TOTAL);
    }

    /**
     * Set Sale Total
     *
     * @param string|float|null $saleTotal
     * @return $this
     */
    public function setSaleTotal($saleTotal)
    {
        $this->setData(SalesSummaryInterface::SALE_TOTAL, $saleTotal);
    }
    
    /**
     * Set Total Orders
     * @param int|null $totalOrders
     * @return $this
     */
    public function setTotalOrders($totalOrders)
    {
        $this->setData(SalesSummaryInterface::TOTAL_ORDERS, $totalOrders);
    }
    
    /**
     * Get Total Orders
     * @return int|null
     */
    public function getTotalOrders()
    {
        $this->getData(SalesSummaryInterface::TOTAL_ORDERS);
    }
}
