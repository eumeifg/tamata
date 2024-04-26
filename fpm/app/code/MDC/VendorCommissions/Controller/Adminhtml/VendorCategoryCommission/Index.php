<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_VendorCommissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission;

use MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission;

class Index extends VendorCategoryCommission
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu('MDC_VendorCommissions::vendor_category_commission');
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Category Commission'));
        return $resultPage;
    }
}
