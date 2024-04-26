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
namespace Magedelight\Catalog\Observer;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductCollectionObserver implements ObserverInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_configurable;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory
     */
    protected $_defaultVendorIndexersFactory;

    /**
     *
     * @param \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_defaultVendorIndexersFactory = $defaultVendorsFactory->create();
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $productCollection = $observer->getEvent()->getCollection();

        if (($productCollection instanceof \Magento\Framework\Data\Collection)
            ) {
            $col = clone $productCollection;

            /*
            $col->getSelect()->join(
                array('vp' => 'md_vendor_product_listing_idx'),
                "e.entity_id = vp.marketplace_product_id",
                ['vp.marketplace_product_id', 'vp.vendor_id', 'vp.price', 'vp.special_price']
             );*/

            $productsWithVendor = $col->getAllIds();

            //configurable product child collection on product view page.
            if ($this->_request->getControllerName() == 'product' && $this->_request->getActionName() == 'view') {
                $col2 = clone $productCollection;
                $col2->getSelect()->join(
                    ['vp' => 'md_vendor_product'],
                    "e.entity_id = vp.marketplace_product_id",
                    ['vp.marketplace_product_id', 'vp.vendor_id','vp.qty', 'vp.price', 'vp.special_price']
                );
                $col2->getSelect()->where("vp.status = 1 AND vp.store_id  = '$storeId'");
                $productsWithVendor = $col2->getAllIds();
            }

            foreach ($productCollection as $product) {
                if (!in_array($product->getId(), $productsWithVendor)) {
                    $product->setIsSalable(false);
                } else {
                    $vendorId = ($this->_request->getParam('v', false)) ? $this->_request->getParam('v') :
                       ($this->_request->getParam('vid', false)) ? $this->_request->getParam('vid') :
                       $this->_helper->getDefaultVendorId($product->getId());

                    $vendorPrice = $this->_getVendorProductPrice($vendorId, $product->getId());
                    $vendorSpecialPrice = $this->_getProductSpecialPrice($vendorId, $product->getId());

                    if ($product->getTypeId() == Type::TYPE_SIMPLE) {
                        if ($this->_getProductQty($vendorId, $product->getId()) > 0) {
                            $product->setIsSalable(true);
                        } else {
                            $product->setIsSalable(false);
                        }
                    }

                    $vendorPrice = $this->_helper->currency($vendorPrice, false, true);
                    $product->setPrice($vendorPrice);

                    if (!($vendorSpecialPrice === null)) {
                        $product->setSpecialPrice($vendorSpecialPrice);
                    /* $product->setFinalPrice($vendorSpecialPrice); */
                    } else {
                        $product->setSpecialFromDate(null);
                        $product->setSpecialToDate(null);
                        $product->setSpecialPrice(null);
                    }
                }
            }
        }
    }

    /**
     *
     * @param int $vendorId
     * @param int $productId
     * @return float Prodct Qantity
     */
    protected function _getProductQty($vendorId, $productId)
    {
        if ($this->_request->getControllerName() == 'product' && $this->_request->getActionName() == 'view') {
            if ($vendorId) {
                $qty = $this->_helper->getVendorQty($vendorId, $productId);
            } else {
                $qty = 0;
            }
        } else {
            $qty = $this->_defaultVendorIndexersFactory->getProductQty($productId);
        }
        return $qty;
    }
    /**
     *
     * @param int $vendorId
     * @param int $productId
     * @return float Price
     */
    protected function _getVendorProductPrice($vendorId, $productId)
    {
        if ($this->_request->getControllerName() == 'product' && $this->_request->getActionName() == 'view') {
            if ($vendorId) {
                $price = $this->_helper->getVendorPrice($vendorId, $productId);
            } else {
                $price = null;
            }
        } else {
            $price = $this->_defaultVendorIndexersFactory->getPriceByProductId($productId);
        }
        return $price;
    }
    /**
     *
     * @param int $vendorId
     * @param int $productId
     * @return float special price
     */
    protected function _getProductSpecialPrice($vendorId, $productId)
    {
        if ($this->_request->getControllerName() == 'product' && $this->_request->getActionName() == 'view') {
            if ($vendorId) {
                $specialPrice = $this->_helper->getVendorSpecialPrice($vendorId, $productId);
            } else {
                $specialPrice = null;
            }
        } else {
            $specialPrice = $this->_defaultVendorIndexersFactory->getSpecialPriceByProductId($productId);
        }
        return $specialPrice;
    }
}
