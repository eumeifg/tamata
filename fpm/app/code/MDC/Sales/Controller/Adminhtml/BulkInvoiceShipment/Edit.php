<?php

namespace MDC\Sales\Controller\Adminhtml\BulkInvoiceShipment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 * @package MDC\Sales\Controller\Adminhtml\BulkInvoiceShipment
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute() {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}