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
namespace Magedelight\VendorPromotion\Model\Condition\Product;

use Magento\Rule\Model\Condition\Context;
use Magento\Checkout\Model\Session;

/**
 * Description of Rule
 *
 * @author Rocket Bazaar Core Team
 */
class Sku extends \Magento\SalesRule\Model\Rule\Condition\Product
{

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    private $vendorProductFactory;

    /**
     * @param Context $context
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
        Session $session,
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
        $this->session = $session;
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
        } else {
            $value = $this->getValueParsed();
        }
        
        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        /**
         * Comparison operator
         */
        $option = $this->getOperatorForValidate();

        /*
         * if operator requires array and it is not, or on opposite, return false
         */
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($option) {
            case '==':
            case '!=':
                if (is_array($value)) {
                    if (is_array($validatedValue)) {
                        $result = array_intersect($value, $validatedValue);
                        $result = !empty($result);
                    } else {
                        return false;
                    }
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
                } else {
                    $result = $validatedValue <= $value;
                }
                break;

            case '>=':
            case '<':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = $validatedValue >= $value;
                }
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
                    if (is_array($validatedValue)) {
                        $result = array_intersect($value, $validatedValue);
                        $result = !empty($result);
                    } else {
                        return false;
                    }
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
     *
     * @param type $sku
     * @return type
     */
    
    protected function getVendorSkuFromSku($sku)
    {
        $quoteId = $this->session->getQuoteId();

        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return $sku;
        }
        
        if (!$quoteId) {
            return $sku;
        }
        
        /**
         * @todo Magedelight\VendorPromotion\Observer\CartSaveAfter
         */
        $quoteItemsData = $this->session->getQuoteItemsData();
        $productId = $vendor_id = $vendorSku = [];
        if (!$quoteItemsData){
            return $sku;
        }
        foreach ($quoteItemsData as $item) {
            if ($item[0] == $product->getId()) {
                $productId[] = $product->getId();
                $vendor_id[] = $item[1];
            }
        }

        $vendorProduct = $this->vendorProductFactory->create()->getCollection()
            ->addFieldToSelect('vendor_sku')
            ->addFieldToFilter('vendor_id', ['in' => $vendor_id])
            ->addFieldToFilter('marketplace_product_id', ['in' => $productId]);
        foreach ($vendorProduct->getData() as $data) {
            $vendorSku[] = $data['vendor_sku'];
        }

        return $vendorSku;
    }
}
