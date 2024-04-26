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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Store\Model\ScopeInterface;

class Order extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $vendorOrderFactory;

    const BILLING_ALIAS = 'billing_o_a';
    const SHIPPING_ALIAS = 'shipping_o_a';
    const BILL_TO_FIRST_NAME_FIELD = 'billing_o_a.firstname';
    const BILL_TO_LAST_NAME_FIELD = 'billing_o_a.lastname';
    const SHIP_TO_FIRST_NAME_FIELD = 'shipping_o_a.firstname';
    const SHIP_TO_LAST_NAME_FIELD = 'shipping_o_a.lastname';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orders;
    protected $_tabs;
    protected $cancelledBy;

    protected $storeIds = [];

    /**
     * @var VendorOrder\Listing
     */
    protected $subOrdersListing;

    /**
     * Order constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param CancelledBy $cancelledBy
     * @param VendorOrder\Listing $subOrdersListing
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Store\Model\StoreRepository $storeRepository,
        CancelledBy $cancelledBy,
        \Magedelight\Sales\Model\Order\Listing $subOrdersListing,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->authSession = $authSession;
        $this->_orderConfig = $orderConfig;
        $this->resourceConnection = $resourceConnection;
        $this->date = $date;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->_storeRepository = $storeRepository;
        $this->cancelledBy = $cancelledBy;
        $this->subOrdersListing = $subOrdersListing;
        parent::__construct($context, $data);
        $this->_tabs = '2,0';
    }

    /**
     * @param $date
     * @return string
     */
    public function getCurrentLocaleDate($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
    }

    /**
     * @param $_order
     * @return mixed
     */
    public function getVendorOrder($_order)
    {
        return $this->vendorOrderFactory->create()->load($_order->getRvoVendorOrderId());
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection | \Magedelight\Sales\Model\ResourceModel\Order\Collection
     */
    public function getNewOrders($vendorId = 0, $skipfilterSearch = false)
    {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!($vendorId)) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if (isset($paramsData['session-clear-ordernew']) && $paramsData['session-clear-ordernew'] == "1") {
            $orders_active_new = $grid_session['grid_session']['orders_active_new'];
            foreach ($orders_active_new as $key => $val) {
                if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                    $orders_active_new[$key] = '';
                }
            }
            $grid_session['grid_session']['orders_active_new'] = $orders_active_new;
            $this->setGridSession($grid_session);
        } elseif (isset($paramsData['sfrm']) && isset($paramsData['sort_order']) && $paramsData['sfrm'] == 'new') {
            foreach ($paramsData as $key => $val) {
                $orders_active_new[$key] = $val;
            }
            $grid_session['grid_session']['orders_active_new'] = $orders_active_new;
            $this->setGridSession($grid_session);
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->getSelect()->join(
            ["main_order"=>"sales_order"],
            "main_order.entity_id = main_table.order_id and main_order.is_confirmed = 1",
            ["main_order.entity_id"]
        );
        $includeStatuses = [VendorOrder::STATUS_PENDING, VendorOrder::STATUS_PROCESSING, VendorOrder::STATUS_CONFIRMED];
        $collection
            ->addFieldToFilter(
                'main_table.status',
                ['in' => $includeStatuses]
            )->addFieldToFilter(
                'main_table.is_confirmed',
                ['eq' => 0]
            );
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        /* return collection if it is for summary */
        if ($skipfilterSearch) {
            return $collection;
        }

        if (isset($grid_session['grid_session']['orders_active_new']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_new']['q']
            );
        }

        if (!empty($paramsData) && isset($paramsData['sort_order']) && isset($paramsData['sfrm'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_new'],
                'orders_active_new',
                $grid_session['grid_session']['orders_active_new']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_active_new']['sort_order'];
        $direction = $grid_session['grid_session']['orders_active_new']['dir'];

        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    /*
     * @return \Magedelight\Vendor\Model\Vendor
     */
    /**
     * @return \Magento\User\Model\User|null
     */
    public function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getConfirmOrders(
        $includeStatuses = [VendorOrder::STATUS_PENDING, VendorOrder::STATUS_PROCESSING, VendorOrder::STATUS_CONFIRMED],
        $skipfilterSearch = false,
        $vendorId = 0
    ) {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!($vendorId)) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if (isset($paramsData['session-clear-order']) && $paramsData['session-clear-order'] == "1") {
            $orders_active_confirm = $grid_session['grid_session']['orders_active_confirm'];
            foreach ($orders_active_confirm as $key => $val) {
                if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                    $orders_active_confirm[$key] = '';
                }
            }
            $grid_session['grid_session']['orders_active_confirm'] = $orders_active_confirm;
            $this->setGridSession($grid_session);
        } elseif (isset($paramsData['sfrm']) &&
            isset($paramsData['sort_order']) &&
            $paramsData['sfrm'] == 'confirmed') {
            foreach ($paramsData as $key => $val) {
                $orders_active_confirm[$key] = $val;
            }
            $grid_session['grid_session']['orders_active_confirm'] = $orders_active_confirm;
            $this->setGridSession($grid_session);
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->addFieldToFilter(
            'main_table.status',
            ['in' => $includeStatuses]
        )->addFieldToFilter(
            'main_table.is_confirmed',
            ['eq' => 1]
        );
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        /* return collection if it is for summary */
        if ($skipfilterSearch) {
            return $collection;
        }

        if (isset($grid_session['grid_session']['orders_active_confirm']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_confirm']['q']
            );
        }

        if (!empty($paramsData) && isset($paramsData['sort_order']) && isset($paramsData['sfrm'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_confirm'],
                'orders_active_confirm',
                $grid_session['grid_session']['orders_active_confirm']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_active_confirm']['sort_order'];
        $direction = $grid_session['grid_session']['orders_active_confirm']['dir'];

        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    /**
     * @param $collection
     * @param $data
     * @param $type
     * @param $filterData
     */
    protected function _addFiltersToCollection($collection, $data, $type, $filterData)
    {
        $purchase_date_from = urldecode($data['pdate_from']);
        $purchase_date_to = urldecode($data['pdate_to']);

        if (!empty($purchase_date_from)) {
            $collection->addFieldToFilter(
                'main_table.created_at',
                ['gteq' => $this->date->gmtDate(null, $purchase_date_from)]
            );
        }

        if (!empty($purchase_date_to)) {
            $collection->addFieldToFilter(
                'main_table.created_at',
                ['lteq' => $this->date->gmtDate(
                    null,
                    strtotime('+23 hours 59 minutes 59 seconds', strtotime($purchase_date_to))
                )]
            );
        }

        if (!empty($data['grndt_from'])) {
            $collection->addFieldToFilter(
                'main_table.grand_total',
                ['gteq' => $data['grndt_from']]
            );
        }

        if (!empty($data['grndt_to'])) {
            $collection->addFieldToFilter(
                'main_table.grand_total',
                ['lteq' => $data['grndt_to']]
            );
        }

        if ($type == "orders_cancel") {
            if (!empty($data['cf_from'])) {
                $collection->addFieldToFilter(
                    'rvp.cancellation_fee',
                    ['gteq' => $data['cf_from']]
                );
            }
            if (!empty($data['cf_to'])) {
                $collection->addFieldToFilter(
                    'rvp.cancellation_fee',
                    ['lteq' => $data['cf_to']]
                );
            }
        }

        if ( isset($data['increment_id']) && !empty($data['increment_id'])) {
            $collection->addFieldToFilter(
                'main_table.increment_id',
                ['eq' => $data['increment_id']]
            );
        }

        if (isset($data['bill_to_name']) && !empty($data['bill_to_name'])) {
            $name = preg_replace('/[^A-Za-z0-9\. -]/', '', $data['bill_to_name']);
            $collection->getSelect()->where("CONCAT_WS(' '," . self::BILL_TO_FIRST_NAME_FIELD . ","
                . self::BILL_TO_LAST_NAME_FIELD . ") LIKE '%" . trim($name) . "%'");
        }

        if (isset($data['ship_to_name']) && !empty($data['ship_to_name'])) {
            $name = preg_replace('/[^A-Za-z0-9\. -]/', '', $filterData['ship_to_name']);
            $collection->getSelect()->where("CONCAT_WS(' '," . self::SHIP_TO_FIRST_NAME_FIELD . ","
                . self::SHIP_TO_LAST_NAME_FIELD . ") LIKE '%" . trim($name) . "%'");
        }

        if (isset($data['status']) && !empty($data['status'])) {
            $collection->addFieldToFilter(
                'main_table.status',
                ['eq' => $data['status']]
            );
        }
    }

    /**
     * @param $collection
     * @param $sortOrder
     * @param $direction
     */
    protected function _addSortOrderToCollection($collection, $sortOrder, $direction)
    {
        if ($sortOrder != '') {
            $collection->getSelect()->order($sortOrder . ' ' . $direction);
        }
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('rbsales/order/view', ['id' => $order->getRvoVendorOrderId(), 'tab' => $this->_tabs]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getEditUrl($order)
    {
        return $this->getUrl('rbsales/order/edit', ['order_id' => $order->getId(), 'tab' => $this->_tabs]);
    }

    /**
     * @param object $_order
     * @return string
     */
    public function getShipmentUrl($_order)
    {
        return $this->getUrl(
            'rbsales/order_shipment/new',
            [
                'vendor_order_id' => $_order->getVendorOrderId(),
                'order_id' => $_order->getOrderId(),
                'tab' => $this->_tabs
            ]
        );
    }

    /**
     * @param object $vendorOrder
     * @return string
     */
    public function getInvoiceUrl($vendorOrder)
    {
        return $this->getUrl(
            'rbsales/order_invoice/new',
            [
                'vendor_order_id' => $vendorOrder->getVendorOrderId(),
                'order_id' => $vendorOrder->getOrderId(),
                'tab' => $this->_tabs
            ]
        );
    }

    /**
     * @param $_order
     * @return string
     */
    public function getPackingSlipUrl($_order)
    {
        return $this->getUrl(
            'rbsales/order/pdfshipments',
            [
                'vendor_order_id' => $_order->getVendorOrderId(),
                'order_id' => $_order->getOrderId(),
                'tab' => $this->getTab()
            ]
        );
    }

    /**
     * @param \Magedelight\Sales\Api\Data\VendorOrderInterface $vendorOrder
     * @return string
     */
    public function getPrintUrl($vendorOrder, $tab = '2,0')
    {
        return $this->getUrl('rbsales/order/print', ['id' => $vendorOrder->getId(), 'tab' => $this->_tabs]);
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('rbsales/order/index', ['tab' => $this->_tabs]);
    }

    /**
     * @return int|string
     */
    public function getCountOrdersToBeConfirm()
    {
        return ($newOrders = $this->getNewOrders(null, true)) ? $newOrders->count() : '';
    }

    /**
     * @return int
     */
    public function getCountOrdersToBeHandover()
    {
        $includeStatuses = [VendorOrder::STATUS_PACKED];
        return $this->getConfirmOrders($includeStatuses, true)->count();
    }

    /**
     * @return int
     */
    public function getCountOrdersToBePacked()
    {
        $includeStatuses = [VendorOrder::STATUS_PROCESSING, VendorOrder::STATUS_CONFIRMED];
        return $this->getConfirmOrders($includeStatuses, true)->count();
    }

    /**
     * @return int
     */
    public function getCountOrdersToBeInTransit()
    {
        $includeStatuses = [VendorOrder::STATUS_SHIPPED];
        return $this->getConfirmOrders($includeStatuses, true)->count();
    }

    /**
     * @return int
     */
    public function getCountOrdersTobeInDelivered()
    {
        $includeStatuses = [VendorOrder::STATUS_IN_TRANSIT,VendorOrder::STATUS_OUT_WAREHOUSE];
        return $this->getConfirmOrders($includeStatuses, true)->count();
    }

    /**
     * @return int
     */
    public function getCountOrdersDelivered()
    {
        $includeStatuses = [VendorOrder::STATUS_COMPLETE, VendorOrder::STATUS_DELIVERED];
        return $this->getConfirmOrders($includeStatuses, true)->count();
    }

    /**
     * @return int
     */
    public function getCountOrdersClosed()
    {
        $includeStatuses = [VendorOrder::STATUS_CLOSED];
        return $this->getConfirmOrders($includeStatuses, true)->count();
    }

    /**
     * @return string
     */
    public function getCountOrdersToDispatched()
    {
        return "To Do";
    }

    /**
     * @return string
     */
    public function getCountOrdersCancelled()
    {
        return "To Do";
    }

    /**
     * @return string
     */
    public function getCountOrdersDeliveredOnTime()
    {
        return "To Do";
    }

    /**
     * @return string
     */
    public function getCountOrdersDeliveredWithDelay()
    {
        return "To Do";
    }

    /**
     * @return string
     */
    public function getCountOrdersInTransit()
    {
        return "To Do";
    }

    /**
     * @return mixed
     */
    public function getGridSession()
    {
        return $this->authSession->getGridSession();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function setGridSession($data)
    {
        return $this->authSession->setGridSession($data);
    }

    /**
     * @return mixed
     */
    public function getTab()
    {
        return $this->getRequest()->getParam('tab');
    }

    /**
     *
     * @param type $websiteId
     * @return array
     */
    public function getStoresForWebsite($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        }
        if (empty($this->storeIds[$websiteId])) {
            $this->storeIds[$websiteId] = [];
            $stores = $this->_storeRepository->getList();
            foreach ($stores as $store) {
                if ($websiteId == $store["website_id"]) {
                    array_push($this->storeIds[$websiteId], $store["store_id"]);
                }
            }
        }
        return $this->storeIds[$websiteId];
    }

    /**
     *
     * @return bool
     */
    public function isManualShipmentAllowed()
    {
        return $this->_scopeConfig->getValue(
            \Magedelight\Sales\Model\Order::IS_MANUAL_SHIPMENT_ALLOWED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     *
     * @return bool
     */
    public function isMagentoOrderStatusDisplayed()
    {
        return $this->_scopeConfig->getValue(
            \Magedelight\Sales\Model\Order::IS_MAGENTO_ORDER_STATUS_ALLOWED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param $order
     * @return mixed
     */
    public function getOrderSummarizedHtml($order)
    {
        $block = $this->getChildBlock('summarized.order.details');
        if ($this->getParentBlock()) {
            $block = $this->getParentBlock()->getChildBlock('summarized.order.details');
        }
        return $block->setOrder($order)
                ->setVendor($this->getVendor())
                ->setTemplate('Magedelight_Sales::vendor/order/summarized_details.phtml')
                ->toHtml();
    }

    /**
     * @param $orderCancelledBy
     * @return string
     */
    public function getOrderCancelledBy($orderCancelledBy)
    {
        $isCancelledBy = $this->cancelledBy->getOptionText($orderCancelledBy);
        return $isCancelledBy;
    }
}
