<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Index;

class Edit extends \Magedelight\Vendor\Controller\Adminhtml\Index
{

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::view_detail_vendor');
    }

    /**
     * Vendor edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $vendor = $this->_initVendor();

        /* Check if vendor exists for the given site.*/
        if ($vendor->getId()) {
            if (!$vendor->getWebsiteId()) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                return $resultRedirect->setPath('vendor/index/');
            }
            /* Assign role to vendor in case of registration initiated
             from seller panel and the rest processed from admin.*/
            $businessName = $vendor->getBusinessName();
            $name = $vendor->getName();
            if ($businessName == "" || $name == "") {
                $eventParams = ['vendor' => $vendor];
                $this->_eventManager->dispatch('vendor_register_success_admin', $eventParams);
            }
            /* Assign role to vendor in case of registration initiated
             from seller panel and the rest processed from admin.*/
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Vendor::vendor_manage');
        $this->prepareDefaultVendorTitle($resultPage);

        if ($vendor->getId()) {
            $resultPage->getConfig()->getTitle()->prepend($this->_viewHelper->getVendorFullBusinessName($vendor));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Vendor'));
        }
        return $resultPage;
    }
}
