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

class Delete extends \Magedelight\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {

            $collection = $this->vendorProduct->getCollection()
                ->addFieldToFilter('main_table.vendor_product_id', ['in' => $id]);
            $collection->getSelect()->joinLeft(
                ['rbv' => 'md_vendor'],
                "main_table.vendor_id = rbv.vendor_id",
                [
                    'rbv.email'
                ]
            );
            $offer  = $collection->getFirstItem();
            $status = $offer->getStatus();
            /* Prepare data. */
            $notificationData['email'] = $offer->getEmail();
            $notificationData['vendor_name'] = $offer->getVendorName();
            $notificationData['vendor_id'] = $offer->getVendorId();
            $notificationData['offer_sku'] = $offer->getVendorSku();
            $notificationData['store_id'] = $offer->getStoreId();
            /* Prepare data. */

            $this->vendorProduct->deleteVendorOffer(
                $this->vendorProduct->getMarketplaceProductId(),
                $this->vendorProduct->getVendorId()
            );
            $this->messageManager->addSuccessMessage(
                __('Deleted successfully !')
            );
            $eventParams = [
                'id' => $id,
                'notification_data' => $notificationData
            ];
            $this->_eventManager->dispatch('vendor_product_approve_delete', $eventParams);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect('*/*/', ['status' => $status]);
    }
}
