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

interface ProductWebsiteSearchResultInterface
{
    /**
     * Get Product Websites list.
     *
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface[]
     */
    public function getItems();

    /**
     * Set Product Websites list.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
