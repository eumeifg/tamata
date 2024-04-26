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

use Magedelight\Sales\Api\Data\SubOrdersInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magedelight\Sales\Api\OrderRepositoryInterface;

class AddVendorOrdersToMainOrder
{
    /**
     * @var SubOrdersInterface
     */
    protected $subOrdersInterface;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * AddVendorOrdersToMainOrder constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SubOrdersInterface $subOrdersInterface
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SubOrdersInterface $subOrdersInterface,
        OrderExtensionFactory $extensionFactory
    ) {
        $this->subOrdersInterface = $subOrdersInterface;
        $this->orderRepository = $orderRepository;
        $this->extensionFactory = $extensionFactory;
    }

    public function afterGetExtensionAttributes(
        OrderInterface $entity,
        OrderExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        $collection = $this->orderRepository->getSubOrdersByMainOrder($entity->getEntityId());
        $this->subOrdersInterface->setSubOrders($collection);
        $extension->setVendorOrders($this->subOrdersInterface);
        $entity->setExtensionAttributes($extension);
        return $extension;
    }
}
