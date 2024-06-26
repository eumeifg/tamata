<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Search;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Amasty\Shopby\Helper\FilterSetting;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Class RequestGenerator
 * @package Amasty\Shopby\Model\Search
 */
class RequestGenerator extends \Magento\CatalogSearch\Model\Search\RequestGenerator
{
    const FAKE_SUFFIX = '_amshopby_filter_';
    /**
     * @var FilterSetting
     */
    protected $settingHelper;
    /**
     * @param CollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        FilterSetting $settingHelper
    ) {
        $this->settingHelper = $settingHelper;
        parent::__construct($productAttributeCollectionFactory);
    }

    /**
     * @return array
     */
    public function generate()
    {
        $requests = [];
        $requests['catalog_view_container'] = $this->generateFakeRequest(
            EavAttributeInterface::IS_FILTERABLE,
            'catalog_view_container'
        );
        $requests['quick_search_container'] = $this->generateFakeRequest(
            EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
            'quick_search_container'
        );
        return $requests;
    }

    /**
     * @param $attributeType
     * @param $container
     * @return array
     */
    protected function generateFakeRequest($attributeType, $container)
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            $filterSetting = $this->settingHelper->getSettingByAttribute($attribute);
            if ($attribute->getData($attributeType) && $filterSetting->isUseAndLogic()) {
                foreach ($attribute->getOptions() as $key => $option) {
                    if ($key == 0) {
                        continue;
                    }
                    $attributeCode = $attribute->getAttributeCode() . self::FAKE_SUFFIX . $key;
                    $queryName = $attributeCode . '_query';

                    $request['queries'][$container]['queryReference'][] = [
                        'clause' => 'should',
                        'ref' => $queryName,
                    ];
                    $filterName = $attributeCode . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [['ref' => $filterName]],
                    ];

                    $request['filters'][$filterName] = [
                        'type' => FilterInterface::TYPE_TERM,
                        'name' => $filterName,
                        'field' => $attributeCode,
                        'value' => '$' . $attributeCode . '$',
                    ];
                }
            }
        }

        return $request;
    }
}
