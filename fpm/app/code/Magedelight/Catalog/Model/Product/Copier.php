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
namespace Magedelight\Catalog\Model\Product;

class Copier
{

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
    ) {
        $this->vendorProductFactory = $vendorProductFactory;
    }

    public function copy(\Magedelight\Catalog\Model\Product $vendorProduct, $storeId = 0)
    {

        /** @var \Magedelight\Catalog\Model\Product $duplicate */
        $duplicate = $this->vendorProductFactory->create();

        $duplicate->setData($vendorProduct->getData());

        $duplicate->setId(null);

        $duplicate->setStatus($vendorProduct->getStatus());

        $duplicate->setStoreId($storeId);

        $isDuplicateSaved = false;

        $duplicate->save();

        return $duplicate->getId();
    }
}
