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

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AddSubOrderDetailsToInvoice
{

    /**
     * @param OrderInterface $order
     * @param array $items
     * @param InvoiceCommentCreationInterface|null $comment
     * @param bool|false $appendComment
     * @param InvoiceCreationArgumentsInterface|null $arguments
     * @return InvoiceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 100.1.2
     */
    public function afterCreate(
        \Magento\Sales\Model\Order\InvoiceDocumentFactory $subject,
        $entity,
        OrderInterface $order,
        $items = [],
        InvoiceCommentCreationInterface $comment = null,
        $appendComment = false,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        /*
         * Added vendor_id and vendor_order_id for invoice generation using API in vendor app.
         */
        if ($arguments && $arguments->getExtensionAttributes()->getVendorId()) {
            /*  Set vendor_id to invoice.*/
            $entity->setVendorId($arguments->getExtensionAttributes()->getVendorId());
        }
        if ($arguments && $arguments->getExtensionAttributes()->getVendorOrderId()) {
            /*  Set vendor_order_id to invoice.*/
            $entity->setVendorOrderId($arguments->getExtensionAttributes()->getVendorOrderId());
        }
        return $entity;
    }
}
