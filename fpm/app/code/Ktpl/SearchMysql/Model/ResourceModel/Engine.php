<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchMysql
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchMysql\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Class Engine
 *
 * @package Ktpl\SearchMysql\Model\ResourceModel
 */
class Engine implements EngineInterface
{
    /**
     * @deprecated
     * @see EngineInterface::FIELD_PREFIX
     */
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Scope identifier
     *
     * @deprecated
     * @see EngineInterface::SCOPE_IDENTIFIER
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;
    /**
     * Is attribute filterable as term cache
     *
     * @var array
     */
    private $termFilterableAttributeAttributeCache = [];

    /**
     * Engine constructor.
     *
     * @param Visibility $catalogProductVisibility
     * @param IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        Visibility $catalogProductVisibility,
        IndexScopeResolver $indexScopeResolver
    )
    {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return int[]
     */
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $value
     * @return array|mixed|string
     */
    public function processAttributeValue($attribute, $value)
    {
        if ($attribute->getIsSearchable()
            && in_array($attribute->getFrontendInput(), ['text', 'textarea'])
        ) {
            return $value;
        } elseif ($this->isTermFilterableAttribute($attribute)
            || in_array($attribute->getAttributeCode(), ['visibility', 'status'])
        ) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(',', $value);
            }
            if (!is_array($value)) {
                $value = [$value];
            }
            $valueMapper = function ($value) use ($attribute) {
                return Engine::ATTRIBUTE_PREFIX . $attribute->getAttributeCode() . '_' . $value;
            };

            return implode(' ', array_map($valueMapper, $value));
        }
    }

    /**
     * Is Attribute Filterable as Term
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @return bool
     */
    private function isTermFilterableAttribute($attribute)
    {
        $attributeId = $attribute->getAttributeId();
        if (!isset($this->termFilterableAttributeAttributeCache[$attributeId])) {
            $this->termFilterableAttributeAttributeCache[$attributeId] =
                in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
                && ($attribute->getIsVisibleInAdvancedSearch()
                    || $attribute->getIsFilterable()
                    || $attribute->getIsFilterableInSearch());
        }

        return $this->termFilterableAttributeAttributeCache[$attributeId];
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        $indexData = [];
        foreach ($index as $attributeId => $value) {
            $indexData[$attributeId] = is_array($value) ? implode($separator, $value) : $value;
        }
        return $indexData;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }
}
