<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Banner;

use Ktpl\BannerManagement\Controller\Adminhtml\Banner;

/**
 * Class Delete
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Banner
 */
class Delete extends Banner
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $bannerId=$this->getRequest()->getParam('id');
        try {
            $this->bannerRepository->deleteById($bannerId);
            $this->messageManager->addSuccess(__('The Banner has been deleted.'));
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            $resultRedirect->setPath('bannermanagement/*/edit', ['id' => $bannerId]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('bannermanagement/*/');

        return $resultRedirect;
    }
}
