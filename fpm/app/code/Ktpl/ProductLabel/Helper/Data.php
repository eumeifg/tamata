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

namespace Ktpl\ProductLabel\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;
use Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var ProductLabelRepositoryInterface
     */
    protected $plabelRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    protected $_state;

    public function __construct(
        Context $context,
        ProductLabelRepositoryInterface $plabelRepository,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory $productLabelCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Ktpl\ProductLabel\Block\ProductLabel\ProductLabel $productLabel,
        \Magento\Framework\App\State $state
    ) {
        $this->plabelRepository      = $plabelRepository;
        $this->filterBuilder         = $filterBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productLabelCollectionFactory     = $productLabelCollectionFactory;
        $this->storeManager          = $storeManager;
        $this->registry              = $registry;
        $this->productLabel = $productLabel;
        $this->_state = $state;

        parent::__construct($context);
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getProductLabelIds($product)
    {
        if ($this->_state->getAreaCode() == "frontend") {
          $storeId = $this->storeManager->getStore()->getId();
          $productLabelsCollection = $this->productLabelCollectionFactory->create();
          $productLabelList        = $productLabelsCollection
                  ->addStoreFilter($storeId)
                  ->addFieldToSelect(array('product_label_id','attribute_id'))
                  ->addFieldToFilter('is_active', true)
                  ->getData();

          $plabelIds = [];
          $currentProduct = $this->productLabel->getProduct();
          $productEntity  = $currentProduct->getResourceCollection()->getEntity();

          foreach ($productLabelList as $key => $label) {
              $attribute = $productEntity->getAttribute($label['attribute_id']);
              if ($attribute) {
                $optionIds = $currentProduct->getCustomAttribute($attribute->getAttributeCode());

                if (is_null($optionIds)) {
                  continue;
                }

                $value = $optionIds->getValue();
                if ($value) {
                  $plabelIds[] = $label['product_label_id'];
                }
              }
          }
        } else {
          $plabelIds = $product->getKtplProductLabelIds();
          if (!is_array($plabelIds)) {
              $plabelIds = explode(',', $plabelIds);
          }

          foreach ($plabelIds as $key => $value) {
              $plabelIds[$key] = (int) $value;
          }
        }

        return $plabelIds;
    }

    public function getSearchCriteriaOnProductLabelIds($plabelIds)
    {
        $filters   = [];
        $filters[] = $this->filterBuilder
            ->setField(ProductLabelInterface::PRODUCTLABEL_ID)
            ->setConditionType('in')
            ->setValue($plabelIds)
            ->create();
        $this->searchCriteriaBuilder->addFilters($filters);

        $sort = $this->sortOrderBuilder
            ->setField(ProductLabelInterface::PRODUCTLABEL_NAME)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $this->searchCriteriaBuilder->addSortOrder($sort);

        return $this->searchCriteriaBuilder->create();
    }

    public function getProductPLabels($product)
    {
        $plabelIds      = $this->getProductLabelIds($product);
        $searchCriteria = $this->getSearchCriteriaOnProductLabelIds($plabelIds);

        return $this->plabelRepository->getList($searchCriteria)->getItems();
    }
}
