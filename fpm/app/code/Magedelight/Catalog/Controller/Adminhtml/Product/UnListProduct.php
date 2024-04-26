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

use Magedelight\Catalog\Model\Product as VendorProduct;

class UnListProduct extends \Magedelight\Catalog\Controller\Adminhtml\Product
{

    /**
     * List action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
             $this->vendorProduct->load($id);
             $productWebModel = $this->productWebsiteRepository->getProductWebsiteData($id);
                
            if (!$this->vendorProduct->getId()) {
                $this->messageManager->addError('Vendor Product does not exist');
                return $resultRedirect->setPath('*/*/');
            }
           
            try {
                if ($productWebModel) {
                    $productWebModel->setData('status', 0);
                }
                $oldQty = (int) $this->vendorProduct->getQty();
                $this->productWebsiteRepository->save($productWebModel);
                $vendorProduct = $this->vendorProduct->save();
                $eventParams = [
                    'marketplace_product_id' => $this->vendorProduct->getMarketplaceProductId(),
                    'old_qty'   => $oldQty,
                    'vendor_product' => $vendorProduct
                    ];
                $this->_eventManager->dispatch('vendor_product_unlist_after', $eventParams);

                $eventParams = ['status' => 'Unlist','id' => $id];
               
                $this->_eventManager->dispatch('vendor_product_admin_unlist', $eventParams);

                $this->messageManager->addSuccess(__('Vendor product unlisted sucessfully'));
                $this->_cacheManager->clean('catalog_product_' . $vendorProduct->getMarketplaceProductId());
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving record.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
