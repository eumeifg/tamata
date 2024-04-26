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
namespace Magedelight\Sales\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magedelight\Sales\Api\Data\OrderItemImageInterface;

class OrderItemImages
{
    /**
     * @var OrderItemImageInterfaceFactory
     */
    protected $orderItemImageInterfaceFactory;
    
    /**
     * @var Magento\Catalog\Helper\Product
     */
    protected $productHelper;
    
    /**
     * @param \MDC\Sales\Api\Data\OrderItemImageInterfaceFactory $orderItemImageInterfaceFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     */
    public function __construct(
        \Magedelight\Sales\Api\Data\OrderItemImageInterfaceFactory $orderItemImageInterfaceFactory,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
        $this->orderItemImageInterfaceFactory = $orderItemImageInterfaceFactory;
        $this->productHelper = $productHelper;
    }

    /**
     * Get Items
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function afterGetItems(
        \Magento\Sales\Api\Data\OrderInterface $subject,
        $result
    ) {
        foreach ($result as $item) {
            $imageData = $this->orderItemImageInterfaceFactory->create();
            $imageData->setImageUrl($this->productHelper->getImageUrl(
                $item->getProduct()
            ));
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes->setOrderItemImageData($imageData);
            $item->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }
}
