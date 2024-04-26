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
namespace Magedelight\Quote\Model;

use Magedelight\Quote\Api\CartInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class Cart implements CartInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    protected $customMessage;

    /**
     * Cart constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepoInterface
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepoInterface,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magedelight\Sales\Api\Data\CustomMessageInterface $customMessage,
        Json $serializer = null
    ) {
        $this->request = $request;
        $this->cartRepoInterface = $cartRepoInterface;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_vendorHelper = $vendorHelper;
        $this->_helper = $helper;
        $this->quoteRepository = $quoteRepository;
        $this->vendorProductResource = $vendorProductResource;
        $this->customMessage = $customMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $requestData = $this->request->getParam('cartItem');
        $product = $this->productRepository->getById($requestData['product_id']);
        $vendorId = $requestData['vendor_id'];
        $params = [];
        $vendorName = $this->_vendorHelper->getVendorNameById($vendorId);

        /* Add Additonal Information */
        if ($vendorId) {
            $additionalOptions = [];
            if ($additionalOption = $product->getCustomOption('additional_options')) {
                $additionalOptions = (array)$this->serializer->unserialize($additionalOption->getValue());
            }
            if (!empty($additionalOptions)) {
                if (!in_array('vendor', array_column($additionalOptions, 'code'))) {
                    $additionalOptions[] = [
                        'code' => 'vendor',
                        'label' => __('Sold By'),
                        'value' => $vendorName
                    ];
                }
            } else {
                $additionalOptions[] = [
                    'code' => 'vendor',
                    'label' => __('Sold By'),
                    'value' => $vendorName
                ];
            }
            /* add the additional options array with the option code additional_options*/
            $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        }

        // Check product type
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productId = $product->getId();
        }else{
            if (isset($requestData['product_option']['extension_attributes']['configurable_item_options'])) {
                $myArray = $requestData['product_option']['extension_attributes']['configurable_item_options'];
                foreach ($myArray as $array) {
                    $selectedConfigurableOptions[$array['option_id']] = $array['option_value'];
                }
                $params['super_attribute'] = $selectedConfigurableOptions;
            }
            $childProduct = $product->getTypeInstance()->getProductByAttributes($selectedConfigurableOptions, $product);
            $productId = $childProduct->getId();
        }

        $price = $this->_helper->getVendorFinalPrice($vendorId, $productId);
        if($price < 1){
            $this->customMessage->setMessage(
                __('The requested quantity from selected seller is not available for this product.'));
            $this->customMessage->setStatus(false);
            /* Disallow adding product to cart if price is not available for that product. */
            return $this->customMessage;
        }

        if (isset($requestData['qty'])) {
            $params['qty'] = $requestData['qty'];
        }

        $params['vendor_id'] = $vendorId;
        $params['vendor'] = $vendorId;
        $quote = $this->cartRepoInterface->get($requestData['quote_id']);

        $dataObject = new \Magento\Framework\DataObject();
        $dataObject->addData($params);
        $item = $quote->addProduct($product, $dataObject)->save();

        /* Handel specific case for reorder */
        if (!$vendorId) {
            $vendorId = $item->getVendorId();
        }

        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        $item->setVendorId($vendorId);
        $item->setVendorSku(
            $this->vendorProductResource->getVendorProductSku($item->getProductId(), $vendorId)
        );
        $item->save();
        $this->quoteRepository->save($quote->collectTotals());

        /* Update vendor and vendor product details for a child of configurable product. */
        $savedQuote = $this->quoteRepository->get($requestData['quote_id']);
        foreach ($savedQuote->getAllItems() as $quoteItem){
            if($quoteItem->getParentItemId() === $item->getId()){
                $quoteItem->setVendorId($vendorId);
                $quoteItem->setVendorSku(
                    $this->vendorProductResource->getVendorProductSku($quoteItem->getProductId(), $vendorId)
                );
                $quoteItem->save();
            }
        }
        /* Update vendor and vendor product details for a child of configurable product. */
        $this->customMessage->setMessage(
            __('Product successfully added to cart.')
        );
        return $this->customMessage->setStatus(true);
    }
}
