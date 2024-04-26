<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Class Engine
 *
 * @package Ktpl\SearchElastic\Model\ResourceModel
 */
class Engine implements EngineInterface
{
    /**
     * attribute prefix
     */
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * Engine constructor.
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
     * {@inheritdoc}
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $value
     * @return mixed
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * {inheritdoc}
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }
}
