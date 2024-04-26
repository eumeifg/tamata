<?php

namespace CAT\TamataCollect\Plugin\Order;

use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use CAT\TamataCollect\Helper\Data as TamataCollectHelper;

class AddTamataCollect
{
    /**
     * @var TamataCollectHelper
     */
    protected $tamataCollectHelper;
    /**
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @param TamataCollectHelper $tamataCollectHelper
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        TamataCollectHelper   $tamataCollectHelper,
        OrderExtensionFactory $extensionFactory
    )
    {
        $this->tamataCollectHelper = $tamataCollectHelper;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param OrderInterface $order
     * @param OrderExtensionInterface|null $extension
     * @return OrderExtension|OrderExtensionInterface|null
     */
    public function afterGetExtensionAttributes(OrderInterface $order, OrderExtensionInterface $extension = null)
    {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }
        if ($this->tamataCollectHelper->isEnabled()) {
            $optionID = $this->tamataCollectHelper->getOptionId();
            $isTamataCollect = 'false';
            /* get shipping address */
            $shipping = $order->getShippingAddress();
            if ($shipping && ($shipping->getData('addresstype') == $optionID)) {
                $isTamataCollect = 'true';
            }
            $extension->setIsTamataCollect($isTamataCollect);
            $order->setExtensionAttributes($extension);
            return $extension;
        }
        return $extension;
    }
}
