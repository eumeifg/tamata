<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Microsite;

use Magedelight\Vendor\Model\Microsite\VendorProducts;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

/**
 * Description of ListProduct
 *
 * @author Rocket Bazaar Core Team
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $_reviewFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    protected $_attributeRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var VendorProducts
     */
    protected $vendorProducts;

    /**
     * ListProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param VendorProducts $vendorProducts
     * @param FlatState $flatState
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        VendorProducts $vendorProducts,
        FlatState $flatState,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        $this->_reviewFactory = $reviewFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->request = $request;
        $this->flatState = $flatState;
        $this->vendorProducts = $vendorProducts;
    }

    /**
     * Retrieve loaded product collection
     *
     * The goal of this method is to choose whether the existing collection should be returned
     * or a new one should be initialized.
     *
     * It is not just a caching logic, but also is a real logical check
     * because there are two ways how collection may be stored inside the block:
     *   - Product collection may be passed externally by 'setCollection' method
     *   - Product collection may be requested internally from the current Catalog Layer.
     *
     * And this method will return collection anyway,
     * even when it did not pass externally and therefore isn't cached yet
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            $collection = $layer->getProductCollection();
            $productIds = array_merge(
                $this->getProductCollectionForSimple()->getAllIds(),
                $this->getProductCollectionForConfig()->getAllIds()
            );

            /* Flat table Compatibility Changes */
            if ($this->flatState->isAvailable()) {
                $collection->addFieldToFilter('entity_id', ['in' => array_unique($productIds)]);
            } else {
                $collection->addAttributeToFilter('entity_id', ['in' => array_unique($productIds)]);
            }
            $collection->getSelect()->group('e.entity_id');
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return parent::getProductPrice($product);
    }

    /**
     * @param $product
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRatingSummary($product)
    {
        $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();

        if ($ratingSummary == '' || $ratingSummary == 0) {
            $ratingSummary = 0;
            return $ratingSummary;
        } else {
            return $ratingSummary;
        }
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionForSimple()
    {
        return $this->vendorProducts->getProductCollectionForSimple($this->request->getParam('vid'));
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionForConfig()
    {
        return $this->vendorProducts->getProductCollectionForConfig($this->request->getParam('vid'));
    }
}
