<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\Productslider\Helper\AbstractData;
use Ktpl\Productslider\Model\ResourceModel\Slider\Collection;
use Ktpl\Productslider\Model\SliderFactory;
use Zend_Serializer_Exception;
use Magento\Framework\Locale\Resolver;

/**
 * Class Data
 * @package Ktpl\Productslider\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'productslider';

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var Resolver
     */
    protected $localeResolver;

    protected $_categoryFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param HttpContext $httpContext
     * @param SliderFactory $sliderFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        DateTime $date,
        HttpContext $httpContext,
        SliderFactory $sliderFactory,
        Resolver $localeResolver,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->date = $date;
        $this->httpContext = $httpContext;
        $this->sliderFactory = $sliderFactory;
        $this->localeResolver = $localeResolver;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return Collection
     */
    public function getActiveSliders()
    {
        /** @var Collection $collection */
        $collection = $this->sliderFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'customer_group_ids',
                ['finset' => $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)]
            )
            ->addFieldToFilter('status', 1);

        $collection->getSelect()
            ->where('FIND_IN_SET(0, store_ids) OR FIND_IN_SET(?, store_ids)', $this->storeManager->getStore()->getId())
            ->where('from_date is null OR from_date <= ?', $this->date->date())
            ->where('to_date is null OR to_date >= ?', $this->date->date());

        return $collection;
    }

    /**
     * Retrieve all configuration options for product slider
     *
     * @return string
     * @throws Zend_Serializer_Exception
     */
    public function getAllOptions()
    {
        $sliderOptions = '';
        $allConfig = $this->getModuleConfig('slider_design');
        foreach ($allConfig as $key => $value) {
            if ($key == 'item_slider') {
                $sliderOptions = $sliderOptions . $this->getResponseValue();
            } elseif ($key != 'responsive') {
                if (in_array($key, ['loop', 'nav', 'dots', 'lazyLoad', 'autoplay', 'autoplayHoverPause'])) {
                    $value = $value ? 'true' : 'false';
                }
                $sliderOptions = $sliderOptions . $key . ':' . $value . ',';
            }
        }

        return '{' . $sliderOptions . '}';
    }

    /**
     * @return bool
     */
    public function isResponsive()
    {
        if ($this->getModuleConfig('slider_design/responsive') == 1) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve responsive values for product slider
     *
     * @return string
     * @throws Zend_Serializer_Exception
     */
    public function getResponseValue()
    {
        $responsiveOptions = '';
        $responsiveConfig = $this->isResponsive() ?
        $this->unserialize($this->getModuleConfig('slider_design/item_slider')) : [];

        foreach ($responsiveConfig as $config) {
            if ($config['size'] && $config['items']) {
                $responsiveOptions = $responsiveOptions . $config['size'] . ':{items:' . $config['items'] . '},';
            }
        }

        $responsiveOptions = rtrim($responsiveOptions, ',');

        return 'responsive:{' . $responsiveOptions . '}';
    }

    /**
     * Retrieve local for specific store
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        $currentLocaleCode = $this->localeResolver->getLocale();
        $languageCode = strstr($currentLocaleCode, '_', true);
        return $languageCode;
    }

    public function getCategoryNameById($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        $categoryUrl = $category->getUrl();
        return $categoryUrl;
    }
}
