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

class ResendConfirmation extends \Magedelight\Vendor\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $vendorId = (int) $this->getRequest()->getParam('vendor_id');
        if (!$vendorId) {
            $resultRedirect->setPath('vendor/index');
            return $resultRedirect;
        }

        try {
            $vendor = $this->_vendorRepository->getById($vendorId);

            $this->vendorAccountManagement->sendEmailConfirmation($vendor, '');
            $this->messageManager->addSuccess(__(
                '%1 will receive a confirmation email of registration.',
                ($vendor->getName()) ? $vendor->getName() : 'Vendor'
            ));
        } catch (\Exception $exception) {
            $this->messageManager->addException(
                $exception,
                __('Something went wrong while sending confirmation email.')
            );
        }
        $resultRedirect->setPath(
            'vendor/*/edit',
            ['id' => $vendorId,'website_id'=>$this->getRequest()->getParam('website_id'), '_current' => true]
        );
        return $resultRedirect;
    }
}
