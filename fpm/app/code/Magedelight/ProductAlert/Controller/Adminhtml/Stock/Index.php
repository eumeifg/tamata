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
namespace Magedelight\ProductAlert\Controller\Adminhtml\Stock;

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
        $pageResult = $this->resultPageFactory->create();

        $pageResult->getLayout();

        $pageResult->setActiveMenu('Magedelight_ProductAlert::md_stock');

        $pageResult->addBreadcrumb(__('Alerts'), __('Stock Alerts'));

        $pageResult->addContent($pageResult->getLayout()->createBlock('Magedelight\ProductAlert\Block\Adminhtml\Stock'));

        $this->_helper->addMessage();

        $pageResult->getConfig()->getTitle()->prepend(__('Stock Alerts '));

        return $pageResult;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_ProductAlert::stock');
    }
}
