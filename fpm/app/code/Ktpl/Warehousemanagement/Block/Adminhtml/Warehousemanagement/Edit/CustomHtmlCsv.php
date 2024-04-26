<?php

namespace Ktpl\Warehousemanagement\Block\Adminhtml\Warehousemanagement\Edit;

class CustomHtmlCsv extends \Magento\Backend\Block\Widget
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
    protected $_template = 'customhtml/customuploadcsv.phtml';

    protected $warehouseHelper;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param array  $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ktpl\Warehousemanagement\Helper\Data $warehouseHelper,
        array $data = []
    ) {
        $this->warehouseHelper = $warehouseHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve number of barcode scan
     *
     * @return int
     */
    public function getScanLimit()
    {
        return $this->warehouseHelper->getScanLimit();
    }

    /**
     * Retrieve status of Delivery vendor to warehouse status
     *
     * @return string
     */
    public function getDeliveryVendortoWarehouseStatus()
    {
        return $this->warehouseHelper->getDeliveryVendortoWarehouseStatus();
    }

    /**
     * Retrieve status of Delivery warehouse to customer status
     *
     * @return string
     */
    public function getDeliveryWarehousetoCustomer()
    {
        return $this->warehouseHelper->getDeliveryWarehousetoCustomer();
    }

    /**
     * Retrieve status of Returns customer to warehouse status
     *
     * @return string
     */
    public function getReturnsVendortoWarehouse()
    {
        return $this->warehouseHelper->getReturnsVendortoWarehouse();
    }

    /**
     * Retrieve status of Returns warehouse to customer status
     *
     * @return string
     */
    public function getReturnsWarehousetoCustomer()
    {
        return $this->warehouseHelper->getReturnsWarehousetoCustomer();
    }
}
