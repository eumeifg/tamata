<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Controller\Adminhtml\Price;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $_helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\ProductAlert\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getLayout();

        $resultPage->setActiveMenu('Magedelight_ProductAlert::md_price');

        $resultPage->addBreadcrumb(__('Alerts'), __('Price Alerts'));

        $resultPage->addContent($resultPage->getLayout()->createBlock('Magedelight\ProductAlert\Block\Adminhtml\Price'));

        $this->_helper->addMessage();

        $resultPage->getConfig()->getTitle()->prepend(__('Price Alerts'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_ProductAlert::price');
    }
}
