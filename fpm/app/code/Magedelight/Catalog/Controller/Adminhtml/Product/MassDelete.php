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

class MassDelete extends \Magedelight\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('vendor_product');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                $collection = $this->vendorProduct->getCollection()
                    ->addFieldToFilter('main_table.vendor_product_id', ['in' => $ids]);
                $collection->getSelect()->joinLeft(
                    ['rbv' => 'md_vendor'],
                    "main_table.vendor_id = rbv.vendor_id",
                    [
                        'rbv.email'
                    ]
                );
                /* Prepare vendor-wise data. */
                $notificationData = [];
                foreach ($collection as $data) {
                    $notificationData[$data->getVendorId()]['email'] = $data->getEmail();
                    $notificationData[$data->getVendorId()]['vendor_name'] = $data->getVendorName();
                    $notificationData[$data->getVendorId()]['offer_skus'][] = $data->getVendorSku();
                    $notificationData[$data->getVendorId()]['store_id'] = $data->getStoreId();
                }
                /* Prepare vendor-wise data. */

                $connection = $this->vendorProductResource->getConnection();
                $deletedRows = $connection->delete(
                    $this->vendorProductResource->getMainTable(),
                    ['vendor_product_id IN (?)' => $ids]
                );
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $deletedRows)
                );
                $eventParams = ['ids' => $ids,'notification_data' => $notificationData];
                $this->_eventManager->dispatch('vendor_product_massdelete_after', $eventParams);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
