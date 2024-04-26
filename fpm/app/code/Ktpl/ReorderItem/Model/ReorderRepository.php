<?php
namespace Ktpl\ReorderItem\Model;

use Ktpl\ReorderItem\Api\ReorderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart\CartInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class OrderService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderRepository extends DataObject implements ReorderRepositoryInterface
{
    protected $orderRepository;
    protected $cart;
    protected $productRepository;
    protected $messageManager;
    protected $stockRegistry;
    protected $quoteRepository;
    private $requestInfoFilter;
    protected $customerRepository;
    protected $quoteFactory;
    protected $customerId;
    protected $orderItemRepository;
    protected $vendorHelper;
    protected $serializer;
    protected $cartManagementInterface;
    protected $reorderResponseFactory;
    protected $vendorCatalogHelper;
    protected $mdcProduct;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        Json $serializer,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Ktpl\ReorderItem\Api\Data\ReorderResponseMessageInterfaceFactory $reorderResponseFactory,
        \MDC\Catalog\Model\Product $vendorCatalogHelper,
        \MDC\Catalog\Model\Product $mdcProduct
    ){
        $this->orderRepository = $orderRepository;
        $this->cart = $cart;
        $this->_storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->vendorHelper = $vendorHelper;
        $this->serializer = $serializer;
        $this->cartRepository = $cartRepository;
        $this->reorderResponseFactory = $reorderResponseFactory;
        $this->vendorCatalogHelper = $vendorCatalogHelper;
        $this->mdcProduct = $mdcProduct;
    }

    /**
     * Add order item for the customer
     * @param int $customerId
     * @param int $orderId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reorderItem($customerId, $orderId, $vendorId, $itemId)
    {
        $response = [];
        $reorderResponseFactory = $this->reorderResponseFactory->create();
        $this->customerId = $customerId;
        $cart = $this->cart;
        try {
            $item = $this->orderItemRepository->get($itemId);
            $loadedProduct = $this->productRepository->get($item->getSku());
            //$itemStock = $loadedProduct->getExtensionAttributes()->getStockItem()->getQty();
            $stock = $this->mdcProduct->checkVendordSimpleStockStatus($vendorId, $loadedProduct->getId());
            if (empty($stock)) {
                $response['status'] = false;
                $response['message'] = __("Your product is out of stock");
            } else {
                $quote = $this->addOrderItem($item);
                $this->saveQuote();
                $response['status'] = true;
                $response['message'] = __("Product successfully added to your cart");
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response['status'] = false;
            $response['message'] = __("The product wasn't found. Verify the product and try again.");
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = __('The product does not exist.');
        }

        $reorderResponseFactory->setStatus($response['status']);
        $reorderResponseFactory->setMessage($response['message']);
        return $reorderResponseFactory;
    }

    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param true|null $qtyFlag if is null set product qty like in order
     * @return $this
     */
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }

            /*....To set price for cart items while re-ordering....*/
            $info->setPrice($orderItem->getOriginalPrice());
            $info->setBasePrice($orderItem->getOriginalPrice());
            $info->setCustomPrice($orderItem->getOriginalPrice());

            $additionalOptions = $orderItem->getOptionByCode('additional_options');
            $vendorId = $orderItem->getVendorId();
            $info->setVendorId($vendorId);
            $soldBy = $this->vendorHelper->getVendorNameById($vendorId);
            $additionalOptions[] =  ['code'  => 'vendor',
                                     'label' => __('Sold By'),
                                     'value' => $soldBy
                                    ];
            $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
            $this->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();

        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();
            if ($minimumQty && $minimumQty > 0 && !$request->getQty()) {
                $request->setQty($minimumQty);
            }
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result = $e->getMessage();
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        return $this;
    }

     /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $quote = $this->createCustomerCart($this->customerId, $storeId);

        if (!$this->hasData('quote')) {
            $this->setData('quote', $quote);
        }
        return $this->_getData('quote');
    }

    protected function createCustomerCart($customerId, $storeId)
    {
        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customer = $this->customerRepository->getById($customerId);
            $quote = $this->quoteFactory->create();
            $quote->setStoreId($storeId);
            $quote->setCustomer($customer);
            $quote->setCustomerIsGuest(0);
        }
        return $quote;
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->setData('quote', $quote);
        return $this;
    }

    /**
     * Get product object based on requested product information
     *
     * @param   Product|int|string $productInfo
     * @return  Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (!$product->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The product wasn't found. Verify the product and try again.")
                );
            }
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The product wasn't found. Verify the product and try again."),
                    $e
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("The product wasn't found. Verify the product and try again.")
            );
        }
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (!is_array($product->getWebsiteIds()) || !in_array($currentWebsiteId, $product->getWebsiteIds())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("The product wasn't found. Verify the product and try again.")
            );
        }
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   \Magento\Framework\DataObject|int|array $requestInfo
     * @return  \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        $this->getRequestInfoFilter()->filter($request);

        return $request;
    }

    /**
     * Getter for RequestInfoFilter
     *
     * @deprecated 100.1.2
     * @return \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);
        }
        return $this->requestInfoFilter;
    }

    /**
     * Save cart
     *
     * @return $this
     */
    public function save()
    {
        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getQuote());
        return $this;
    }

    /**
     * Save cart (implement interface method)
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function saveQuote()
    {
        $this->save();

        $cartRepository = $this->cartRepository->getActiveForCustomer($this->customerId);
        foreach ($cartRepository->getAllItems() as $item) {
            $vendor_id = $item->getBuyRequest()->getVendorId();
            $item->setVendorId($vendor_id)->save();
        }
    }

}
