<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Rma\Controller\Sellerhtml\VendorRequest;

class Index extends \Magedelight\Rma\Controller\Sellerhtml\VendorRequest
{
    /**
     * @return type
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Requested RMA'));
        return $resultPage;
    }
}
