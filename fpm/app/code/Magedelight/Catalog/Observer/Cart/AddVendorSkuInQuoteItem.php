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
namespace Magedelight\Catalog\Observer\Cart;

use Magento\Framework\Event\ObserverInterface;

class AddVendorSkuInQuoteItem implements ObserverInterface
{
    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->vendorProductResource = $vendorProductResource;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return BeforeSaveCartItemInDB
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getData('quote_item');
        $vendorId = $this->_checkoutSession->getProductVendorId();
        if ($item->getProductType() === 'configurable') {
            if ($item->getVendorSku() === null || $item->getVendorSku() === false) {
                $simpleOption = $item->getProduct()->getCustomOption('simple_product');
                $hasChild = false;
                if ($simpleOption) {
                    /* Set sku of selected associated/child product following magento standards/format. */
                    $optionProduct = $simpleOption->getProduct();
                    if ($optionProduct) {
                        $hasChild = true;
                        $item->setVendorSku(
                            $this->vendorProductResource->getVendorProductSku(
                                $simpleOption->getProduct()->getId(),
                                $vendorId
                            )
                        );
                    }
                }
                if(!$hasChild){
                    $item->setVendorSku(
                        $this->vendorProductResource->getVendorProductSku(
                            $item->getProductId(),
                            null
                        )
                    );
                }  
            }
        } else {
            if ($item->getVendorSku() === null) {
                $item->setVendorSku(
                    $this->vendorProductResource->getVendorProductSku($item->getProductId(), $vendorId)
                );
            }
        }
        return $this;
    }
}
