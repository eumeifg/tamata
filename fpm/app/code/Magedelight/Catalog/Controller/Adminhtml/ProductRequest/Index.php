<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Controller\Adminhtml\ProductRequest;

use Magedelight\Vendor\Model\Source\RequestStatuses;

class Index extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * Vendor Request list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $resultPage = $this->resultPageFactory->create();

        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu("Magedelight_Catalog::product_request_new");
        $title = 'Product Requests - New';
        if ($this->getRequest()->getParam('existing')) {
            $title = 'Product Requests - Existing';
            $resultPage->setActiveMenu("Magedelight_Catalog::product_request_existing");
        }
        $status = $this->getRequest()->getParam('status', RequestStatuses::VENDOR_REQUEST_STATUS_PENDING);
        if ($status == RequestStatuses::VENDOR_REQUEST_STATUS_REJECTED) {
            $title = 'Product Requests - Disapproved';
            $resultPage->setActiveMenu("Magedelight_Catalog::vendor_products_disapproved");
        }
        $resultPage->getConfig()->getTitle()->prepend(__($title));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Products'), __('Products'));
        $resultPage->addBreadcrumb(__('Manage Request'), __('Manage Request'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        $status = $this->getRequest()->getParam('status', RequestStatuses::VENDOR_REQUEST_STATUS_PENDING);
        if ($status == RequestStatuses::VENDOR_REQUEST_STATUS_REJECTED) {
            return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products_disapproved');
        } elseif ($this->getRequest()->getParam('existing')) {
            return $this->_authorization->isAllowed('Magedelight_Catalog::product_request_existing');
        } else {
            return $this->_authorization->isAllowed('Magedelight_Catalog::product_request_new');
        }
    }
}
