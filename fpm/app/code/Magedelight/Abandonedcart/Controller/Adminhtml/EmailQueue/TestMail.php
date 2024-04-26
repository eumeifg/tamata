<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;

use Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;
 
class TestMail extends EmailQueue
{
    
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Send Abandoned cart email manually
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->helper->isAbandonedcartEnabled()) {
            $this->messageManager->addError(__('Please enable extension to send test mail'));
            return $resultRedirect->setPath('adminhtml/system_config/edit', ['section' => 'abandonedcart_section']);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        if ($this->helper->sendTestMail()) {
            $this->messageManager->addSuccess(__('Test Email is sent successfully!'));
        } else {
            $this->messageManager->addError(__('Email Sending Error!'));
        }
        
        return $resultRedirect->setPath('adminhtml/system_config/edit', ['section' => 'abandonedcart_section']);
    }
}
