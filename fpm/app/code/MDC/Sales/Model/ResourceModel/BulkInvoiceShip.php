<?php

namespace MDC\Sales\Model\ResourceModel;

/**
 * Class BulkInvoiceShip
 * @package MDC\Sales\Model\ResourceModel
 */
class BulkInvoiceShip extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * BulkInvoiceShip constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('sales_suborder_bulk_invoice_shipment', 'bulk_import_id');
    }
}