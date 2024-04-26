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
namespace Magedelight\Vendor\Api\Data;

/**
 * @api
 */
interface SalesSummaryInterface
{
    const LABEL = 'label';
    const SALE_TOTAL = 'sale_total';
    const TOTAL_ORDERS = 'total_orders';
    
    /**
     * Get Label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set Label
     * @param string|null $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get Sale Total
     * @return string|float|null
     */
    public function getSaleTotal();

    /**
     * Set Sale Total
     *
     * @param string|float|null $saleTotal
     * @return $this
     */
    public function setSaleTotal($saleTotal);
    
    /**
     * Set Total Orders
     * @param int|null $totalOrders
     * @return $this
     */
    public function setTotalOrders($totalOrders);
    
    /**
     * Get Total Orders
     * @return int|null
     */
    public function getTotalOrders();
}
