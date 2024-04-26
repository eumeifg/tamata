<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_TargetRule
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\TargetRule\Model\Product;

use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ktpl\ProductLabel\Helper\Data as DataHelper;

/**
 * TargetRule abstract Products Model
 *
 */
abstract class AbstractProduct
{
    /**
     * Link collection
     *
     * @var null|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_linkCollection = null;

    /**
     * Catalog Product List Item Collection array
     *
     * @var null|array
     */
    protected $_items = null;

    /**
     * Get link collection for specific target
     *
     * @abstract
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    abstract protected function _getTargetLinkCollection();

    /**
     * Get target rule products
     *
     * @abstract
     * @return array
     */
    abstract protected function _getTargetRuleProducts();

    /**
     * Retrieve Catalog Product List Type identifier
     *
     * @return int
     */
    abstract public function getProductListType();

    /**
     * Retrieve Maximum Number Of Product
     *
     * @return int
     */
    abstract public function getPositionLimit();

    /**
     * Retrieve Position Behavior
     *
     * @return int
     */
    abstract public function getPositionBehavior();

    /**
     * Target rule data
     *
     * @var \Magento\TargetRule\Helper\Data
     */
    protected $_targetRuleData = null;

    /**
     * @var \Magento\TargetRule\Model\ResourceModel\Index
     */
    protected $_resourceIndex;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @deprecated 103.0.2
     *
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var MediaGalleryProcessor
     */
    protected $mediaProcessor;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var FinalPriceResolver
     */
    protected $priceResolver;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory
     */
    protected $wishlistCollectionFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\TargetRule\Model\IndexFactory
     */
    protected $_indexFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * AbstractProduct constructor.
     * @param \Magento\TargetRule\Model\ResourceModel\Index $index
     * @param \Magento\TargetRule\Helper\Data $targetRuleData
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryProcessor
     * @param DataHelper $dataHelper
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param FinalPriceResolver $priceResolver
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\TargetRule\Model\IndexFactory $indexFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\TargetRule\Model\ResourceModel\Index $index,
        \Magento\TargetRule\Helper\Data $targetRuleData,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryProcessor,
        DataHelper $dataHelper,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        FinalPriceResolver $priceResolver,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\TargetRule\Model\IndexFactory $indexFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MDC\Catalog\Helper\OnlyXLeft $mdcHelper
    ) {
        $this->_resourceIndex = $index;
        $this->_targetRuleData = $targetRuleData;
        $this->collectionFactory = $collectionFactory;
        $this->mediaGalleryProcessor = $mediaGalleryProcessor;
        $this->dataHelper = $dataHelper;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->priceResolver = $priceResolver;
        $this->vendorProduct = $vendorProduct;
        $this->pricingHelper = $pricingHelper;
        $this->catalogHelper = $catalogHelper;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->userContext = $userContext;
        $this->_visibility = $visibility;
        $this->_indexFactory = $indexFactory;
        $this->_cart = $cart;
        $this->_eventManager = $eventManager;
        $this->mdcHelper = $mdcHelper;
    }

    /**
     * Return the behavior positions applicable to products based on the rule(s)
     *
     * @return int[]
     */
    public function getRuleBasedBehaviorPositions()
    {
        return [
            \Magento\TargetRule\Model\Rule::BOTH_SELECTED_AND_RULE_BASED,
            \Magento\TargetRule\Model\Rule::RULE_BASED_ONLY
        ];
    }

    /**
     * Retrieve the behavior positions applicable to selected products
     *
     * @return int[]
     */
    public function getSelectedBehaviorPositions()
    {
        return [
            \Magento\TargetRule\Model\Rule::BOTH_SELECTED_AND_RULE_BASED,
            \Magento\TargetRule\Model\Rule::SELECTED_ONLY
        ];
    }

    /**
     * Get link collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    public function getLinkCollection()
    {
        if ($this->_linkCollection === null) {
            $this->_linkCollection = $this->_getTargetLinkCollection();

            if ($this->_linkCollection) {
                // Perform rotation mode
                $select = $this->_linkCollection->getSelect();
                $rotationMode = $this->_targetRuleData->getRotationMode($this->getProductListType());
                if ($rotationMode == \Magento\TargetRule\Model\Rule::ROTATION_SHUFFLE) {
                    $this->_resourceIndex->orderRand($select);
                } else {
                    $select->order('link_attribute_position_int.value ASC');
                }
            }
        }

        return $this->_linkCollection;
    }

    /**
     * Get linked products
     *
     * @return array
     */
    protected function _getLinkProducts()
    {
        $items = [];
        $linkCollection = $this->getLinkCollection();
        if ($linkCollection) {
            foreach ($linkCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
        }
        return $items;
    }

    /**
     * Whether rotation mode is set to "shuffle"
     *
     * @return bool
     */
    public function isShuffled()
    {
        $rotationMode = $this->_targetRuleData->getRotationMode($this->getProductListType());
        return $rotationMode == \Magento\TargetRule\Model\Rule::ROTATION_SHUFFLE;
    }

    /**
     * Order product items
     *
     * @param array $items
     * @return array
     */
    protected function _orderProductItems(array $items)
    {
        if ($this->isShuffled()) {
            // shuffling assoc
            $ids = array_keys($items);
            shuffle($ids);
            $result = [];
            foreach ($ids as $id) {
                $result[$id] = $items[$id];
            }
            return $result;
        } else {
            uasort($items, [$this, 'compareItems']);
            return $items;
        }
    }

    /**
     * Compare two items for ordered list
     *
     * @param \Magento\Framework\DataObject $item1
     * @param \Magento\Framework\DataObject $item2
     * @return int
     */
    public function compareItems($item1, $item2)
    {
        // Prevent rule-based items to have any position
        if ($item2->getPosition() === null && $item1->getPosition() !== null) {
            return -1;
        } elseif ($item1->getPosition() === null && $item2->getPosition() !== null) {
            return 1;
        }
        $positionDiff = (int)$item1->getPosition() - (int)$item2->getPosition();
        if ($positionDiff != 0) {
            return $positionDiff;
        }
        return (int)$item1->getEntityId() - (int)$item2->getEntityId();
    }

    /**
     * Slice items to limit
     *
     * @return $this
     */
    protected function _sliceItems()
    {
        if ($this->_items !== null) {
            if ($this->isShuffled()) {
                $this->_items = array_slice($this->_items, 0, $this->_targetRuleData->getMaxProductsListResult(), true);
            } else {
                $this->_items = array_slice($this->_items, 0, $this->getPositionLimit(), true);
            }
        }
        return $this;
    }

    /**
     * Retrieve Catalog Product List Items
     *
     * @return ProductInterface[]
     */
    public function getItemCollection()
    {
        if ($this->_items === null) {
            $behavior = $this->getPositionBehavior();

            $this->_items = [];

            if (in_array($behavior, $this->getSelectedBehaviorPositions())) {
                $this->_items = $this->_orderProductItems($this->_getLinkProducts());
            }


            if (in_array($behavior, $this->getRuleBasedBehaviorPositions())) {
                foreach ($this->_orderProductItems($this->_getTargetRuleProducts()) as $id => $item) {
                    $this->_items[$id] = $item;
                }
            }
            $this->_sliceItems();
        }
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name','price']);
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $collection->addIdFilter(array_keys($this->_items));
        $collection->load();

        foreach ($collection->getItems() as $entity){

            if($entity->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                $price = $this->getPriceForConfigurable($entity);
                $entity->setVendorPrice($price);
                $entity->setVendorSpecialPrice($price);
            }else{
                $defaultVendorProduct = $this->catalogHelper->getProductDefaultVendor($entity->getId());
                if($defaultVendorProduct && $defaultVendorProduct->getVendorProductId()){
                    $entity->setVendorId($defaultVendorProduct->getVendorId());
                    $entity->setVendorPrice($defaultVendorProduct->getPrice());
                    $entity->setVendorSpecialPrice($defaultVendorProduct->getSpecialPrice());
                }
            }
            $this->mediaGalleryProcessor->execute($entity);
            $imageUrl = null;
            foreach ($entity->getMediaGalleryEntries() as $image)
            {
                $imageUrl = $this->storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ).'catalog/product'. $image->getFile();
                break;
            }
            $entity->setImageUrl($imageUrl);
            $entity->setProductLabels($this->dataHelper->getProductPLabels($entity));
            

            if($entity->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                $salableQty = "";
            }else{
                $thresoldStatus = $this->mdcHelper->getProductXleftById($entity->getId());

                if($thresoldStatus["status"]){
                    
                        $salableQty = $thresoldStatus['qty'];
                    }else{
                        $salableQty = "";
                    }
            }
        
            $entity->setOnlyXLeft($salableQty);

            $this->processReviews($entity);
            $this->processWishlistFlag($entity);
        }
        return $collection->getItems();
    }

    /**
     * @param $entity
     * @return float|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPriceForConfigurable($entity){
        $productPrice = null;
        foreach ($entity->getTypeInstance()->getUsedProducts($entity) as $subProduct) {
            $productPrice = $this->pricingHelper->currency(
                $this->vendorProduct->getMinimumPriceForProduct($subProduct),
                false,
                false,
                true
            );
            if (!$productPrice) {
                $productPrice = $this->priceResolver->resolvePrice($subProduct);
            }
        }
        return $productPrice;
    }

    /**
     * @param $entity
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processReviews($entity)
    {
        $this->reviewFactory->create()->getEntitySummary(
            $entity,
            $this->storeManager->getStore()->getId()
        );
        $reviewCount = $entity->getRatingSummary()->getReviewsCount();
        $ratingSummary = $entity->getRatingSummary()->getRatingSummary();
        if ($reviewCount == null) {
            $reviewCount = 0;
        }
        if ($ratingSummary == null) {
            $ratingSummary = 0;
        }
        $entity->setRatingSummary($ratingSummary);
        $entity->setReviewCount($reviewCount);
        return $this;
    }

    /**
     * @param $entity
     */
    protected function processWishlistFlag($entity)
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $collection = $this->wishlistCollectionFactory->create()
                ->addCustomerIdFilter($customerId)
                ->addFieldToFilter('product_id', $entity->getId())->getFirstItem();

            if ($collection && $collection->getId()) {
                $entity->setWishlistFlag(true);
            } else {
                $entity->setWishlistFlag(false);
            }
        } else {
            $entity->setWishlistFlag(false);
        }
    }
}
