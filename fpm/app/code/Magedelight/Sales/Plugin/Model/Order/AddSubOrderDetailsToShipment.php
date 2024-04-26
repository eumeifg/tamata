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
namespace Magedelight\Sales\Plugin\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentPackageCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;

class AddSubOrderDetailsToShipment
{

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param OrderInterface $order
     * @param ShipmentItemCreationInterface[] $items
     * @param ShipmentTrackCreationInterface[] $tracks
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param ShipmentPackageCreationInterface[] $packages
     * @param ShipmentCreationArgumentsInterface|null $arguments
     * @return ShipmentInterface
     * @since 100.1.2
     */
    public function afterCreate(
        \Magento\Sales\Model\Order\ShipmentDocumentFactory $subject,
        $entity,
        OrderInterface $order,
        array $items = [],
        array $tracks = [],
        ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        array $packages = [],
        ShipmentCreationArgumentsInterface $arguments = null
    ) {
        /*
         * Added vendor_id and vendor_order_id for shipment generation using API in vendor app.
         */
        if ($arguments && $arguments->getExtensionAttributes()->getVendorId()) {
            /*  Set vendor_id to shipment.*/
            $entity->setVendorId($arguments->getExtensionAttributes()->getVendorId());
        }
        
        if ($arguments && $arguments->getExtensionAttributes()->getVendorOrderId()) {
            /*  Set vendor_order_id to shipment.*/
            $entity->setVendorOrderId($arguments->getExtensionAttributes()->getVendorOrderId());
        }
        return $entity;
    }
}
