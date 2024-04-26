<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Plugin;

use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class SearchIndexerPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class SearchIndexerPlugin
{
    /**
     * Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * SearchIndexerPlugin constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    )
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Get attribute options
     *
     * @param $dataProvider
     * @param $attributeData
     * @param null $productData
     * @param null $productAdditional
     * @param null $storeId
     * @return mixed
     */
    public function afterPrepareProductIndex(
        $dataProvider,
        $attributeData,
        $productData = null,
        $productAdditional = null,
        $storeId = null
    )
    {
        if ($productData === null) {
            return $attributeData;
        }

        $includeBundled = $this->getIndex()->getProperty('include_bundled');
        if(!empty($productData))
        {
            $productData = array_values($productData)[0];

            if (!$includeBundled) {
                if (isset($attributeData['options']) && !empty($attributeData['options'])) {
                    $attributeData['options'] = '';
                }
            }

            foreach ($attributeData as $attributeId => $value) {
                $attribute = $dataProvider->getSearchableAttribute($attributeId);

                if (!$includeBundled) {
                    if (isset($productData[$attributeId])
                        && isset($productData['type'])
                        && $productData['type'] == 'simple') {
                        $attributeData[$attributeId] = $productData[$attributeId];
                    }
                }

                if (!empty($value) && $attribute->getFrontendInput() == 'multiselect') {
                    $attribute->setStoreId($storeId);
                    $options = $attribute->getSource()->toOptionArray();
                    $optionLabels = [];
                    if (array_key_exists($attributeId, $productData)) {
                        foreach ($options as $optionValue) {
                            if (in_array($optionValue['value'], explode(',', $productData[$attributeId]))) {
                                $optionLabels[] = $optionValue['label'];
                            }
                        }
                    }

                    $attributeData[$attributeId] = implode(' | ', $optionLabels);
                }
            }
        }

        return $attributeData;
    }

    /**
     * Get fulltext search index
     *
     * @return \Ktpl\ElasticSearch\Api\Data\IndexInterface
     */
    private function getIndex()
    {
        return $this->indexRepository->get('catalogsearch_fulltext');
    }
}
