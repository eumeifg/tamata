<?php

namespace Ktpl\DealsZone\Cron;

class AssignProduct
{

    const DEALSZONE_CATEGORY        = 'dealszonesettings/general/shortdescriptiontitle';

    protected $categoryLinkManagement;
    protected $categoryLink;
    protected $_categoryFactory;
    protected $_vendorProduct;
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,        
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagementInterface,
        \Magento\Catalog\Model\CategoryLinkRepository $CategoryLinkRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \MDC\Catalog\Model\Product $vendorProduct,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->categoryLinkManagement = $categoryLinkManagementInterface;
        $this->categoryLink = $CategoryLinkRepository;
        $this->_categoryFactory = $categoryFactory;
        $this->_vendorProduct = $vendorProduct;
        $this->_productRepository = $productRepository;
    }

    public function execute()
    {

      $dealzoneCategoryId = $this->getDealsZoneCategory();

      /*....Delete old product from dealszone category....*/
      $this->deleteOldDealsZoneProducts($dealzoneCategoryId);

      /*....To assign product to specified dealszone category in configuration settings....*/
      $productCollectionData = $this->getProductCollection();
 
      foreach ($productCollectionData as $product) {

        /*....To get all exiting category ids and assign product to old categories....*/
        $productData = $this->_productRepository->getById($product->getMarketplaceProductId());
        $existingCategoryIds = $productData->getCategoryIds();
        $existingCategoryIds[] = $dealzoneCategoryId;
        $allIds = implode(",",$existingCategoryIds);
        $allIds = explode(",", $allIds);
        $categoryIds = $allIds;
        
        /*....To chek if product have parent id then assign parent product to category....*/
        if ($product->getParentId()) {
          $this->getProductById($product->getParentId(), $categoryIds);
        }

        /*....To assign products to defined category....*/
        $this->categoryLinkManagement->assignProductToCategories(
              $product->getSku(),
              $categoryIds
          );
      }

      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dealszone.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info("DealsZone Cron job Run successfully!");
    }

    public function getProductById($id, $categoryIds)
    {
      $parentProductData = $this->_productRepository->getById($id);
      $this->categoryLinkManagement->assignProductToCategories(
              $parentProductData->getSku(),
              $categoryIds
          );
    }

    public function getDealsZoneCategory()
    {   
        return $this->scopeConfig->getValue(self::DEALSZONE_CATEGORY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductCollection()
    {   
        $collection = $this->_vendorProduct->getCollection(false);
        $collection->addFieldToFilter('special_price',['neq' => 0]);
        $collection->getSelect()->where('(CURDATE() between special_from_date AND special_to_date)');
        return $collection;
    }

    public function deleteOldDealsZoneProducts($dealzoneCategoryId) {
      $categoryProductData = $this->_categoryFactory->create()
                            ->load($dealzoneCategoryId)
                            ->getProductCollection()
                            ->addAttributeToSelect(array('sku'));

        foreach ($categoryProductData as $product) {
          $CategoryLinkRepository = $this->categoryLink;
          $CategoryLinkRepository->deleteByIds($dealzoneCategoryId,$product->getSku());
        }
    }
}
