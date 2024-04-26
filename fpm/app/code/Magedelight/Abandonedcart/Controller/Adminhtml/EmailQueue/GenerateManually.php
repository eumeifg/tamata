<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;

class GenerateManually extends \Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue
{
    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
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
     * Generate Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->helper->isAbandonedcartEnabled()) {
            $this->messageManager->addError(__('Please enable extension to generate abandoned cart emails queue'));
            return $resultRedirect->setPath('*/*/');
        }
        
        try {
            if ($this->helper->generateAbandonedcartQueue() == true) {
                $this->messageManager->addSuccess(__('The Email Queue generated successfully!'));
            } else {
                $this->messageManager->addError(__('Error while Generating email queue or extension is not enabled!'));
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
