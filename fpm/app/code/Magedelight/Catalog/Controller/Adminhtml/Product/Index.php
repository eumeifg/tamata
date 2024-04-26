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
namespace Magedelight\Catalog\Controller\Adminhtml\Product;

use Magedelight\Catalog\Model\Product as VendorProduct;

class Index extends \Magedelight\Catalog\Controller\Adminhtml\Product
{
    /**
     * Vendor product list action
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
        $status = $this->getRequest()->getParam(VendorProduct::STATUS_PARAM_NAME, VendorProduct::STATUS_UNLISTED);
        $title = 'Non Live Products';
        switch ($status) {
            case VendorProduct::STATUS_UNLISTED:
                $title = 'Non-Live(Approved) Products';
                $resultPage->setActiveMenu("Magedelight_Catalog::vendor_products_listed");
                break;
            case VendorProduct::STATUS_LISTED:
                $title = 'Live Products';
                $resultPage->setActiveMenu("Magedelight_Catalog::vendor_products_unlisted");
                break;
        }
        $resultPage->getConfig()->getTitle()->prepend(__($title));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Products'), __('Products'));
        $resultPage->addBreadcrumb(__('Manage Products'), __('Manage Products'));

        return $resultPage;
    }
}
