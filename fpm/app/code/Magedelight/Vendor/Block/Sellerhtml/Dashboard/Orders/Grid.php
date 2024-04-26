<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Sellerhtml\Dashboard\Orders;

class Grid extends \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Grid
{
    protected $_template = 'Magedelight_Vendor::dashboard/lastorders.phtml';

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory $collectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory $collectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_collectionFactory = $collectionFactory;
        $this->vendorHelper = $vendorHelper;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lastOrdersGrid');
    }

    /**
     * @param $orderId
     * @return float
     */
    public function getVendorItemsCount($orderId)
    {
        $vendorProdSold = $this->_collectionFactory->create()->calculateProductsSold();
        $vendorProdSold->getSelect()->where('order_id = ?', $orderId);
        $qty = $vendorProdSold->getColumnValues('product_sold');
        return floatval($qty[0]);
    }

    /**
     * @return $this
     * Kirsh - The same data should be display on Dashboard - New Orders and Order - New Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }

        $collection = $this->_collectionFactory->create();
        $collection->getSelect()->group('main_table.entity_id');
        $collection->addItemCountExpr()->joinCustomerName(
            'customer'
        )->orderByCreatedAt();

        if ($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            } elseif ($this->getParam('website')) {
                $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
            } elseif ($this->getParam('group')) {
                $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
            }

            $collection->addRevenueToSelect();
        } else {
            $collection->addRevenueToSelect(true);
        }
        $collection->addFieldToFilter('vendor_id', ['eq' => $this->authSession->getUser()->getVendorId()]);
        $collection->addFieldToFilter("main_table.is_confirmed", ['eq' => 1]);
        $collection->getSelect()->joinLeft(
            ["rvo" => "md_vendor_order"],
            "main_table.entity_id = rvo.order_id",
            ["vendor_order_id","vendor_id","rvo.increment_id","rvo.status"]
        );
        $collection->addFieldToFilter('main_table.status', ['nin'=>'canceled']);
        $collection->addFieldToFilter('rvo.status', ['nin'=>'canceled']);
        $collection->addFieldToFilter('rvo.is_confirmed', ['eq' => 0]);
        $collection->getSelect()->order('main_table.updated_at DESC');

        if (!empty($this->vendorHelper->getConfigValue('vendor/dashboard_summary/new_orders'))) {
            $collection->setPageSize($this->vendorHelper->getConfigValue('vendor/dashboard_summary/new_orders'))
                ->setCurPage(1);
        }
        return $collection;
    }

    /**
     * Process collection after loading
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection() as $item) {
            $item->getCustomer() ?: $item->setCustomer('Guest');
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }

    /**
     * @return string
     */
    public function getNewOrdersUrl()
    {
        return $this->getUrl("rbsales/order/index/tab/2,0");
    }
}
