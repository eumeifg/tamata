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

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface
     */
    protected $productWebsiteRepository;

    /**
     * @var \Magedelight\Catalog\Api\ProductStoreRepositoryInterface
     */
    protected $productStoreRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProduct;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magedelight\Catalog\Api\ProductStoreRepositoryInterface $productStoreRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magedelight\Catalog\Api\ProductStoreRepositoryInterface $productStoreRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
    ) {
        $this->productStoreRepository = $productStoreRepository;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->storeManager = $storeManager;
        $this->vendorProduct = $vendorProductFactory->create();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $productWebsite = null;
        $productWebsiteData = null;
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->vendorProduct->load($id);
            $data = $this->getRequest()->getPost();

            if (!$this->vendorProduct->getId()) {
                $this->messageManager->addError('Vendor Product does not exist');
                return $resultRedirect->setPath('*/*/', ['status' => $data['status']]);
            }
            foreach ($data as $key => $value) {
                $this->vendorProduct->setData($key, $value);
            }
            $productId = $this->vendorProduct->getMarketplaceProductId();
            $oldQty = (int) $this->vendorProduct->getQty();
            try {
                if (isset($this->vendorProduct['form_key']) && $this->vendorProduct['form_key'] != '') {
                    $store = $this->storeManager->getStore($data['store_id']);
                    $websiteId = $store->getWebsiteId();
                    $webProductData = [
                        'vendor_product_id' => $data['id'],
                        'price' => $data['price'],
                        'special_price' => $data['special_price'],
                        'warranty_type' => $data['warranty_type'],
                        'reorder_level' => $data['reorder_level'],
                        'status' => $data['status']
                    ];

                    if (!empty($data['special_price']) || trim($data['special_price']) == '0') {
                        $webProductData['special_price'] = $data['special_price'];
                        $webProductData['special_from_date'] = $data['special_from_date'];
                        $webProductData['special_to_date'] = $data['special_to_date'];
                    } else {
                        $webProductData['special_price'] = null;
                    }

                    $storeProductData = [
                        'condition_note' => $data['condition_note'],
                        'warranty_description' => $data['warranty_description'],
                        'vendor_product_id' => $data['id'],
                        'store_id' => $data['store_id']
                    ];

                    $productWebModel = $this->productWebsiteRepository->getProductWebsiteData($data['id']);
                    if ($productWebModel) {
                        foreach ($webProductData as $key => $value) {
                            $productWebModel->setData($key, $value);
                        }
                        $this->productWebsiteRepository->save($productWebModel);
                    }

                    $productStoreModel = $this->productStoreRepository->getProductStoreData(
                        $data['id'],
                        $data['store_id']
                    );

                    if ($productStoreModel) {
                        foreach ($storeProductData as $key => $value) {
                            $productStoreModel->setData($key, $value);
                        }
                        $this->productStoreRepository->save($productStoreModel);
                    }

                    /* Event for single store save. */
                    $this->_eventManager->dispatch(
                        'vendor_product_store_save_after',
                        [
                            'vendor_product_store_data' => [
                                'vendor_product_id' => $id,
                                'store_id' => $productStoreModel ? $productStoreModel->getStoreId() : 1
                            ]
                        ]
                    );
                    /* Event for single store save. */

                    /* Event for all stores save. */
                    $eventParams = [
                        'post_data' => $this->getRequest()->getPostValue(),
                        'vendor_product_ids_with_stores' => [
                            0 => [
                                'vendor_product_id' => $id,
                                'store_id' => $productStoreModel ? $productStoreModel->getStoreId() : 1
                            ]
                        ]
                    ];
                    $this->_eventManager->dispatch('vendor_product_stores_save_after', $eventParams);
                    /* Event for all stores save. */
                }

                $vendorProduct = $this->vendorProduct->save();
                $eventParams = [
                    'marketplace_product_id' => $vendorProduct->getMarketplaceProductId(),
                    'old_qty' => $oldQty,
                    'vendor_product' => $vendorProduct
                ];
                $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);
                $this->messageManager->addSuccess(__('products has been updated successfully.'));
                return $resultRedirect->setPath('*/*/', ['status' => $this->vendorProduct->getStatus()]);
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->messageManager->addError('Product already exists with same vendor SKU. Please try another SKU.');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving record.'));
            }
        }
        return $resultRedirect->setPath('*/*/', ['status' => $this->vendorProduct->getStatus()]);
    }
}
