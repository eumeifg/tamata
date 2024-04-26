<?php

namespace Ktpl\ExtendedCheckoutApi\Plugin;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\Totals\TotalsItemInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class TotalStockMessage {

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $quoteItem;
    private $quoteItemFactory;
    private $itemResourceModel;
    private $mdcProduct;

    /**
     * @var CartRepositoryInterface
     */
    public $quoteRepository;

    public function __construct(
        \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        \MDC\Catalog\Model\Product $mdcProduct,
        EventManager $eventManager,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->totalsItemInterface = $totalItemExtension;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quote = $quote;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteItem = $quoteItem;
        $this->mdcProduct = $mdcProduct;
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
    }

     /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartTotalRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGet(CartTotalRepositoryInterface $totalItemRepo,$result)
    {
        $result = $this->setStockMessage($result);
        return $result;
    }

    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    private function setStockMessage($quote) {

        if (($quote->getData())) {
             $extensionAttributes = null;
            foreach ($quote->getItems() as $item) {
                $data = [];
                $extensionAttributes = $item->getExtensionAttributes();
                if(!$extensionAttributes)
                {
                    $extensionAttributes = $this->totalsItemInterface->create();
                }
                $itemId = $item->getItemId();
                $data = $this->quoteItem->load($itemId);              
                $quoteId = $data->getQuoteId();
                $loadedProduct = $this->productRepository->get($data->getSku());
                $produtId = $loadedProduct->getId();
                $vendorId = $data->getVendorId();
                $stock = $this->mdcProduct->checkVendordSimpleStockStatus($vendorId, $produtId);
                if ($stock) {
                    $message = 1;
                } else {
                    $message = 0;
                }
                $item->setExtensionAttributes($extensionAttributes->setIsInStock($message));
                $quoteData = $this->quoteRepository->get($quoteId);
                $customerId = $quoteData->getCustomerId();
                $this->eventManager->dispatch('ktpl_pushnotification_abandon_cart', ['kp_quote_id' => $quoteId, 'kp_quote_item_id' => $itemId, 'kp_quote_customer_id' => $customerId]);
            }
           
            return $quote;
        }
    }
}