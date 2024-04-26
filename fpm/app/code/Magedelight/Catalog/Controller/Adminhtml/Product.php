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
namespace Magedelight\Catalog\Controller\Adminhtml;

use Magedelight\Catalog\Model\Product as VendorProduct;

abstract class Product extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var VendorProduct
     */
    protected $vendorProduct;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface
     */
    protected $productWebsiteRepository;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Product constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->vendorProduct = $vendorProductFactory->create();
        $this->_cacheManager = $cacheManager;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->vendorProductResource = $vendorProductResource;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products_listed');
    }
}
