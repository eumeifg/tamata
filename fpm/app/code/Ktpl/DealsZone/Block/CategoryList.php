<?php
/**
 * Ktpl
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_DealsZone
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com/)
 * @license     https://https://www.KrishTechnolabs.com/
 */

namespace Ktpl\DealsZone\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\Helper\Data;

/**
 * Class TopProducts
 * @package Ktpl\TopCategory\Block
 */
class CategoryList extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var Data
     */
    protected $categoryRepository;

    protected $storeManager;

    protected $_categoryCollectionFactory;

    protected $_categoryFactory;

    protected $localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $data = []
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryFactory           = $categoryFactory;
        $this->categoryRepository         = $categoryRepository;
        $this->storeManager               = $storeManager;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    public function getTopCategoryData($limit = 0)
    {
        $collection = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect(
                ['entity_id', 'name', 'image', 'url_key', 'url_path', 'thumbnail','discount_label','dealzone_image']
            )
            ->addAttributeToFilter('is_deal_zone', '1')
            ->setOrder('entity_id', 'ASC');
        return $collection;
    }

    public function getConfig($config_path)
    {
        return $this->storeManager->getStore()->getConfig($config_path);
    }

    public function getThumbnailImagePath($thumbnail)
    {
        $_imgUrl = "";
        $currentStore = $this->storeManager->getStore();
        if (!empty($thumbnail)) {
            $_imgUrl = $currentStore
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/'.$thumbnail;
        } else {
            $imagePlaceholder = $this->helperImageFactory->create();
            $_imgUrl = $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('small_image'));
        }
        return $_imgUrl;
    }

    public function getCurrentLocale()
    {
        $currentLocaleCode = $this->localeResolver->getLocale();
        $languageCode = strstr($currentLocaleCode, '_', true);
        return $languageCode;
    }
}
