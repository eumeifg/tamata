<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_TargetRule
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

/**
 * TargetRule Catalog Product List Upsell Model
 *
 */
namespace Magedelight\TargetRule\Model\Catalog\Product\ProductList;

use Magedelight\TargetRule\Model\Catalog\Product\ProductList\AbstractProductList;

/**
 * @api
 * @since 100.0.2
 */
class Upsell extends AbstractProductList
{
    /**
     * Retrieve Catalog Product List Type identifier
     *
     * @return int
     */
    public function getProductListType()
    {
        return \Magento\TargetRule\Model\Rule::UP_SELLS;
    }

    /**
     * Retrieve related product collection assigned to product
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getLinkCollection()
    {
        if ($this->_linkCollection === null) {
            parent::getLinkCollection();
            /**
             * Updating collection with desired items
             */
            $this->_eventManager->dispatch(
                'catalog_product_upsell',
                [
                    'product' => $this->getProduct(),
                    'collection' => $this->_linkCollection,
                    'limit' => $this->getPositionLimit()
                ]
            );
        }

        return $this->_linkCollection;
    }

    /**
     * Get ids of all related products
     *
     * @return array
     */
    public function getAllIds()
    {
        if ($this->_allProductIds === null) {
            if (!$this->isShuffled()) {
                return parent::getAllIds();
            }

            $ids = parent::getAllIds();
            $ids = new \Magento\Framework\DataObject(['items' => array_flip($ids)]);
            /**
             * Updating collection with desired items
             */
            $this->_eventManager->dispatch(
                'catalog_product_upsell',
                ['product' => $this->getProduct(), 'collection' => $ids, 'limit' => null]
            );

            $this->_allProductIds = array_keys($ids->getItems());
            shuffle($this->_allProductIds);
        }

        return $this->_allProductIds;
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getAllItems()
    {
        $collection = parent::getAllItems();
        $collectionMock = new \Magento\Framework\DataObject(['items' => $collection]);
        $this->_eventManager->dispatch(
            'catalog_product_upsell',
            [
                'product'       => $this->getProduct(),
                'collection'    => $collectionMock,
                'limit'         => null
            ]
        );
        return $collectionMock->getItems();
    }

    /**
     * Retrieve array of exclude product ids
     * Rewrite for exclude shopping cart products
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        if ($this->_excludeProductIds === null) {
            $cartProductIds = $this->_cart->getProductIds();
            $this->_excludeProductIds = array_merge($cartProductIds, [$this->getProduct()->getEntityId()]);
        }
        return $this->_excludeProductIds;
    }
}
