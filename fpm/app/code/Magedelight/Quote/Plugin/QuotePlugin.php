<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Quote
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Quote\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;

class QuotePlugin
{
    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * QuotePlugin constructor.
     * @param \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository
     * @param \Magento\Catalog\Helper\Product $productHelper
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
        $this->cartItemExtension = $cartItemExtension;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGet(CartRepositoryInterface $cartRepository, $result)
    {
        if ($result->getItemsCount() > 0) {
            $result = $this->setAttributeValue($result);
        }
        return $result;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGetActiveForCustomer(CartRepositoryInterface $cartRepository, $result)
    {
        if ($result->getItemsCount() > 0) {
            $result = $this->setAttributeValue($result);
        }
        return $result;
    }

    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    private function setAttributeValue($quote)
    {
        if (($quote->getData())) {
            foreach ($quote->getItems() as $item) {
                $extensionAttributes = $item->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->cartItemExtension->create();
                }
                $productData = $this->productRepository->create()->getById($item->getProductId());
                $extensionAttributes->setImage($this->productHelper->getImageUrl(
                    $productData
                ));
                $item->setExtensionAttributes($extensionAttributes);
                break;
            }
            return $quote;
        }
    }
}
