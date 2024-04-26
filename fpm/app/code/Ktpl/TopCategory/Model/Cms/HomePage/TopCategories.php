<?php
namespace Ktpl\TopCategory\Model\Cms\HomePage;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class TopCategories extends \Magento\Framework\DataObject
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * TopCategories constructor.
     * @param CollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Ktpl\TopCategory\Api\Data\TopCategoryInterface[]
    */
    public function build()
    {
        $store = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
        $collection = $this->categoryCollectionFactory->create()
                    ->addAttributeToSelect(['entity_id', 'name', 'image', 'url_key', 'url_path'])
                    ->addExpressionAttributeToSelect(
                        'thumbnail',
                        'CONCAT("'.$mediaUrl.'","",{{thumbnail}})', ['thumbnail']
                    )
                    ->setStore($store)
                    ->addAttributeToFilter('is_top_category', '1')
                    ->setOrder('entity_id', 'ASC');
        return $collection->getItems();
    }
}
