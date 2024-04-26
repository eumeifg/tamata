<?php

namespace MDC\Sales\Model\ResourceModel\BulkInvoiceShip;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package MDC\Sales\Model\ResourceModel\BulkInvoiceShip
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'bulk_import_id';

    public function _construct()
    {
        $this->_init('MDC\Sales\Model\BulkInvoiceShip', 'MDC\Sales\Model\ResourceModel\BulkInvoiceShip');
    }
}