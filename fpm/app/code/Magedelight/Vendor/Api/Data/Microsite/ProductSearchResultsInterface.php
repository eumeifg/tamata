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
namespace Magedelight\Vendor\Api\Data\Microsite;

interface ProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     *
     * @return \Magedelight\Vendor\Api\Data\Microsite\ProductInterface[]
     */
    public function getItems();

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\Microsite\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
