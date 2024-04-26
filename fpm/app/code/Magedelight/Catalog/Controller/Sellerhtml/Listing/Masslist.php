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
namespace Magedelight\Catalog\Controller\Sellerhtml\Listing;

class Masslist extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $vendorProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory
     */
    protected $productWebsiteFactory;

    /**
     * @var \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface
     */
    protected $productWebsiteRepository;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $productWebsiteFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magedelight\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $productWebsiteFactory
    ) {
        $this->vendorProductRequest = $productRequestFactory;
        $this->vendorProduct = $productFactory->create();
        $this->productWebsiteFactory = $productWebsiteFactory;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->_cacheManager = $cacheManager;
        $this->_session = $context->getSession();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $dataStatus = $this->getRequest()->getParam('masslive_list_id');
        if ($dataStatus == 'mass_action_delete') {
            $ids = $this->getRequest()->getParam('product_list');
            if (!is_array($ids) || empty($ids)) {
                $this->messageManager->addError(__('Please select product(s).'));
            } else {
                try {
                    foreach ($ids as $id) {
                        /* Delete child products if exists. */
                        $collection = $this->vendorProductRequest->create()->getCollection();
                        $collection->getSelect()->join(
                            ['mvprsl' => 'md_vendor_product_request_super_link'],
                            'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = ' . $id,
                            ['mvprsl.product_request_id']
                        );
                        if ($collection && $collection->getSize() > 0) {
                            foreach ($collection as $request) {
                                $request->delete();
                            }
                        }
                        /* Delete child products if exists. */
                        $model = $this->vendorProductRequest->create()->load($id);
                        $model->delete();
                    }
                    $this->messageManager->addSuccess(
                        __('A total of %1 record(s) have been deleted.', count($ids))
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
        if ($dataStatus == 'mass_action_list') {
            $ids = $this->getRequest()->getParam('product_list');
            if (!is_array($ids) || empty($ids)) {
                $this->messageManager->addError(__('Please select product(s).'));
            } else {
                $vendorId = $this->_auth->getUser()->getVendorId();
                $resultRedirect = $this->resultRedirectFactory->create();
                try {
                    $productIds = [];
                    $products = [];

                    foreach ($ids as $id) {
                        $product = $this->vendorProduct->load($id);
                        $productWebModel = $this->productWebsiteRepository->getProductWebsiteData($id);
                        if ($productWebModel) {
                            $productWebModel->setData('status', 1);
                        }

                        $this->productWebsiteRepository->save($productWebModel);
                        $productIds[] = $product->getMarketplaceProductId();
                        $products[$id]['qty'] = $product->getQty();
                        $products[$id]['marketplace_product_id'] = $product->getMarketplaceProductId();

                        $this->_cacheManager->clean('catalog_product_' . $product->getMarketplaceProductId());
                    }
                    $eventParams = [
                        'marketplace_product_ids' => $productIds,
                        'vendor_products' => $products
                    ];

                    $this->messageManager->addSuccess(
                        __('Product list successfully !')
                    );
                    $this->_eventManager->dispatch('frontend_vendor_product_mass_list_after', $eventParams);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
