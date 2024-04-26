<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\Wishlist\Model;

use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\ProductFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;

class WishlistManager extends \Magento\Framework\View\Element\Template implements \Ktpl\Wishlist\Api\WishlistManagerInterface
{
    /**
     * @var customerSession
     */
    protected $customerSession;

    /**
     * @var jsonHelper
     */
    protected $jsonHelper;

    /**
     * @var productFactory
     */
    protected $productFactory;

    /**
     * @var wishlistRepository
     */
    protected $wishlistRepository;

    /**
     * @var messageManager
     */
    protected $messageManager;
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    private $wishlistHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSessionFactory;

    /**
     * @var CollectionFactory
     */
    protected $wishlistCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var Item
     */
    protected $_itemFactory;

    /**
     * @param Session $customerSession
     * @param Data $jsonHelper
     * @param ProductFactory $productFactory
     * @param WishlistFactory $wishlistRepository
     * @param ManagerInterface $messageManager
     * @param CollectionFactory $wishlistCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Session $customerSession,
        Data $jsonHelper,
        ProductFactory $productFactory,
        WishlistFactory $wishlistRepository,
        ManagerInterface $messageManager,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        CollectionFactory $wishlistCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory
    ) {
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->productFactory = $productFactory;
        $this->wishlistRepository = $wishlistRepository;
        $this->messageManager = $messageManager;
        $this->wishlistHelper = $wishlistHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->_itemFactory = $itemFactory;
    }

    public function add($val)
    {
        if(!$this->customerSession->isLoggedIn())
        {
            $product = $this->productFactory->create()->load($val);
            $addToWishlistUrl = $this->wishlistHelper->getAddParams($product);
            return $this->jsonHelper->jsonEncode([
                'status' => 'LOGIN_REQUIRED',
                'message' => __('Please login to your account to add product to wishlist'),
                'referer' => $addToWishlistUrl
            ]);
        }

        if(isset($val))
        {
            $product = $this->productFactory->create()->load($val);
            if($product->getId())
            {
                $customerData = $this->customerSession->getCustomerData();
                $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerData->getId(), true);
                $wishlist->addNewItem($product);
                $wishlist->save();

                $this->messageManager->addSuccess(__('%1 has been added to your Wish List.', $product->getName()));

                return $this->jsonHelper->jsonEncode([
                    'status' => 'SUCCESS',
                    'message' => __('Product added successfully!')
                ]);
            }
        }

        return $this->jsonHelper->jsonEncode([
            'status' => 'ERROR',
            'message' => __('Error in adding product to wishlist!')
        ]);
    }

    public function remove($val)
    {
        if(!$this->customerSession->isLoggedIn())
        {
            return $this->jsonHelper->jsonEncode([
                'status' => 'LOGIN_REQUIRED',
                'message' => __('Please login to your account to add product to wishlist')
            ]);
        }

        if(isset($val))
        {
            $product = $this->productFactory->create()->load($val);
            if($product->getId())
            {
                $customerData = $this->customerSession->getCustomerData();
                $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerData->getId(), true);

                foreach($wishlist->getItemCollection() as $item)
                {
                    if ($item->getProductId() == $product->getId())
                    {
                        $item->delete();
                        $wishlist->save();
                    }
                }

                $this->messageManager->addSuccess(__('%1 has been removed from your Wish List.', $product->getName()));
                return $this->jsonHelper->jsonEncode([
                    'status' => 'SUCCESS',
                    'message' => __('Product remove successfully!')
                ]);
            }
        }

        return $this->jsonHelper->jsonEncode([
            'status' => 'ERROR',
            'message' => __('Error in adding product to wishlist!')
        ]);
    }

    public function checkLoggedIn()
    {
        $customer = $this->customerSessionFactory->create();
        return $customer->getCustomer()->getId();
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
            throw new InputException(__('Customer id required'));
        } else {
            $collection =
                $this->wishlistCollectionFactory->create()->addCustomerIdFilter($customerId);

            $wishlistData = [];
            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id"      => $item->getWishlistId(),
                    "product_id"       => $item->getProductId(),
                    "store_id"         => $item->getStoreId(),
                    "added_at"         => $item->getAddedAt(),
                    "description"      => $item->getDescription(),
                    "qty"              => round($item->getQty()),
                    "product"          => $productInfo
                ];
                $wishlistData[] = $data;
            }
            return $wishlistData;
        }
    }

    /**
     * Return count of wishlist item for customer
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWishlistInfo($customerId){

        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new InputException(__('Customer id required'));
        } else {
            $collection =
                $this->wishlistCollectionFactory->create()
                    ->addCustomerIdFilter($customerId);

            $totalItems = count($collection);

            $data = [
                "total_items"      => $totalItems
            ];

            $wishlistData[] = $data;

            return $wishlistData;
        }
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        if ($productId == null) {
            throw new LocalizedException(__
            ('Invalid product, Please select a valid product'));
        }
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        try {
            $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);
            $wishlist->addNewItem($product);
            $returnData = $wishlist->save();
        } catch (NoSuchEntityException $e) {
            return $this->jsonHelper->jsonEncode([
                'status' => 'ERROR',
                'message' => __('Error in adding product to wishlist!')
            ]);
        }

        return $this->jsonHelper->jsonEncode([
            'status' => 'SUCCESS',
            'message' => __('Product added successfully!')
        ]);
    }

    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return bool|\Magento\Wishlist\Api\status
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId)
    {

        if ($wishlistItemId == null) {
            throw new LocalizedException(__
            ('Invalid wishlist item, Please select a valid item'));
        }
        $item = $this->_itemFactory->create()->load($wishlistItemId);
        if (!$item->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List Item doesn\'t exist.')
            );
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->wishlistRepository->create();

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
        } catch (\Exception $e) {

        }
        return true;
    }
}