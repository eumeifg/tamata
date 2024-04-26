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

class MassUnList extends \Magedelight\Catalog\Controller\Adminhtml\Product
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('vendor_product');

        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                $products = $productIds = $parentIds = [];
                $this->vendorProductResource->updateStatusForProducts($ids, VendorProduct::STATUS_UNLISTED);
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('vendor_product_id', ['in' => $ids])
                    ->addFieldToSelect(['marketplace_product_id','qty','parent_id','vendor_sku']);
                foreach ($collection as $vendorProduct) {
                    $products[$vendorProduct->getVendorProductId()]['qty'] = $vendorProduct->getQty();
                    $products[$vendorProduct->getVendorProductId()]['marketplace_product_id'] =
                        $vendorProduct->getMarketplaceProductId();
                    $productIds[] = $vendorProduct->getMarketplaceProductId();
                    if ($vendorProduct->getParentId()) {
                        /* Empty Check is required. Throws validation issue in indexing. */
                        $parentIds[] = $vendorProduct->getParentId();
                    }
                    $this->_cacheManager->clean('catalog_product_' . $vendorProduct->getMarketplaceProductId());
                    /* Event for single store save. */
                    $this->_eventManager->dispatch(
                        'vendor_product_store_save_after',
                        [
                            'vendor_product_store_data' => [
                                'vendor_product_id' => $vendorProduct->getVendorProductId(),
                                'store_id' => $vendorProduct->getStoreId()
                            ]
                        ]
                    );
                    /* Event for single store save. */

                    $eventParams = [
                        'marketplace_product_id' => $vendorProduct->getMarketplaceProductId(),
                        'old_qty' => $vendorProduct->getQty(),
                        'vendor_product' => $vendorProduct
                    ];
                    $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);
                    $this->_eventManager->dispatch('vendor_product_unlist_after', $eventParams);
                }
                $parentIds = array_values($parentIds);
                // array_unique will remove duplicate marketplace product id.
                $eventParams = [
                    'marketplace_product_ids' => array_unique($productIds),
                    'vendor_products' => $products,
                    'parent_ids' => array_unique($parentIds)
                ];
                $this->_eventManager->dispatch('vendor_product_mass_unlist_after', $eventParams);
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been updated.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/', ['status'=>  VendorProduct::STATUS_LISTED]);
    }
}
