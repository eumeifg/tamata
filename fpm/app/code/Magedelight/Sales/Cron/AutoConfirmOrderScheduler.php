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
namespace Magedelight\Sales\Cron;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @author Rocket Bazaar Core Team
 * Created at 12 May, 2016 05:05:27 PM
 */
class AutoConfirmOrderScheduler
{
    const CONFIG_PATH_ORDER_AUTO_CONFIRMED = 'vendor_sales/order/auto_confirm';
    const CONFIG_PATH_ORDER_AUTO_CONFIRMED_DELAY_STATUSES = 'vendor_sales/order/auto_confirm_delay';

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $vendorOrderCollFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var array
     */
    protected $websites = [];

    /**
     *
     * @param \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->vendorOrderCollFactory = $vendorOrderCollFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_eventManager = $eventDispatcher;
    }

    /**
     * describes that auto confirm is enabled for order by admin
     * @return boolean
     */
    private function _isAutoConfirmEnabled($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_ORDER_AUTO_CONFIRMED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     *
     * @return Array
     */
    public function _getAutoConfirmDelayStatuses($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_ORDER_AUTO_CONFIRMED_DELAY_STATUSES,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * order confirm scheduler in case of auto confirm
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $this->setAutoConfirmEnableWebsites();

        /* unconfirmed orders */
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('is_confirmed', 0);
        foreach ($orders as $order) {
            $orderWebsiteId = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
            if (in_array($orderWebsiteId, $this->websites)) {
                $statuses = $this->_getAutoConfirmDelayStatuses($orderWebsiteId);
                if (!is_array($statuses) || !in_array($order->getStatus(), $statuses)) {
                    $order->setData('is_confirmed', 1);
                    $order->save();
                    $this->_eventManager->dispatch(
                        'vendor_order_admin_confirm_cron',
                        ['order' => $order, 'website_id' => $orderWebsiteId]
                    );
                } elseif ($order->getStatus() == 'canceled') {
                    /* cancel vendor orders if main order is cancelled.*/
                    $vendorOrders = $this->vendorOrderCollFactory->create()
                        ->addFieldToFilter('order_id')
                        ->addFieldToFilter('status', ['neq' => 'canceled']);
                    foreach ($vendorOrders as $vendorOrder) {
                        $vendorOrder->registerCancel($order, true);
                        $vendorOrder->setData('cancelled_by', CancelledBy::MERCHANT);
                        $vendorOrder->save();
                    }
                }
            }
        }
        $this->cancelVendorOrders();
    }

    /**
     * Set websites which has admin order auto confirm feature enable
     */
    protected function setAutoConfirmEnableWebsites()
    {
        if (empty($this->websites)) {
            foreach ($this->storeManager->getWebsites() as $website) {
                $websiteId = $website->getId();
                if ($this->_isAutoConfirmEnabled($websiteId)) {
                    $this->websites[] = $websiteId;
                }
            }
        }
    }

    /**
     * Cancel all vendor orders for which 'Main Orders' status is canceled.
     */
    protected function cancelVendorOrders()
    {
        $vendorOrders = $this->vendorOrderCollFactory->create()
            ->addFieldToFilter('main_table.status', ['neq' => 'canceled'])
            ->addFieldToFilter('sales_order.status', ['eq' => 'canceled'])
            ->addFieldToFilter('sales_order.is_confirmed', ['eq' => 0]);
        $vendorOrders->getSelect()->joinLeft(
            ["sales_order" => "sales_order"],
            "sales_order.entity_id = main_table.order_id",
            ["sales_order.status","sales_order.is_confirmed"]
        );
        foreach ($vendorOrders as $vendorOrder) {
            $mainOrder = $vendorOrder->getOriginalOrder();
            $vendorOrder->registerCancel($mainOrder, true);
            $vendorOrder->setData('cancelled_by', CancelledBy::MERCHANT);
            $vendorOrder->save();
        }
    }
}
