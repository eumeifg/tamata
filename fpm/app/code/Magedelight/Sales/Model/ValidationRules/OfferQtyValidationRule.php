<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace  Magedelight\Sales\Model\ValidationRules;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * @inheritdoc
 */
class OfferQtyValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var ValidationResultFactory
     */
    protected $validationResultFactory;

    /**
     * OfferQtyValidationRule constructor.
     * @param CustomerCart $cart
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        CustomerCart $cart,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        ValidationResultFactory $validationResultFactory
    ) {
        $this->vendorProductFactory = $vendorProductFactory;
        $this->cart = $cart;
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $invalidItems = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            //associated simple product means skip loop;
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getProductType() == Configurable::TYPE_CODE) {
                $productId = $item->getOptionByCode('simple_product')->getProduct()->getId();
            } else {
                $productId = $item->getProductId();
            }

            $availableQty =  $this->getVendorQty($item->getVendorId(), $productId);
            if ($item->getQty() > $availableQty) {
                $invalidItems[] = $item->getName();
            }
        }

        if (count($invalidItems) > 0) {
            $validationErrors = [
                __(
                    "The requested quantity for item(s) [ '%1' ] is not available. Please update your cart or try other item(s).",
                    implode(', ', $invalidItems)
                )
            ];
        }
        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }

    /**
     *
     * @param type $vendorId
     * @param type $productId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorQty($vendorId, $productId)
    {
        $vendorProduct = $this->vendorProductFactory->create()->getVendorProduct($vendorId, $productId);

        if ($vendorProduct && (!($vendorProduct->getQty() === null))) {
            return $vendorProduct->getQty();
        }
        return 0;
    }
}
