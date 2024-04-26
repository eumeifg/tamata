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
namespace Magedelight\Catalog\Model\VendorProduct\Listing;

use Magedelight\Catalog\Model\Product;

/**
 * Moved listing collection code to models to sync with API calls.
 */
class ApprovedProducts extends AbstractProductList
{

    /*
    * @param integer $vendorId
    * @param integer $storeId
    * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
    */
    public function getList($vendorId, $storeId)
    {
        return $this->getCollection($vendorId, $storeId, Product::STATUS_UNLISTED);
    }
}
