<?php

namespace CAT\Custom\Controller\Adminhtml\Automation;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 * @package CAT\Custom\Controller\Adminhtml\Automation
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