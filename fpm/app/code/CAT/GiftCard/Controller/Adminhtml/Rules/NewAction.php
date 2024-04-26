<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Class NewAction
 * @package CAT\GiftCard\Controller\Adminhtml\Rules
 */
class NewAction extends Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}