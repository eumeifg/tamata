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

class ListProduct extends \Magedelight\Catalog\Controller\Adminhtml\Product
{
    
    /**
     * List action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productWebsite = null;
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        
        if ($id) {
            $this->vendorProduct->load($id);
            if (!$this->vendorProduct->getId()) {
                $this->messageManager->addErrorMessage('Vendor Product does not exist');
                return $resultRedirect->setPath('*/*/');
            }
            try {
                // $this->vendorProduct->setData(VendorProduct::STATUS_PARAM_NAME,  VendorProduct::STATUS_LISTED);

                 $productWebModel = $this->productWebsiteRepository->getProductWebsiteData($id);
                if ($productWebModel) {
                    $productWebModel->setData('status', VendorProduct::STATUS_LISTED);
                }
                        
                $this->productWebsiteRepository->save($productWebModel);
                
                $oldQty = (int) $this->vendorProduct->getQty();
                
                $vendorProduct = $this->vendorProduct->save();

                $eventParams = [
                    'marketplace_product_id' => $vendorProduct->getMarketplaceProductId(),
                    'old_qty'   => $oldQty,
                    'vendor_product' => $vendorProduct
                ];
                $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);

                $this->messageManager->addSuccessMessage(__('Vendor product Listed sucessfully.'));
           
                $eventParams = ['status' => 'List','id' => $id];
                $this->_eventManager->dispatch('vendor_product_admin_list', $eventParams);
                $this->_cacheManager->clean('catalog_product_' . $vendorProduct->getMarketplaceProductId());
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving record.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
