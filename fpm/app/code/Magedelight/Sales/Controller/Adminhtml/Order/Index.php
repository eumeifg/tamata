<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Controller\Adminhtml\Order;

use Magedelight\Sales\Model\Order as VendorOrder;

class Index extends \Magedelight\Sales\Controller\Adminhtml\Order
{
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
        $status = $this->getRequest()->getParam('status');
        switch ($status) {
            case 'canceled':
                $resultPage->setActiveMenu("Magedelight_Vendor::vendor_order_listed_canceled");
                $title = 'Cancelled Orders';
                break;
            case 'complete':
                $resultPage->setActiveMenu("Magedelight_Vendor::vendor_order_listed_complete");
                $title = 'Completed Orders';
                break;
            default:
                $resultPage->setActiveMenu("Magedelight_Vendor::vendor_order_listed");
                $title = 'Active Orders';
                break;
           
        }
        $resultPage->getConfig()->getTitle()->prepend(__($title));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Manage Orders'), __('Manage Orders'));
        $resultPage->addBreadcrumb(__($title), __($title));

        return $resultPage;
    }
}
