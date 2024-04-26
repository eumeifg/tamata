<?php

namespace MDC\OrderConfirmCode\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderConfirmationCode
{
    /**
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory
    )
    {
        $this->extensionFactory = $extensionFactory;
    }

    public function afterGetExtensionAttributes(
        OrderInterface          $entity,
        OrderExtensionInterface $extension = null
    )
    {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }
        $extension->setOrderConfirmCode($entity->getOrderConfirmCode());
        $entity->setExtensionAttributes($extension);
        return $extension;
    }
}
