<?php

namespace Ktpl\ExtendedCheckoutApi\Plugin;

class StockMessage
{
    protected $quoteRepository;
    protected $cartItemExtensionFactory;
    protected $stockRegistry;
    protected $mdcProduct;
    protected $productRepository;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtensionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \MDC\Catalog\Model\Product $mdcProduct,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->stockRegistry = $stockRegistry;
        $this->cartItemExtensionFactory = $cartItemExtensionFactory;
        $this->mdcProduct = $mdcProduct;
        $this->productRepository = $productRepository;
    }

    public function aroundGetList(\Magento\Quote\Api\CartItemRepositoryInterface $subject, \Closure $proceed, $cartId)
    {
        $cartItems = $proceed($cartId);
        foreach ($cartItems as $item) {
            $extensionAttributes = $item->getExtensionAttributes();

            if ($extensionAttributes === null) {
                $extensionAttributes = $this->cartItemExtensionFactory->create();
            }
            $produt = $this->productRepository->get($item->getSku());
            $produtId = $produt->getId();
            $vendorId = $item->getVendorId();
            $stock = $this->mdcProduct->checkVendordSimpleStockStatus($vendorId, $produtId);
            if ($stock) {
                $message = 1;
            } else {
                $message = 0;
            }
            $extensionAttributes->setIsInStock($message);
            $item->setExtensionAttributes($extensionAttributes);
        }

        return $cartItems;
    }
}