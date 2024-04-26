<?php

namespace Ktpl\Warehousemanagement\Block\Adminhtml\Warehousemanagement\Edit;

class CustomReturnHtml extends \Magento\Backend\Block\Template
{
    const SCAN_LIMIT        = 'warehousemanagement/general/scanlimit';
    const DEL_VEN_TO_WRHSE  = 'warehousemanagement/deliverystatus/vendortowarehouse';
    const DEL_WRHSE_TO_CUST = 'warehousemanagement/deliverystatus/warehousetocustomer';
    const RET_CUST_TO_WRHSE = 'warehousemanagement/returnsstatus/vendortowarehouse';
    const RET_WRHSE_TO_VEN  = 'warehousemanagement/returnsstatus/warehousetocustomer';

    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'customhtml/customreturngrid.phtml';

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param array  $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve number of barcode scan
     *
     * @return int
     */
    public function getScanLimit()
    {
        return $this->_scopeConfig->getValue(self::SCAN_LIMIT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Delivery vendor to warehouse status
     *
     * @return string
     */
    public function getDeliveryVendortoWarehouseStatus()
    {
        return $this->_scopeConfig->getValue(self::DEL_VEN_TO_WRHSE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Delivery warehouse to customer status
     *
     * @return string
     */
    public function getDeliveryWarehousetoCustomer()
    {
        return $this->_scopeConfig->getValue(self::DEL_WRHSE_TO_CUST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Returns customer to warehouse status
     *
     * @return string
     */
    public function getReturnsVendortoWarehouse()
    {
        return $this->_scopeConfig->getValue(self::RET_CUST_TO_WRHSE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Returns warehouse to customer status
     *
     * @return string
     */
    public function getReturnsWarehousetoCustomer()
    {
        return $this->_scopeConfig->getValue(self::RET_WRHSE_TO_VEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
