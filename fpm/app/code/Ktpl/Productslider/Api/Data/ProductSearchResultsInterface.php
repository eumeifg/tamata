<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Productslider\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface ProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Ktpl\Productslider\Api\Data\ProductInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Ktpl\Productslider\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
