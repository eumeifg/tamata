<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductView\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const SHORT_DESC_TITLE  = 'productdetailpagesettings/general/shortdescriptiontitle';
    const LONG_DESC_TITLE   = 'productdetailpagesettings/general/longdescriptiontitle';

    protected $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

   public function getReviewSummary($productId)
    {
        $reviewFactory = $this->_reviewFactory->create();
        $product = $this->_productFactory->create()->load($productId);
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $reviewFactory->getEntitySummary($product, $storeId);

        $reviewData['ratingSummary'] = $product->getRatingSummary()->getRatingSummary();
        $reviewData['reviewCount'] = $product->getRatingSummary()->getReviewsCount();

        return $reviewData;
    }
    public function getReviewCount($productId) {
        $reviewFactory = $this->_reviewFactory->create();
        $product = $this->_productFactory->create()->load($productId);
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $reviewFactory->getEntitySummary($product, $storeId);

      return $product->getRatingSummary()->getReviewsCount();
    }
    public function getReviewsUrl($useDirectLink = false,$productId)
    {
        $product = $this->_productFactory->create()->load($productId);
        if ($useDirectLink) {
            return $this->getUrl(
                'review/product/list',
                ['id' => $product->getId(), 'category' => $product->getCategoryId()]
            );
        }
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }

    public function getShortDescTitle()
    {
        return $this->_scopeConfig->getValue(self::SHORT_DESC_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLongDescTitle()
    {
        return $this->_scopeConfig->getValue(self::LONG_DESC_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
