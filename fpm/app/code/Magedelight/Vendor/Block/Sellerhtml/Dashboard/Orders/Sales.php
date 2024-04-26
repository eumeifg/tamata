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

class Sales extends \Magedelight\Vendor\Block\Sellerhtml\Dashboard\AbstractDashboard
{

    /**
     * @var array
     */
    protected $_totals = [];

    /**
     * @var \Magento\Directory\Model\Currency|null
     */
    protected $_currentCurrencyCode = null;

    /**
     *
     * @var type
     */
    protected $_template = 'Magedelight_Vendor::dashboard/sale_summary.phtml';

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory $collectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory $collectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_collectionFactory = $collectionFactory;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * @param string $period
     * @param int $vendorId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function getCollection($period = '24h', $vendorId = 0)
    {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam(
            'store'
        ) || $this->getRequest()->getParam(
            'website'
        ) || $this->getRequest()->getParam(
            'group'
        );
        //$period = $this->getRequest()->getParam('period', '24h');

        /* @var $collection \Magento\Reports\Model\ResourceModel\Order\Collection */
        $collection = $this->_collectionFactory->create()
            ->addCreateAtPeriodFilter($period)
            ->calculateTotals($isFilter);

        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('main_table.store_id', $this->getRequest()->getParam('store'));
        } else {
            if ($this->getRequest()->getParam('website')) {
                $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
                $collection->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);
            } else {
                if ($this->getRequest()->getParam('group')) {
                    $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
                    $collection->addFieldToFilter(
                        'main_table.store_id',
                        ['in' => $storeIds]
                    );
                } elseif (!$collection->isLive()) {
                    $collection->addFieldToFilter(
                        'main_table.store_id',
                        ['eq' => $this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()]
                    );
                }
            }
        }

        $select = $collection->getSelect();

        if ($where = $select->getPart('where')) {
            $where = str_replace('`created_at`', 'main_table.`created_at`', $where);
            $select->setPart('where', $where);
        }

        if ($collection->isLive()) {
            $collection->getSelect()->joinLeft(
                ["rvo" => "md_vendor_order"],
                "main_table.entity_id = rvo.order_id",
                ["rvo.vendor_id", "rvo.increment_id", "rvo.status"]
            );
        } else {
            $collection->getSelect()->joinLeft(
                ["rvo" => "md_vendor_order"],
                "main_table.id = rvo.order_id",
                ["rvo.vendor_id", "rvo.increment_id", "rvo.status"]
            );
        }

        $collection->addFieldToFilter('rvo.vendor_id', ['eq' => $vendorId]);
        $collection->addFieldToFilter('rvo.status', ['nin' => ['canceled']]);
        $collection->addFieldToFilter('rvo.is_confirmed', ['eq' => 1]);

        $collection->load();

        $totals = $collection->getFirstItem();
        $this->addTotal(__('Revenue'), $totals->getRevenue());
        $this->addTotal(__('Quantity'), $totals->getQuantity() * 1, true);
        return $collection;
    }

    /**
     * Process collection after loading
     *
     * @return $this
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
     * @return array
     */
    public function getTotals()
    {
        return $this->_totals;
    }

    /**
     * @param string $label
     * @param float $value
     * @param bool $isQuantity
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addTotal($label, $value, $isQuantity = false)
    {
        /* if (!$isQuantity) {
          $value = $this->format($value);
          $decimals = substr($value, -2);
          $value = substr($value, 0, -2);
          } else {
          $value = ($value != '')?$value:0;
          $decimals = '';
          } */
        if (!$isQuantity) {
            $value = $this->format($value);
        }
        $decimals = '';
        $this->_totals[$label->getText()] = ['label' => $label, 'value' => $value, 'decimals' => $decimals];

        return $this;
    }

    /**
     * Formatting value specific for this store
     *
     * @param float $price
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function format($price)
    {
        return $this->getCurrency()->format($price);
    }

    /**
     * Setting currency model
     *
     * @param \Magento\Directory\Model\Currency $currency
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    /**
     * Retrieve currency model if not set then return currency model for current store
     *
     * @return \Magento\Directory\Model\Currency
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrency()
    {
        if ($this->_currentCurrencyCode === null) {
            if ($this->getRequest()->getParam('store')) {
                $this->_currentCurrencyCode = $this->_storeManager->getStore(
                    $this->getRequest()->getParam('store')
                )->getBaseCurrency();
            } elseif ($this->getRequest()->getParam('website')) {
                $this->_currentCurrencyCode = $this->_storeManager->getWebsite(
                    $this->getRequest()->getParam('website')
                )->getBaseCurrency();
            } elseif ($this->getRequest()->getParam('group')) {
                $this->_currentCurrencyCode = $this->_storeManager->getGroup(
                    $this->getRequest()->getParam('group')
                )->getWebsite()->getBaseCurrency();
            } else {
                $this->_currentCurrencyCode = $this->_storeManager->getStore()->getBaseCurrency();
            }
        }
        return $this->_currentCurrencyCode;
    }
}
