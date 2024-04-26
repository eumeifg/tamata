<?php

namespace MDC\Sales\Model;
/**
 * Class BulkInvoiceShip
 * @package MDC\Sales\Model
 */
class BulkInvoiceShip extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('MDC\Sales\Model\ResourceModel\BulkInvoiceShip');
    }
}