<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\Productslider\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductRepositoryInterface
{

    /**
     * Get product list
     *
     * @param \Ktpl\Productslider\Api\Data\ProductsliderInterface $slider
     * @param int|null $limit
     * @return \Ktpl\Productslider\Api\Data\ProductSearchResultsInterface
     */
    public function getList($slider, $limit = null);
}
