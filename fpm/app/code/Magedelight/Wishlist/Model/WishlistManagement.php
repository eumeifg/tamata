<?php

namespace Magedelight\Wishlist\Model;

use Magedelight\Wishlist\Api\WishlistManagementInterface;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Helper\ImageFactory as ProductImageHelper;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Defines the implementation class of the WishlistManagementInterface
 */
class WishlistManagement implements WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;

    /**
     * @var Item
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $productImageHelper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * WishlistManagement constructor.
     * @param CollectionFactory $wishlistCollectionFactory
     * @param WishlistFactory $wishlistFactory
     * @param AppEmulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductImageHelper $productImageHelper
     * @param WishlistFactory $wishlistRepository
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository
     * @param \Magento\Quote\Model\Quote\ItemFactory $cartItem
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Json|null $serializer
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        AppEmulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductImageHelper $productImageHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository,
        \Magento\Quote\Model\Quote\ItemFactory $cartItem,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Json $serializer = null
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_wishlistRepository = $wishlistRepository;
        $this->productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->wishlist = $wishlist;
        $this->storeManager = $storeManager;
        $this->_itemFactory = $itemFactory;
        $this->appEmulation = $appEmulation;
        $this->productImageHelper = $productImageHelper;
        $this->checkoutSession = $checkoutSession;
        $this->cartManagement = $cartManagement;
        $this->cartItemFactory = $cartItemFactory;
        $this->cartItemRepository = $cartItemRepository;
        $this->cartItem = $cartItem;
        $this->_helper = $helper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Get wishlist collection
     * @deprecated
     * @param $customerId
     * @return WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {
        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new InputException(__('Id required'));
        }
        $baseurl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';

        $wishlistCollection = $this->_wishlistCollectionFactory->create()
                                ->addCustomerIdFilter($customerId);
        $wishlistData = [];
        foreach ($wishlistCollection as $item) {
            $productInfo = $item->getProduct();
            $vendorInfo = $item->getOptionByCode('additional_options');
            $vendorInformation = [];
            if (!empty($vendorInfo)) {
                $vendorValue = $vendorInfo->getValue();
                if (!empty($vendorValue)) {
                    $vendorInformation  = $this->serializer->unserialize($vendorValue);
                }
            }
            $smallImage = $productInfo->getSmallImage();
            if ($productInfo->getSmallImage() == 'no_selection' || empty($smallImage)) {
                $currentproduct = $this->productRepository->getById($productInfo->getEntityId());
                $imageURL = $this->getImageUrl($currentproduct, 'product_base_image');
                $productInfo->setSmallImage($imageURL);
                $productInfo->setThumbnail($imageURL);
            } else {
                $imageURL = $baseurl.$productInfo->getSmallImage();
                $productInfo->setSmallImage($imageURL);
                $productInfo->setThumbnail($imageURL);
            }
            $productInfo = $productInfo->toArray();
            $data = [
                "wishlist_item_id" => $item->getWishlistItemId(),
                "wishlist_id"      => $item->getWishlistId(),
                "product_id"       => $item->getProductId(),
                "store_id"         => $item->getStoreId(),
                "added_at"         => $item->getAddedAt(),
                "description"      => $item->getDescription(),
                "qty"              => round($item->getQty()),
                "vendor"           => $vendorInformation,
                "product"          => $productInfo
            ];
            $wishlistData[] = $data;
        }
        return $wishlistData;
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productId
     * @param int|null $vendorId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($customerId, $productId, $vendorId = null)
    {
        if ($productId == null) {
            throw new LocalizedException(__('Invalid product, Please select a valid product'));
        }
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId(
                $customerId,
                true
            );
            if (!$vendorId) {
                if(!empty($product->getExtensionAttributes()->getAppendedData()->getDefaultVendorData()[0]))
                {
                    $vendorId = $product->getExtensionAttributes()->getAppendedData()->getDefaultVendorData()[0]['vendor_id'];
                }
            }
            if($vendorId){
                $this->checkoutSession->setVendorId($vendorId);
                $item = $wishlist->addNewItem($product);
                $wishlist->save();
            }else{
                return false;
            }
        } catch (NoSuchEntityException $e) {

        }
        return $item->getWishlistItemId();
    }

    /**
     * @inheritDoc
     */
    public function removeWishlistItem($customerId, $itemId)
    {
        if ($itemId == null) {
            throw new LocalizedException(__('Please select a item'));
        }
        $item = $this->_itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List Item doesn\'t exist.')
            );
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->_wishlistFactory->create();

        if ($wishlistId) {
            $wishlist->load($wishlistId);
        } elseif ($customerId) {
            $wishlist->loadByCustomerId($customerId, true);
        }
        if (!$wishlist) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
        if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Something went wrong, please try again later.')
            );
        }

        return $this->getWishlistForCustomer($customerId);
    }


    /**
     * Remove wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeWishlistItemProductWise($customerId, $productId)
    {
        if ($productId == null) {
            throw new LocalizedException(__('Invalid product, Please select a valid product'));
        }
        $wishlist = $this->wishlist->loadByCustomerId($customerId);
        $items = $wishlist->getItemCollection();
        try {
            /** @var \Magento\Wishlist\Model\Item $item */
            foreach ($items as $item) {
                if ($item->getProductId() == $productId) {
                    $item->delete();
                    $wishlist->save();
                }
            }
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
    
    /**
     * Remove wishlist item for the customer
     * @param int $customerId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeAllWishlistForCustomer($customerId)
    {
        $wish = $this->wishlist->loadByCustomerId($customerId);
        $items = $wish->getItemCollection();
        try {
           /** @var \Magento\Wishlist\Model\Item $item */
            foreach ($items as $item) {
                $item->delete();
                $wish->save();
            }
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param $product
     * @param string $imageType
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($product, string $imageType = "")
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $imageUrl = $this->productImageHelper->create()->init($product, $imageType)->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();
        return $imageUrl;
    }


    /**
     * @inheritDoc
     */
    public function moveToCart($customerId, $itemId, $qty)
    {
        $wishlist = $this->_wishlistFactory->create()->loadByCustomerId($customerId, true);
        $item = $wishlist->getItem($itemId);
        $isMoveSuccess = false;
        if ($item) {
            $this->checkoutSession->setVendorId($item->getVendorId());
            $this->checkoutSession->getProductVendorId($item->getVendorId());
            $product = $this->productRepository->getById($item->getProductId());
            $quoteId = $this->cartManagement->createEmptyCartForCustomer($customerId);
            $cartItemObject = $this->cartItemFactory->create();
            $cartItemObject->setQty($qty);
            $cartItemObject->setSku($product->getSku());
            $cartItemObject->setQuoteId($quoteId);
            try {
                $addedItem = $this->cartItemRepository->save($cartItemObject);
                $addedItem->setVendorId($item->getVendorId());
                $this->updateCartItemPrice($addedItem);
                $addedItem->setVendorId($item->getVendorId());
            } catch (\Magento\Framework\Exception\Exception $e) {
                throw new \Magento\Framework\Exception\Exception(
                    __('Unable to add this item in cart. ')
                );
            }
            $item->delete();
            $isMoveSuccess = true;
        } else {
            throw new NoSuchEntityException(__('No such item in wishlist'));
        }
        if ($isMoveSuccess) {
            return true;
        } else {
            throw new LocalizedException(__('Can\'t move this product into cart'));
        }
    }

    /* Update Item Price */
    protected function updateCartItemPrice($item)
    {
        $optionId = $this->productRepository->get($item->getSku())->getId();
        $vendorId = $item->getVendorId();
        $productId = $item->getProduct()->getId();

        if ($optionId) {
            $price = $this->_helper->getVendorFinalPrice($vendorId, $optionId);
        } else {
            $price = $this->_helper->getVendorFinalPrice($vendorId, $productId);
        }
        $item = ($item->getParentProductId()) ? $item->getParentItem() : $item;
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        $item->save();
        $quote = $this->quoteRepository->getActive($item->getQuoteId());
        $this->quoteRepository->save($quote->collectTotals());
    }

    /**
     * @inheritDoc
     */
    public function moveToWishlist($customerId, $itemId)
    {
        $isMoveSuccess = false;
        try {
            $wishlist = $this->_wishlistFactory->create()->loadByCustomerId($customerId, true);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('We can\'t create the Wishlist right now.')
            );
        }
        try {
            $item = $this->cartItem->create()->load($itemId);
            if (!$item) {
                throw new LocalizedException(
                    __('The requested cart item doesn\'t exist.')
                );
            }

            $productId = $item->getProductId();
            $buyRequest = $item->getBuyRequest();
            $this->checkoutSession->setVendorId($item->getVendorId());
            $wishlist->addNewItem($productId, $buyRequest);
            $this->cartItemRepository->deleteById($item->getQuoteId(), $itemId);
            $wishlist->save();
            $isMoveSuccess = true;
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('The requested Wish List Item doesn\'t exist.'. $e->getMessage())
            );
        }
        if ($isMoveSuccess) {
            return true;
        } else {
            throw new LocalizedException(__('Can\'t move this product into wishlist'));
        }
    }
}
