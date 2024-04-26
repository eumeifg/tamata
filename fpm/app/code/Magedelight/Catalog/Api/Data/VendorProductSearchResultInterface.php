<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api\Data;

interface VendorProductSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Products list.
     *
     * @return \Magedelight\Catalog\Api\Data\VendorProductInterface[] $items
     */
    public function getItems();

    /**
     * Set Products list.
     *
     * @param \Magedelight\Catalog\Api\Data\VendorProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
