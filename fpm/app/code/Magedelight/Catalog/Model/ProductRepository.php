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
namespace Magedelight\Catalog\Model;

use Magedelight\Catalog\Api\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{

    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $mdProduct
    ) {
        $this->mdProduct =  $mdProduct;
    }

     /**
      * {@inheritdoc}
      */
    public function getById($id, $field = null, $storeId = null, $forceReload = false)
    {

        $vendorProduct = $this->mdProduct->create();
        if ($field == null) {
            $vendorProductData = $vendorProduct->getVendorRawCollection($id);
        } else {
            $vendorProductData = $vendorProduct->load($id, $field);
        }
        return $vendorProductData;
    }
}
