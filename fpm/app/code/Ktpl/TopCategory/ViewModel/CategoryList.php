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
 * @package     Ktpl_TopCategory
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com/)
 * @license     https://https://www.KrishTechnolabs.com/
 */
namespace Ktpl\TopCategory\ViewModel;

class CategoryList implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected $_categoryCollectionFactory;

    protected $_categoryFactory;

    protected $_helperData;

    protected $categoryRepository;

    protected $storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryFactory           = $categoryFactory;
        $this->categoryRepository         = $categoryRepository;
        $this->storeManager               = $storeManager;
    }

    public function getTopCategoryData($limit = 0)
    {
        $collection = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect(['entity_id', 'name', 'image', 'url_key', 'url_path', 'thumbnail'])
            ->addAttributeToFilter('is_top_category', '1')
            ->setOrder('entity_id', 'ASC');
        return $collection;
    }

    public function getThumbnailImagePath($thumbnail)
    {
        $_imgUrl = "";
        $currentStore = $this->storeManager->getStore();
        $_imgUrl = $currentStore
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/'.$thumbnail;
        return $_imgUrl;
    }
}
