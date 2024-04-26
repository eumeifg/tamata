<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Plugins\ConfigurableProduct;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class Data extends \Magento\ConfigurableProduct\Helper\Data
{
    protected $_registry;
    protected $_objectManager;
    protected $_helper;
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $stockStateProvider;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magedelight\ProductAlert\Helper\Data $_helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->imageHelper = $imageHelper;
        $this->_objectManager = $objectManager;
        $this->_helper = $_helper;
        $this->_registry = $registry;
        parent::__construct($imageHelper);
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $aStockStatus = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $images = $this->getGalleryImages($product);
            if ($images) {
                foreach ($images as $image) {
                    $options['images'][$productId][] =
                        [
                            'thumb' => $image->getData('small_image_url'),
                            'img' => $image->getData('medium_image_url'),
                            'full' => $image->getData('large_image_url'),
                            'caption' => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'isMain' => $image->getFile() == $product->getImage(),
                        ];
                }
            }
            $allowAttributes = $this->getAllowAttributes($currentProduct);
            $key = [];
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;

                /* code start*/
                $key[] = $product->getData(
                    $attribute->getData('product_attribute')->getData(
                        'attribute_code'
                    )
                );
            }


            if ($key) {
               /* $product = $this->_objectManager->get(
                    'Magento\Catalog\Model\Product')->load($product->getId());
                $saleable = $product->isSalable();*/
                $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
                $saleable =  $stockItem->getIsInStock() && $this->verifyStock($stockItem);

                $stockStatus = (!$saleable)? __('Out of Stock'): '';
                $aStockStatus[implode(',', $key)] = [
                    'is_in_stock'   => $saleable,
                    'custom_status' => $stockStatus,
                    'product_id'    => $product->getId()
                ];
                if (!$saleable) {
                    $aStockStatus[implode(',', $key)]['stockalert'] =
                        $this->_helper->getStockAlert($product);
                }
            }
            /*code end*/

        }
        $this->_registry->unregister('Magedelight_ProductAlert_data');
        $this->_registry->register('Magedelight_ProductAlert_data', $aStockStatus);

        return $options;
    }

    public function verifyStock(StockItemInterface $stockItem)
    {
        if ($stockItem->getQty() === null && $stockItem->getManageStock()) {
            return false;
        }
        if ($stockItem->getBackorders() == StockItemInterface::BACKORDERS_NO
            && $stockItem->getQty() <= $stockItem->getMinQty()
        ) {
            return false;
        }
        return true;
    }
}
