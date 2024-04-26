<?php

namespace MDC\Sales\Plugin;

use Magedelight\Sales\Api\Data\OrderItemImageInterfaceFactory;
use Magedelight\Sales\Api\Data\VendorOrderInterface;
use Magento\Catalog\Helper\Product;
use Magento\Sales\Api\Data\OrderInterface;
use Magedelight\Sales\Api\Data\OrderItemImageInterface;
use Magento\Sales\Api\Data\OrderItemInterface;


class VendorOrderItemImages extends \Magedelight\Sales\Plugin\VendorOrderItemImages
{

    /**
     * @var OrderItemImageInterfaceFactory
     */
    protected $orderItemImageInterfaceFactory;

    /**
     * @var Product
     */
    protected $productHelper;

    /**
     * @param OrderItemImageInterfaceFactory $orderItemImageInterfaceFactory
     * @param Product $productHelper
     */
    public function __construct(
        OrderItemImageInterfaceFactory $orderItemImageInterfaceFactory,
        Product                        $productHelper
    )
    {
        $this->orderItemImageInterfaceFactory = $orderItemImageInterfaceFactory;
        $this->productHelper = $productHelper;
    }

    /**
     * Get Items
     * @param VendorOrderInterface $subject
     * @param $result
     * @return array|OrderItemInterface[]
     */
    public function afterGetItems(VendorOrderInterface $subject, $result): array
    {
        $variantImageData = [];
        $isVariantPorduct = false;
        foreach ($result as $item) {
            $variantImageData = $this->orderItemImageInterfaceFactory->create();
            if ($item->getParentItem()) {
                if (!empty($item->getProduct())) {
                    $variantImageData->setImageUrl($this->productHelper->getImageUrl(
                        $item->getProduct()
                    ));
                }
                $isVariantPorduct = true;
            }
        }

        foreach ($result as $item) {
            $imageData = $this->orderItemImageInterfaceFactory->create();
            if (!empty($item->getProduct())) {
                $imageData->setImageUrl($this->productHelper->getImageUrl(
                    $item->getProduct()
                ));
            }
            $extensionAttributes = $item->getExtensionAttributes();
            // $extensionAttributes->setOrderItemImageData($imageData);

            if (isset($variantImageData) && $isVariantPorduct) {
                $extensionAttributes->setOrderItemImageData($variantImageData);
            } else {
                $extensionAttributes->setOrderItemImageData($imageData);
            }
            $item->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }
}
