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
namespace Magedelight\Sales\Model\Order;

use Magedelight\Sales\Api\OrderRepositoryInterface;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;
use Magedelight\Sales\Model\Order\SplitOrder\OrderProcessor;
use Magedelight\Sales\Model\Order\SplitOrder\ShippingProcessor;
use Magedelight\Sales\Model\Order\SplitOrder\TaxProcessor;
use Magedelight\Sales\Model\OrderFactory as VendorOrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Rocket Bazaar Core Team
 * Created at 31 Dec , 2019 11:30:00 AM
 */
class SplitOrder extends \Magento\Framework\DataObject
{

    /**
     * @var VendorOrderFactory
     */
    protected $vendorOrderFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderProcessor
     */
    protected $orderProcessor;

    /**
     * @var ShippingProcessor
     */
    protected $shippingProcessor;

    /**
     * @var TaxProcessor
     */
    protected $taxProcessor;

    /**
     * @var DiscountProcessor
     */
    protected $discountProcessor;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * SplitOrder constructor.
     * @param VendorOrderFactory $vendorOrderFactory
     * @param EventManagerInterface $eventManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param OrderProcessor $orderProcessor
     * @param ShippingProcessor $shippingProcessor
     * @param TaxProcessor $taxProcessor
     * @param DiscountProcessor $discountProcessor
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        VendorOrderFactory $vendorOrderFactory,
        EventManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        OrderProcessor $orderProcessor,
        ShippingProcessor $shippingProcessor,
        TaxProcessor $taxProcessor,
        DiscountProcessor $discountProcessor,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->orderProcessor = $orderProcessor;
        $this->shippingProcessor = $shippingProcessor;
        $this->taxProcessor = $taxProcessor;
        $this->discountProcessor = $discountProcessor;
        $this->orderRepository = $orderRepository;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function execute($order)
    {
        /**
         * @todo get shipping information from main order if dropship enabled
         * $shippingMethods = $order->getData('shipping_method_detail');
         * $shippingMethods = ($shippingMethods ? unserialize($shippingMethods) : array());
         */
        /** @var $items \Magento\Sales\Model\Order\Item */
        try {
            $subOrders = $this->orderProcessor->process($order);
            foreach ($subOrders as $subOrderData) {

                /* Prepare data for vendor/sub order.*/
                $subOrderData = $this->shippingProcessor->process(
                    $order,
                    $subOrderData,
                    $this->orderProcessor->getTotalItems()
                );
                $subOrderData = $this->discountProcessor->process($order, $subOrderData);
                $subOrderData = $this->taxProcessor->process($subOrderData);
                /* Prepare data for vendor/sub order.*/
                /**
                 * @todo Commission calculation for invocing
                 */
                $vendorOrder = $this->getProcessedVendorOrder($order, $subOrderData);

                $this->eventManager->dispatch('vendor_order_save_before', ['vendor_order' => $vendorOrder]);
                $vendorOrder = $this->orderRepository->save($vendorOrder);

                foreach ($order->getAllVisibleItems() as $item) {
                    $itemId = (!empty($item->getParentItem())) ?
                        $item->getParentItem()->getQuoteItemId() : $item->getQuoteItemId();
                    /* Set vendor order id to order items to identify sub/vendor order items. */
                    if (($itemId == $subOrderData['order_item'])) {
                        $item->setVendorOrderId($vendorOrder->getVendorOrderId());
                    }
                }

                $this->eventManager->dispatch(
                    'vendor_order_place_after',
                    ['vendor_order' => $vendorOrder, 'order' => $order]
                );
                /*
                 * This thing will be done using cron to delay vendor confirmation.
                if ($this->isAutoConfirmEnabled()) {
                $this->eventManager->dispatch(
                    'vendor_order_auto_confirmed',
                    ['vendor_order' => $vendorOrder, 'order_id' => $order->getEntityId()]
                );
                }
                 */
            }

            /**
             * This thing will be done using cron to delay vendor confirmation.
            if ($this->isAutoConfirmEnabled()) {
            $order->setData('is_confirmed', 1);
            }
             *
             */
            $order->setData('is_split', 1)->save();
            $this->eventManager->dispatch('vendor_order_split_after', ['order' => $order]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param $vendorOrder
     * @param $order
     * @param $subOrderData
     * @return mixed
     */
    public function getProcessedVendorOrder($order, $subOrderData)
    {
        $vendorOrder = $this->vendorOrderFactory->create();
        $vendorOrder->setData($order->getData());
        $vendorOrder->setData($subOrderData->getData());
        $vendorOrder->setData('is_new_order', true);
        return $vendorOrder;
    }

    /**
     * describes that auto confirm is enabled for order by admin
     * @return type
     */
    public function isAutoConfirmEnabled($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            VendorOrder::CONFIG_PATH_ORDER_AUTO_CONFIRMED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
