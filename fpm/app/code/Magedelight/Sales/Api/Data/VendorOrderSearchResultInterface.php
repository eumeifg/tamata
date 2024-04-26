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
namespace Magedelight\Sales\Api\Data;

interface VendorOrderSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Vendor/Sub Order list.
     *
     * @return \Magedelight\Sales\Api\Data\VendorOrderInterface[] $items
     */
    public function getItems();

    /**
     * Set Vendor/Sub Order list.
     *
     * @param \Magedelight\Sales\Api\Data\VendorOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
