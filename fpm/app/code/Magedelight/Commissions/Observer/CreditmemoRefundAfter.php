<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreditmemoRefundAfter implements ObserverInterface
{
    /**
     * @var \Magedelight\Commissions\Model\Commission\Payment
     */
    private $commissionPayment;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    private $vendorOrderFactory;

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $vendorOrder;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    private $oIFactory;

    public function __construct(
        \Magento\Sales\Model\Order\ItemFactory $oIFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Sales\Model\Order $vendorOrder,
        \Magedelight\Commissions\Model\Commission\PaymentFactory $commissionPaymentFactory
    ) {
        $this->oIFactory = $oIFactory;
        $this->vendorOrder = $vendorOrder;
        $this->logger = $logger;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->commissionPayment = $commissionPaymentFactory->create();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $vendorId = $creditmemo->getVendorId();
        $creditmemo->getSubTotal();
        // $vendorOrder = $this->vendorOrderFactory->create()->getByOriginOrderId($order->getId(), $vendorId);
        $vendorOrder = $this->vendorOrderFactory->create()->getByOriginOrderId($order->getId(), $vendorId, $creditmemo->getVendorOrderId());
        $vendorOrder->setData('shipping_refunded', $vendorOrder->getData('shipping_refunded') +
            $creditmemo->getData('shipping_amount'));
        $vendorOrder->setData('base_shipping_refunded', $vendorOrder->getData('base_shipping_refunded') +
            $creditmemo->getData('base_shipping_amount'));
        $vendorOrder->setData('shipping_tax_refunded', $vendorOrder->getData('shipping_tax_refunded') +
            $creditmemo->getData('shipping_tax_amount'));
        $vendorOrder->setData('base_shipping_tax_refunded', $vendorOrder->getData('base_shipping_tax_refunded') +
            $creditmemo->getData('base_shipping_tax_amount'));
        $vendorOrder->setData('subtotal_refunded', $vendorOrder->getData('subtotal_refunded') +
            $creditmemo->getData('subtotal'));
        $vendorOrder->setData('base_subtotal_refunded', $vendorOrder->getData('base_subtotal_refunded') +
            $creditmemo->getData('base_subtotal'));
        $vendorOrder->setData('discount_refunded', $vendorOrder->getData('discount_refunded') +
            $creditmemo->getData('discount_amount'));
        $vendorOrder->setData('base_discount_refunded', $vendorOrder->getData('base_discount_refunded') +
            $creditmemo->getData('base_discount_amount'));
        $vendorOrder->setData('tax_refunded', $vendorOrder->getData('tax_refunded') +
            $creditmemo->getData('tax_amount'));
        $vendorOrder->setData('base_tax_refunded', $vendorOrder->getData('base_tax_refunded') +
            $creditmemo->getData('base_tax_amount'));
        $vendorOrder->setData('base_adjustment_positive', $vendorOrder->getData('base_adjustment_positive') +
            $creditmemo->getData('base_adjustment_positive'));
        $vendorOrder->setData('adjustment_positive', $vendorOrder->getData('adjustment_positive') +
            $creditmemo->getData('adjustment_positive'));
        $vendorOrder->setData('base_adjustment_negative', $vendorOrder->getData('base_adjustment_negative') +
            $creditmemo->getData('base_adjustment_negative'));
        $vendorOrder->setData('adjustment_negative', $vendorOrder->getData('adjustment_negative') +
            $creditmemo->getData('adjustment_negative'));
        $vendorOrder->setData('main_order', $order);
        $vendorOrder->setData('refunded_process', 1);
        if (floatval($order->getData('base_credit_amount')) > 0) {
            if ($creditmemo->getIsSendToWallet()) {
                /* store credit module hack*/
                $vendorOrder->setData('total_refunded', $vendorOrder->getData('total_refunded') +
                    $creditmemo->getData('grand_total') + $creditmemo->getData('credit_total_refunded') -
                    $creditmemo->getRefundToWallet());
                $vendorOrder->setData('base_total_refunded', $vendorOrder->getData('base_total_refunded') +
                    $creditmemo->getData('base_grand_total')  +
                    $creditmemo->getData('base_credit_total_refunded') - $creditmemo->getRefundToWallet());
            } else {
                
                $vendorOrder->setData('total_refunded', $vendorOrder->getData('total_refunded') +
                    $creditmemo->getData('grand_total') + $creditmemo->getData('credit_total_refunded'));
                $vendorOrder->setData('base_total_refunded', $vendorOrder->getData('base_total_refunded') +
                    $creditmemo->getData('base_grand_total')  +
                    $creditmemo->getData('base_credit_total_refunded'));
            }
            
        } else {
            $vendorOrder->setData('total_refunded', $vendorOrder->getData('total_refunded') +
                $creditmemo->getData('grand_total'));
            $vendorOrder->setData('base_total_refunded', $vendorOrder->getData('base_total_refunded') +
                $creditmemo->getData('base_grand_total'));
        }
        try {
            $vendorOrder->save();
            if ($vendorOrder->getData('po_generated') == 1) {
                $this->commissionPayment->updatePO($vendorOrder);
            }
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }
}
