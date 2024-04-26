<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Plugin\Quote\Item;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class ToOrderItem
{
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magedelight\Catalog\Helper\Data $helper,
        Json $serializer = null
    ) {
        $this->helper = $helper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        $proceed,
        $quoteItem,
        $data = []
    ) {
        $item = $proceed($quoteItem, $data);
        $vendorPrice = $this->helper->getVendorFinalPrice(
            $quoteItem->getData('vendor_id'),
            $item->getProduct()->getId(),
            false
        );
        $formattedVendorPrice = $this->helper->currency($vendorPrice, false, true);

        $vendorProductCollection = $this->helper->getVendorProduct(
            $quoteItem->getData('vendor_id'),
            $item->getProduct()->getId(),
            false
        );
        if ($vendorProductCollection) {
            $vendorSku = $vendorProductCollection['vendor_sku'];
            $additionalOptions = $quoteItem->getOptionByCode('additional_options');
            if (is_array($additionalOptions) && count($additionalOptions) > 0) {
                if ($item->getParentItem()) {
                    $options = $item->getParentItem()->getProductOptions();
                    $options['vendor_sku'] = $vendorSku;
                    $options['additional_options'] = (array)$this->serializer->unserialize(
                        $additionalOptions->getValue()
                    );
                    $item->getParentItem()->setProductOptions($options);
                } else {
                    $options = $item->getProductOptions();
                    $options['vendor_sku'] = $vendorSku;
                    $options['additional_options'] = (array)$this->serializer->unserialize(
                        $additionalOptions->getValue()
                    );
                    $item->setProductOptions($options);
                }
            }
        }

        if ($item->getParentItem()) {
            /* Set Price for Configurable product */
            $item->getParentItem()->setPrice($formattedVendorPrice);
            $item->getParentItem()->setOriginalPrice($formattedVendorPrice);
            $item->getParentItem()->setBaseOriginalPrice($vendorPrice);
        } else {
            $item->setPrice($formattedVendorPrice);
            $item->setOriginalPrice($formattedVendorPrice);
            $item->setBaseOriginalPrice($vendorPrice);
        }
        return $item;
    }
}
