<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Slider;

use Ktpl\BannerManagement\Controller\Adminhtml\Slider;

/**
 * Class Delete
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Slider
 */
class Delete extends Slider
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            /**
             * @var \Ktpl\BannerManagement\Model\Banner $banner
            */
            $this->sliderRepository
                ->deleteById($this->getRequest()->getParam('slider_id'));

            $this->messageManager->addSuccess(__('The slider has been deleted.'));
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            $resultRedirect->setPath('bannermanagement/slider/edit', ['slider_id' => $this->getRequest()->getParam('slider_id')]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('bannermanagement/slider/');

        return $resultRedirect;
    }
}
