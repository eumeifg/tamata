<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Product\View;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template;

class ProductVendor extends Template
{

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $_micrositeHelper;

    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    protected $_productCondition;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Catalog\Model\Source\Condition $productCondition
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magedelight\Catalog\Model\Source\Condition $productCondition,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        array $data = []
    ) {
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->_registry = $coreRegistry;
        $this->_priceHelper = $priceHelper;
        $this->_helper = $helper;
        $this->_productCondition = $productCondition;
        $this->_micrositeHelper = $micrositeHelper;
        parent::__construct($context, $data);
    }
    /**
     *
     * @return collection \Magedelight\Catalog\Model\Product
     */
    public function getAvailableVendorsForProduct()
    {
        $productId = $this->getCurrentProductId();
        $collection = $this->_helper->getAvailableVendorsForProduct($productId, false, true);
        return $collection;
    }
    /**
     *
     * @return collection \Magedelight\Catalog\Model\Product
     */
    public function getAvailableVendorsForProductAjax()
    {
        if ($productId = $this->getRequest()->getParam('productId', false)) {
            $collection = $this->_helper->getAvailableVendorsForProduct($productId, false, true);
            return $collection;
        }
        return null;
    }
    /**
     *
     * @return int productId
     */
    public function getCurrentProductId()
    {
        return $this->_registry->registry('current_product')->getId();
    }
    /**
     *
     * @return object
     */
    public function getProductDefaultVendor()
    {
        if ($productId = $this->getRequest()->getParam('productId', false)) {
            $collection = $this->_vendorProductFactory->create()->getProductDefaultVendor(false, $productId);
        } else {
            $collection = $this->_vendorProductFactory->create()->getProductDefaultVendor(
                $this->getRequest()->getParam('v', false),
                $this->getCurrentProductId()
            );
        }

        return $collection;
    }
    /**
     *
     * @return Vendor Name
     */
    public function getProductDefaultVendorName()
    {
        return $this->getProductDefaultVendor()->getVendorName();
    }
    /**
     *
     * @return Vendor ID
     */
    public function getProductDefaultVendorId()
    {
        return $this->getProductDefaultVendor()->getVendorId();
    }
    /**
     *
     * @return Total No. of vendor for product
     */
    public function getProductNoOfVendors()
    {
        return $this->getAvailableVendorsForProduct()->count() - 1;
    }
    /**
     *
     * @return Product URL
     */
    public function getCurrentProductUrl()
    {
        return $this->_registry->registry('current_product')->getProductUrl();
    }
    /**
     *
     * @param array $data
     * @return string
     */
    public function getPriceHtml($data)
    {
        return $this->_helper->getPriceHtml($data, $this->getCurrentProductId());
    }
    /**
     *
     * @return string product Type
     */
    public function getTypeId()
    {
        return $this->_registry->registry('current_product')->getTypeId();
    }
    /**
     *
     * @return object \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }
    /**
     *
     * @return string Vendor Microsite URL
     */
    public function getVendorUrl()
    {
        return $this->getUrl('microsite/vendor?vid=' . $this->getProductDefaultVendorId());
    }

    /**
     * @return vendor microsite url
     */
    public function getVendorMicrositeUrl()
    {
        return $this->_micrositeHelper->getVendorMicrositeUrl($this->getProductDefaultVendorId());
    }

    /**
     * @param type $value
     * @return type
     */
    public function getConditionOptionText($value)
    {
        return $this->_productCondition->getOptionText($value);
    }

    /* @param string $price
     * @return string
     */
    public function formatPrice($price = 0)
    {
        return $this->_priceHelper->currency(floatval($price));
    }

    /* @param string $price
     * @return string
     */
    public function getMinimumPriceForProduct($product)
    {
        return $this->_priceHelper->currency(
            $this->_vendorProductFactory->create()->getMinimumPriceForProduct($product, true),
            true,
            false
        );
    }

    /**
     * @return \Magedelight\Catalog\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     *
     * @param integer $rating
     * @return integer
     */
    public function getRating($rating)
    {
        $totalRating = number_format($rating, 2, '.', '');
        return ($totalRating * 100)/5;
    }

    /**
     * Initial Sold By vendors list not required for Configurable product as multiple
     * offers are created on child and they are rendered on swatch selection.
     * @return boolean
     */
    public function showSoldByVendorList()
    {
        return ($this->getCurrentProduct()->getTypeId() == Configurable::TYPE_CODE) ? false : true;
    }
}
