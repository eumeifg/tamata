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
namespace Magedelight\Catalog\Block\Sellerhtml\Sellexisting;

use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

class Result extends \Magedelight\Backend\Block\Template
{
    const PAGING_LIMIT = 20;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequest
     */
    protected $_productRequest;

    /**
     * @var \Magento\Catalog\Model\CategoryRepositoryFactory
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        FlatState $flatState,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->authSession = $authSession;
        $this->_categoryRepository = $categoryRepository;
        $this->_categoryFactory = $categoryFactory;
        $this->_productRequest = $productRequestFactory->create();
        $this->_productRepository = $productRepositoryInterface;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->flatState = $flatState;
        parent::__construct($context, $data);
    }

    /**
     * category Attribute Set Id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryAttributeSetId()
    {
        return $this->getLoadedCategory()->getCategoryAttributeSetId();
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoadedCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        return $this->_categoryRepository->get($categoryId);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param type $pid
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function categoryPath($pid)
    {
        $product = $this->_productRepository->getById($pid);
        $cats = $product->getCategoryIds();
        if (empty($cats)) {
            return '';
        }

        $category = $this->_categoryFactory->create();
        $collection = $category->getResourceCollection();
        $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', $cats)
            ->addIsActiveFilter()
            ->load();

        $result = '';

        foreach ($collection as $cat) {
            $result .= '<ul class="items">';
            $names = [];

            foreach ($this->_categoryRepository->get($cat->getId())->getParentCategories() as $parent) {
                $names[] = $parent->getName();
            }

            foreach ($names as $key => $label) {
                $result .= '<li class="item">' . $label . '</li>';
            }
            $result .= '</ul>';
        }

        return $result;
    }

    /**
     * @param $pid
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSelectUrl($pid)
    {
        $product = $this->_productRepository->getById($pid);
        $cats = $product->getCategoryIds();
        /* Get authorized categories. */
        $cats = array_values(array_intersect($cats, $this->getCategoryInVendor()));
        /* Get authorized categories. */
        $categoryId = (isset($cats[0]) ? $cats[0] : null);
        return $this->getUrl('rbcatalog/product/offer', ['cid' => $categoryId, 'pid' => $pid, 'tab' => '1,2']);
    }

    /**
     * @return array
     */
    public function getCategoryInVendor()
    {
        if ($this->authSession->isLoggedIn()) {
            return $this->authSession->getUser()->getCategoryIds();
        }
        return [];
    }

    /**
     * @param $pid
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryId($pid)
    {
        $product = $this->_productRepository->getById($pid);
        $cats = $product->getCategoryIds();
        if (empty($cats)) {
            return '';
        } else {
            return $cats;
        }
    }

    /**
     * load if product request already exist for product for vendor but not approved.
     * @param $productId
     * @return boolean
     */
    public function loadByProductVendorId($productId)
    {
        return $this->_productRequest->loadByProductVendorId($productId, $this->getVendor()->getId());
    }

    /**
     * @return \Magento\User\Model\User|\RB\Vender\Api\Data\VendorInterface
     */
    public function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     * check if product request already exist for product for vendor but not approved.
     * @param type $productId
     * @return boolean
     */
    public function existButNotApproved($productId)
    {
        return $this->_productRequest->existButNotApproved($productId, $this->getVendor()->getId());
    }

    protected function _construct()
    {
        parent::_construct();
        $stockFlag = 'has_stock_status_filter';
        $search = $this->getRequest()->getParam('search');
        $categoryId = $this->getRequest()->getParam('category');
        $vendorId = ($this->getVendor()) ? $this->getVendor()->getVendorId() : '';
        $vendorCollection = $this->_vendorProductFactory->create()->getCollection()
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId]);
        $excludeIds[] = $vendorCollection->getColumnValues('marketplace_product_id');

        $collection = $this->_productCollectionFactory->create();
        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter('type_id', ['neq' => 'configurable']);
            $collection->addFieldToSelect('name');
            $collection->addFieldToSelect('model_number');
            $collection->addFieldToSelect('small_image');
        } else {
            $collection->addAttributeToFilter('type_id', ['neq' => 'configurable']);
            $collection->addAttributeToSelect('name');
            $collection->addAttributeToSelect('model_number');
            $collection->addAttributeToSelect('small_image');
        }

        if ($categoryId) {
            $collection->addCategoriesFilter(['eq'=>$categoryId]);
        }

        $requestedProducts = $this->getRequestedProducts($vendorId);

        if ($excludeIds[0] != null) {
            $excludeIds = array_merge($excludeIds[0], $requestedProducts);
            $collection->addIdFilter($excludeIds, true);
        }

        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter([['attribute' => 'name', 'like' => '%' . $search . '%'],
                                                    ['attribute' => 'sku', 'like' => trim($search)]]);
        } else {
            $collection->addAttributeToFilter([['attribute' => 'name', 'like' => '%' . $search . '%'],
                                                    ['attribute' => 'sku', 'like' => trim($search)]]);
        }
        $collection->setFlag($stockFlag, false);
        $this->setCollection($collection);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sellexisting.result.list.pager'
            );

            $catalog_ids = $this->getCategoryInVendor();
            $collection = $this->getCollection();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $catalog_ids]);
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    public function getRequestedProducts($vendorId)
    {
        $excludeRequested = [];
        /* Exclude Already Requested products */
        $excludeRequestedProductsCollection = $this->_productRequest->getCollection()
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId]);
        $excludeRequested = $excludeRequestedProductsCollection->getColumnValues('marketplace_product_id');
        return $excludeRequested;
    }
}
