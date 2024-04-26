<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Controller\Adminhtml\VendorGroupCommission;

use MDC\Commissions\Controller\Adminhtml\VendorGroupCommission;

class Index extends VendorGroupCommission
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu('MDC_Commissions::vendor_group_commission');
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Group Commission'));
        return $resultPage;
    }
}
