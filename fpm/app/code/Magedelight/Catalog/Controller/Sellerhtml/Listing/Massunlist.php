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

class Massunlist extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;
    
    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $productWebsiteFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $productWebsiteFactory
    ) {
        $this->vendorProduct = $productFactory->create();
        $this->_cacheManager = $cacheManager;
        $this->productWebsiteFactory = $productWebsiteFactory;
        $this->productWebsiteRepository = $productWebsiteRepository;
        parent::__construct($context);
    }
    
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $unlist = $this->getRequest()->getParam('product_unlist');
        if (!is_array($unlist) || empty($unlist)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                $productIds = [];
                $products   = [];
                
                foreach ($unlist as $id) {
                    $product = $this->vendorProduct->load($id);
                    $productWebModel = $this->productWebsiteRepository->getProductWebsiteData($id);
                    if ($productWebModel) {
                        $productWebModel->setData('status', 0);
                    }

                    $this->productWebsiteRepository->save($productWebModel);
                    
                    $productIds[] = $product->getMarketplaceProductId();
                    $products[$id]['qty'] = $product->getQty();
                    $products[$id]['marketplace_product_id'] = $product->getMarketplaceProductId();
                        
                    $this->_cacheManager->clean('catalog_product_' . $product->getMarketplaceProductId());
                }
                
                $eventParams = [
                        'marketplace_product_ids' => $productIds ,
                        'vendor_products' => $products
                ];
                $this->_eventManager->dispatch('frontend_vendor_product_mass_unlist_after', $eventParams);
                $this->messageManager->addSuccess(
                    __('Product Unlist successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
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
