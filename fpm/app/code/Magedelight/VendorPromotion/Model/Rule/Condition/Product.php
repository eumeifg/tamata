<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Model\Rule\Condition;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Rule\Model\Condition\Context;

/**
 * Description of Rule
 *
 * @author Rocket Bazaar Core Team
 */
class Product extends \Magento\SalesRule\Model\Rule\Condition\Product
{

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    private $vendorProductFactory;

    /**
     * Product constructor.
     * @param Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        Session $checkoutSession,
        array $data = []
    ) {
        $this->_backendData = $backendData;
        $this->_config = $config;
        $this->_productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->_productResource = $productResource;
        $this->_attrSetCollection = $attrSetCollection;
        $this->_localeFormat = $localeFormat;
        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data);
        $this->vendorProductFactory = $vendorProductFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Validate product attribute value for condition
     *
     * @param   object|array|int|string|float|bool $validatedValue product attribute value
     * @return  bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        if ($this->getAttribute() == 'sku') {
            $validatedValue = $this->getVendorSkuFromSku($validatedValue);
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        /**
         * Comparison operator
         */
        $option = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($option) {
            case '==':
            case '!=':
                if (is_array($value)) {
                    if (!is_array($validatedValue)) {
                        return false;
                    }
                    $result = !empty(array_intersect($value, $validatedValue));
                } else {
                    if (is_array($validatedValue)) {
                        $result = count($validatedValue) == 1 && array_shift($validatedValue) == $value;
                    } else {
                        $result = $this->_compareValues($validatedValue, $value);
                    }
                }
                break;

            case '<=':
            case '>':
                if (!is_scalar($validatedValue)) {
                    return false;
                }
                $result = $validatedValue <= $value;
                break;

            case '>=':
            case '<':
                if (!is_scalar($validatedValue)) {
                    return false;
                }
                $result = $validatedValue >= $value;
                break;

            case '{}':
            case '!{}':
                if (is_scalar($validatedValue) && is_array($value)) {
                    foreach ($value as $item) {
                        if (stripos($validatedValue, (string)$item) !== false) {
                            $result = true;
                            break;
                        }
                    }
                } elseif (is_array($value)) {
                    if (!is_array($validatedValue)) {
                        return false;
                    }
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    if (is_array($validatedValue)) {
                        $result = in_array($value, $validatedValue);
                    } else {
                        $result = $this->_compareValues($value, $validatedValue, false);
                    }
                }
                break;

            case '()':
            case '!()':
                if (is_array($validatedValue)) {
                    $result = count(array_intersect($validatedValue, (array)$value)) > 0;
                } else {
                    $value = (array)$value;
                    foreach ($value as $item) {
                        if ($this->_compareValues($validatedValue, $item)) {
                            $result = true;
                            break;
                        }
                    }
                }
                break;
        }

        if ('!=' == $option || '>' == $option || '<' == $option || '!{}' == $option || '!()' == $option) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * Set vendor specific Cart Item data for promocode condition
     *
     * @param string $sku
     * @return array|string
     */
    protected function getVendorSkuFromSku($sku)
    {

        try {
            $product = $this->productRepository->get($sku);
        } catch (Exception $e) {
            return $sku;
        }

        $quoteId = $this->checkoutSession->getQuoteId();

        if (!$quoteId) {
            return $sku;
        }

        $productIds = $vendorIds = $vendorSkus = [];
        $items = $this->checkoutSession->getQuote()->getAllItems();
//      $items = $this->checkoutSession->getQuoteItemsData();
        if (!$items) {
            return $sku;
        }
//        foreach ($items as $item) {
//            if ($item[0] == $product->getId()) {
//                $productId[] = $product->getId();
//                $vendorIds[] = $item[1];
//            }
//        }

        foreach ($items as $item) {
            if ($item->getProductId() == $product->getId()) {
                $productIds[] = $product->getId();
                $vendorIds[] = $item->getVendorId();
            }
        }

        $vendorProduct = $this->vendorProductFactory->create()->getCollection()
            ->addFieldToSelect('vendor_sku')
            ->addFieldToFilter('vendor_id', ['in' => $vendorIds])
            ->addFieldToFilter('marketplace_product_id', ['in' => $productIds]);
        foreach ($vendorProduct->getData() as $data) {
            $vendorSkus[] = $data['vendor_sku'];
        }

        return $vendorSkus;
    }
}
